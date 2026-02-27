<?php

namespace App\Http\Controllers\PublicSite;

use App\Enums\ResourceType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreGuestBookingRequest;
use App\Models\BookingSlotHold;
use App\Models\GuestBooking;
use App\Models\Resource;
use App\Services\CancelGuestBooking;
use App\Services\CreateGuestBooking;
use App\Services\FindAvailableSlots;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use ValueError;

class BookingController extends Controller
{
    public function __construct(
        private readonly FindAvailableSlots $findAvailableSlots,
        private readonly CreateGuestBooking $createGuestBooking,
        private readonly CancelGuestBooking $cancelGuestBooking,
    ) {
    }

    public function create(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');
        BookingSlotHold::query()->where('expires_at', '<=', now('UTC'))->delete();

        $resourceType = $request->string('resource_type')->toString() ?: ResourceType::GOLF->value;
        $date = $request->string('date')->toString() ?: Carbon::now($restaurant->timezone)->format('Y-m-d');
        $partySize = (int) $request->integer('party_size', 2);
        $defaultDurations = $restaurant->setting?->default_durations ?? [];
        $fallbackDuration = (int) ($defaultDurations[$resourceType] ?? 60);
        $duration = (int) $request->integer('duration_minutes', $fallbackDuration);

        $slots = [];
        try {
            $slots = $this->findAvailableSlots->execute(
                $restaurant,
                ResourceType::from($resourceType),
                $date,
                $partySize,
                $duration,
                $request->session()->getId(),
            );
        } catch (ValueError) {
            $slots = [];
        }

        $selectedItems = $this->selectedItems($request, $restaurant->id);

        return view('public.book', compact('restaurant', 'slots', 'resourceType', 'date', 'partySize', 'duration', 'selectedItems'));
    }

    public function addItem(Request $request): RedirectResponse
    {
        $restaurant = $request->attributes->get('restaurant');
        BookingSlotHold::query()->where('expires_at', '<=', now('UTC'))->delete();

        $slot = $request->string('resource_slot')->toString();
        if ($slot && ! $request->filled('resource_id')) {
            [$resourceId, $start, $end] = array_pad(explode('|', $slot), 3, null);
            $request->merge([
                'resource_id' => $resourceId,
                'start_time_local' => $start,
                'end_time_local' => $end,
            ]);
        }

        $data = $request->validate([
            'resource_id' => ['required', 'integer'],
            'start_time_local' => ['required', 'date_format:Y-m-d H:i'],
            'end_time_local' => ['required', 'date_format:Y-m-d H:i', 'after:start_time_local'],
            'resource_type' => ['nullable', 'string'],
            'date' => ['nullable', 'date'],
            'party_size' => ['nullable', 'integer', 'min:1', 'max:50'],
            'duration_minutes' => ['nullable', 'integer', 'min:30', 'max:600'],
        ]);

        $resource = Resource::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereKey($data['resource_id'])
            ->where('active', true)
            ->first();

        if (! $resource) {
            throw ValidationException::withMessages(['resource_id' => 'Ogiltig resurs för vald restaurang.']);
        }

        $items = $this->selectedItems($request, $restaurant->id);
        $duplicate = collect($items)->contains(fn (array $item) =>
            (int) $item['resource_id'] === (int) $data['resource_id']
            && $item['start_time_local'] === $data['start_time_local']
            && $item['end_time_local'] === $data['end_time_local']
        );

        if (! $duplicate) {
            $hold = BookingSlotHold::create([
                'restaurant_id' => $restaurant->id,
                'resource_id' => (int) $data['resource_id'],
                'session_id' => $request->session()->getId(),
                'start_time' => Carbon::parse($data['start_time_local'], $restaurant->timezone)->utc(),
                'end_time' => Carbon::parse($data['end_time_local'], $restaurant->timezone)->utc(),
                'expires_at' => now('UTC')->addMinutes(5),
            ]);

            $items[] = [
                'resource_id' => (int) $data['resource_id'],
                'resource_name' => $resource->name,
                'start_time_local' => $data['start_time_local'],
                'end_time_local' => $data['end_time_local'],
                'hold_id' => $hold->id,
            ];
        }

        $this->storeSelectedItems($request, $restaurant->id, $items);

        return redirect()
            ->route('public.booking.create', [
                'slug' => $restaurant->slug,
                'resource_type' => $data['resource_type'] ?? null,
                'date' => $data['date'] ?? null,
                'party_size' => $data['party_size'] ?? null,
                'duration_minutes' => $data['duration_minutes'] ?? null,
            ])
            ->with('status', $duplicate ? 'Aktiviteten fanns redan i bokningen.' : 'Aktivitet tillagd. Välj fler eller gå vidare.');
    }

    public function removeItem(Request $request): RedirectResponse
    {
        $restaurant = $request->attributes->get('restaurant');

        $data = $request->validate([
            'index' => ['required', 'integer', 'min:0'],
        ]);

        $items = $this->selectedItems($request, $restaurant->id);
        if (array_key_exists($data['index'], $items)) {
            if (! empty($items[$data['index']]['hold_id'])) {
                BookingSlotHold::query()
                    ->whereKey($items[$data['index']]['hold_id'])
                    ->where('session_id', $request->session()->getId())
                    ->delete();
            }
            unset($items[$data['index']]);
            $items = array_values($items);
            $this->storeSelectedItems($request, $restaurant->id, $items);
        }

        return back()->with('status', 'Aktivitet borttagen.');
    }

    public function details(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');
        $selectedItems = $this->selectedItems($request, $restaurant->id);

        if (empty($selectedItems)) {
            return redirect()
                ->route('public.booking.create', ['slug' => $restaurant->slug])
                ->with('status', 'Lägg till minst en aktivitet först.');
        }

        $menuItems = $restaurant->menuItems()->where('active', true)->orderBy('name')->get();
        $hasTableBooking = Resource::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('id', collect($selectedItems)->pluck('resource_id'))
            ->where('type', ResourceType::TABLE->value)
            ->exists();

        $firstStart = collect($selectedItems)
            ->map(fn (array $item) => Carbon::parse($item['start_time_local'], $restaurant->timezone))
            ->sort()
            ->first();

        $serveTimeMin = $firstStart->format('Y-m-d\TH:i');
        $serveTimeMax = $firstStart->copy()->addHours(2)->format('Y-m-d\TH:i');

        return view('public.booking-details', compact('restaurant', 'selectedItems', 'menuItems', 'serveTimeMin', 'serveTimeMax', 'hasTableBooking'));
    }

    public function store(StoreGuestBookingRequest $request): RedirectResponse
    {
        $restaurant = $request->attributes->get('restaurant');

        $selectedItems = $this->selectedItems($request, $restaurant->id);
        if (empty($selectedItems)) {
            throw ValidationException::withMessages([
                'booking_items' => 'Du måste välja minst en aktivitet innan bokningen kan slutföras.',
            ]);
        }

        $payload = $request->validated();
        $payload['booking_items'] = array_map(fn (array $item) => [
            'resource_id' => $item['resource_id'],
            'start_time_local' => $item['start_time_local'],
            'end_time_local' => $item['end_time_local'],
        ], $selectedItems);

        $hasTableBooking = Resource::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('id', collect($selectedItems)->pluck('resource_id'))
            ->where('type', ResourceType::TABLE->value)
            ->exists();

        if (! $hasTableBooking) {
            unset($payload['preorder_serve_time_local']);
        }

        if ($hasTableBooking && ! empty($payload['preorder_serve_time_local'])) {
            $firstStart = collect($selectedItems)
                ->map(fn (array $item) => Carbon::parse($item['start_time_local'], $restaurant->timezone))
                ->sort()
                ->first();

            $serveAt = Carbon::parse($payload['preorder_serve_time_local'], $restaurant->timezone);
            $serveMax = $firstStart->copy()->addHours(2);

            if ($serveAt->lt($firstStart) || $serveAt->gt($serveMax)) {
                throw ValidationException::withMessages([
                    'preorder_serve_time_local' => 'Serveringstid måste vara mellan bokningens start och två timmar efter.',
                ]);
            }
        }

        $booking = $this->createGuestBooking->execute($restaurant, $payload);
        BookingSlotHold::query()
            ->where('session_id', $request->session()->getId())
            ->where('restaurant_id', $restaurant->id)
            ->delete();
        $this->clearSelectedItems($request, $restaurant->id);

        return redirect()->route('public.booking.show', [
            'slug' => $restaurant->slug,
            'public_id' => $booking->public_id,
        ]);
    }

    public function show(Request $request, string $slug, string $public_id)
    {
        $restaurant = $request->attributes->get('restaurant');

        $booking = GuestBooking::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('public_id', $public_id)
            ->with(['bookingItems.resource', 'preorders.items.menuItem'])
            ->firstOrFail();

        return view('public.booking-confirmation', compact('restaurant', 'booking'));
    }

    public function cancel(Request $request): RedirectResponse
    {
        $restaurant = $request->attributes->get('restaurant');

        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $this->cancelGuestBooking->execute($restaurant, $request->string('token')->toString());

        return redirect()->route('public.landing', ['slug' => $restaurant->slug])
            ->with('status', 'Bokningen är avbokad.');
    }

    /**
     * @return array<int, array{resource_id:int,resource_name:string,start_time_local:string,end_time_local:string,hold_id?:int}>
     */
    private function selectedItems(Request $request, int $restaurantId): array
    {
        $items = $request->session()->get($this->sessionKey($restaurantId), []);

        $filtered = array_values(array_filter($items, function (array $item) use ($request, $restaurantId) {
            if (empty($item['hold_id'])) {
                return true;
            }

            return BookingSlotHold::query()
                ->whereKey($item['hold_id'])
                ->where('restaurant_id', $restaurantId)
                ->where('session_id', $request->session()->getId())
                ->where('expires_at', '>', now('UTC'))
                ->exists();
        }));

        if (count($filtered) !== count($items)) {
            $request->session()->put($this->sessionKey($restaurantId), $filtered);
        }

        return $filtered;
    }

    /**
     * @param  array<int, array{resource_id:int,resource_name:string,start_time_local:string,end_time_local:string}>  $items
     */
    private function storeSelectedItems(Request $request, int $restaurantId, array $items): void
    {
        $request->session()->put($this->sessionKey($restaurantId), $items);
    }

    private function clearSelectedItems(Request $request, int $restaurantId): void
    {
        $request->session()->forget($this->sessionKey($restaurantId));
    }

    private function sessionKey(int $restaurantId): string
    {
        return 'booking_wizard.'.$restaurantId.'.items';
    }
}
