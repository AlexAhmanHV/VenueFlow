<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantAdmin\AddTemplateDrinkMenuItemRequest;
use App\Http\Requests\RestaurantAdmin\AddTemplateMenuItemRequest;
use App\Http\Requests\RestaurantAdmin\BulkMenuItemsRequest;
use App\Http\Requests\RestaurantAdmin\ReorderMenuItemsRequest;
use App\Http\Requests\RestaurantAdmin\StoreMenuItemRequest;
use App\Models\DishTemplate;
use App\Models\DrinkTemplate;
use App\Models\MenuItem;
use App\Models\MenuItemAudit;
use App\Models\Restaurant;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        /** @var Restaurant $restaurant */
        $restaurant = $request->attributes->get('restaurant');

        $allItems = MenuItem::query()
            ->where('restaurant_id', $restaurant->id)
            ->with(['dishTemplate', 'drinkTemplate'])
            ->get();

        $existingDishTemplateIds = $allItems->pluck('dish_template_id')->filter()->values();
        $dishTemplates = DishTemplate::query()
            ->where('active', true)
            ->whereNotIn('id', $existingDishTemplateIds)
            ->orderBy('name')
            ->get();

        $existingDrinkTemplateIds = $allItems->pluck('drink_template_id')->filter()->values();
        $drinkTemplates = DrinkTemplate::query()
            ->where('active', true)
            ->whereNotIn('id', $existingDrinkTemplateIds)
            ->orderBy('name')
            ->get();

        $query = MenuItem::query()
            ->where('restaurant_id', $restaurant->id)
            ->with(['dishTemplate', 'drinkTemplate']);

        if ($request->filled('q')) {
            $q = trim((string) $request->query('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($request->query('status') === 'active') {
            $query->where('active', true);
        } elseif ($request->query('status') === 'inactive') {
            $query->where('active', false);
        }

        if ($request->query('source') === 'template') {
            $query->where(function ($builder) {
                $builder->whereNotNull('dish_template_id')
                    ->orWhereNotNull('drink_template_id');
            });
        } elseif ($request->query('source') === 'manual') {
            $query->whereNull('dish_template_id')->whereNull('drink_template_id');
        }

        match ((string) $request->query('sort', 'custom')) {
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'recent' => $query->orderByDesc('id'),
            default => $query->orderBy('sort_order')->orderBy('name'),
        };

        $items = $query->get();

        $type = (string) $request->query('type', 'all');
        $items = $items->filter(function (MenuItem $item) use ($type) {
            if ($type === 'drink') {
                return $this->isDrink($item);
            }

            if ($type === 'food') {
                return ! $this->isDrink($item);
            }

            return true;
        })->values();

        $foodItems = $items->filter(fn (MenuItem $item) => ! $this->isDrink($item))->values();
        $drinkItems = $items->filter(fn (MenuItem $item) => $this->isDrink($item))->values();

        $recentAudits = MenuItemAudit::query()
            ->where('restaurant_id', $restaurant->id)
            ->with(['menuItem', 'actor'])
            ->latest('created_at')
            ->limit(15)
            ->get();

        return view('restaurant-admin.menu.index', compact(
            'restaurant',
            'items',
            'dishTemplates',
            'drinkTemplates',
            'foodItems',
            'drinkItems',
            'recentAudits'
        ));
    }

    public function store(StoreMenuItemRequest $request, ImageUploadService $imageUploadService)
    {
        $restaurant = $request->attributes->get('restaurant');

        $menuItem = MenuItem::query()->create([
            'restaurant_id' => $restaurant->id,
            'dish_template_id' => null,
            'drink_template_id' => null,
            'name' => $request->string('name')->toString(),
            'description' => $request->string('description')->toString() ?: null,
            'price' => $request->input('price'),
            'active' => $request->boolean('active', true),
            'tags' => $request->string('tags')->toString() ? array_map('trim', explode(',', $request->string('tags')->toString())) : null,
            'image_path' => $request->hasFile('image')
                ? $imageUploadService->store($request->file('image'), 'uploads/menu/items', ['crop' => $this->cropFromRequest($request)])
                : null,
            'sort_order' => $this->nextSortOrder((int) $restaurant->id),
        ]);

        $this->audit($restaurant->id, $menuItem->id, $request->user()?->id, 'created', [
            'name' => $menuItem->name,
            'price' => $menuItem->price,
            'image_path' => $menuItem->image_path,
        ]);

        return back()->with('status', 'Menyartikel skapad.');
    }

    public function storeFromTemplate(AddTemplateMenuItemRequest $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        $template = DishTemplate::query()
            ->whereKey((int) $request->integer('dish_template_id'))
            ->where('active', true)
            ->firstOrFail();

        $menuItem = MenuItem::query()->updateOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'dish_template_id' => $template->id,
            ],
            [
                'name' => $template->name,
                'description' => $template->description,
                'price' => $request->filled('price') ? $request->input('price') : $template->base_price,
                'active' => $request->boolean('active', true),
                'tags' => $template->tags,
                'drink_template_id' => null,
                'image_path' => $template->image_path,
                'sort_order' => $this->nextSortOrder((int) $restaurant->id),
            ]
        );

        $this->audit($restaurant->id, $menuItem->id, $request->user()?->id, 'added_from_dish_template', [
            'template_id' => $template->id,
            'price' => $menuItem->price,
            'image_path' => $menuItem->image_path,
        ]);

        return back()->with('status', 'Menyartikel tillagd fran rattkatalogen.');
    }

    public function storeFromDrinkTemplate(AddTemplateDrinkMenuItemRequest $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        $template = DrinkTemplate::query()
            ->whereKey((int) $request->integer('drink_template_id'))
            ->where('active', true)
            ->firstOrFail();

        $menuItem = MenuItem::query()->updateOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'drink_template_id' => $template->id,
            ],
            [
                'name' => $template->name,
                'description' => $template->description,
                'price' => $request->filled('price') ? $request->input('price') : $template->base_price,
                'active' => $request->boolean('active', true),
                'tags' => $template->tags,
                'dish_template_id' => null,
                'image_path' => $template->image_path,
                'sort_order' => $this->nextSortOrder((int) $restaurant->id),
            ]
        );

        $this->audit($restaurant->id, $menuItem->id, $request->user()?->id, 'added_from_drink_template', [
            'template_id' => $template->id,
            'price' => $menuItem->price,
            'image_path' => $menuItem->image_path,
        ]);

        return back()->with('status', 'Menyartikel tillagd fran dryckeskatalogen.');
    }

    public function update(StoreMenuItemRequest $request, string $slug, MenuItem $menu, ImageUploadService $imageUploadService)
    {
        $restaurant = $request->attributes->get('restaurant');
        abort_unless($menu->restaurant_id === $restaurant->id, 404);

        $before = [
            'name' => $menu->name,
            'description' => $menu->description,
            'price' => $menu->price,
            'active' => $menu->active,
            'tags' => $menu->tags,
            'image_path' => $menu->image_path,
        ];

        $payload = [
            'name' => $request->string('name')->toString(),
            'description' => $request->string('description')->toString() ?: null,
            'price' => $request->input('price'),
            'active' => $request->boolean('active'),
            'tags' => $request->string('tags')->toString() ? array_map('trim', explode(',', $request->string('tags')->toString())) : null,
        ];

        $undoToken = null;
        if ($request->hasFile('image')) {
            if (! empty($menu->image_path)) {
                $trash = $imageUploadService->moveToTrash(
                    $menu->image_path,
                    MenuItem::class,
                    (int) $menu->id,
                    'image_path',
                    $request->user()?->id
                );
                $undoToken = $trash?->token;
            }

            $payload['image_path'] = $imageUploadService->store(
                $request->file('image'),
                'uploads/menu/items',
                ['crop' => $this->cropFromRequest($request)]
            );
        } elseif ($request->boolean('remove_image') && ! empty($menu->image_path)) {
            $trash = $imageUploadService->moveToTrash(
                $menu->image_path,
                MenuItem::class,
                (int) $menu->id,
                'image_path',
                $request->user()?->id
            );
            $undoToken = $trash?->token;
            $payload['image_path'] = null;
        }

        $menu->update($payload);

        $this->audit($restaurant->id, $menu->id, $request->user()?->id, 'updated', [
            'before' => $before,
            'after' => [
                'name' => $menu->name,
                'description' => $menu->description,
                'price' => $menu->price,
                'active' => $menu->active,
                'tags' => $menu->tags,
                'image_path' => $menu->image_path,
            ],
        ]);

        if ($undoToken) {
            session()->flash('undo_restore_url', route('media.restore', ['token' => $undoToken]));
            session()->flash('undo_message', 'Bild borttagen. Du kan angra i 20 minuter.');
        }

        return back()->with('status', 'Menyartikel uppdaterad.');
    }

    public function bulkUpdate(BulkMenuItemsRequest $request, string $slug, ImageUploadService $imageUploadService)
    {
        $restaurant = $request->attributes->get('restaurant');

        $items = MenuItem::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('id', $request->input('item_ids', []))
            ->get();

        if ($items->isEmpty()) {
            return back()->with('status', 'Inga menyartiklar valdes.');
        }

        $action = (string) $request->input('action');
        $firstUndoToken = null;

        DB::transaction(function () use (
            $action,
            $items,
            $request,
            $imageUploadService,
            &$firstUndoToken,
            $restaurant
        ) {
            foreach ($items as $item) {
                $before = [
                    'price' => $item->price,
                    'active' => $item->active,
                    'tags' => $item->tags,
                    'image_path' => $item->image_path,
                ];

                match ($action) {
                    'set_active' => $item->update(['active' => true]),
                    'set_inactive' => $item->update(['active' => false]),
                    'delete' => $item->delete(),
                    'remove_image' => $this->bulkRemoveImage($item, $request, $imageUploadService, $firstUndoToken),
                    'add_tag' => $this->bulkAddTag($item, (string) $request->input('tag')),
                    'remove_tag' => $this->bulkRemoveTag($item, (string) $request->input('tag')),
                    'increase_price_pct' => $this->bulkAdjustPrice($item, abs((float) $request->input('percentage', 5))),
                    'decrease_price_pct' => $this->bulkAdjustPrice($item, -abs((float) $request->input('percentage', 5))),
                    default => null,
                };

                $this->audit($restaurant->id, $item->id, $request->user()?->id, 'bulk_'.$action, [
                    'before' => $before,
                    'after' => [
                        'price' => $item->price,
                        'active' => $item->active,
                        'tags' => $item->tags,
                        'image_path' => $item->image_path,
                    ],
                ]);
            }
        });

        if ($firstUndoToken) {
            session()->flash('undo_restore_url', route('media.restore', ['token' => $firstUndoToken]));
            session()->flash('undo_message', 'Bild borttagen i bulk. Du kan angra i 20 minuter.');
        }

        return back()->with('status', 'Bulk-uppdatering klar.');
    }

    public function reorder(ReorderMenuItemsRequest $request, string $slug): JsonResponse
    {
        $restaurant = $request->attributes->get('restaurant');
        $ids = collect($request->input('ordered_ids', []))->map(fn ($id) => (int) $id)->values();

        $validIds = MenuItem::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        DB::transaction(function () use ($ids, $validIds, $restaurant, $request) {
            $order = 10;
            foreach ($ids as $id) {
                if (! $validIds->contains($id)) {
                    continue;
                }

                MenuItem::query()
                    ->where('restaurant_id', $restaurant->id)
                    ->where('id', $id)
                    ->update(['sort_order' => $order]);

                $order += 10;
            }

            $this->audit($restaurant->id, null, $request->user()?->id, 'reordered', [
                'ordered_ids' => $ids->all(),
            ]);
        });

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, string $slug, MenuItem $menu, ImageUploadService $imageUploadService)
    {
        $restaurant = $request->attributes->get('restaurant');
        abort_unless($menu->restaurant_id === $restaurant->id, 404);

        $this->audit($restaurant->id, $menu->id, $request->user()?->id, 'deleted', [
            'name' => $menu->name,
            'price' => $menu->price,
            'image_path' => $menu->image_path,
        ]);

        if (! empty($menu->image_path)) {
            $imageUploadService->remove($menu->image_path);
        }

        $menu->delete();

        return back()->with('status', 'Menyartikel borttagen.');
    }

    private function isDrink(MenuItem $item): bool
    {
        if ($item->drink_template_id !== null) {
            return true;
        }

        if ($item->dish_template_id !== null) {
            return false;
        }

        $drinkTags = [
            'drink', 'beer', 'wine', 'cocktail', 'mocktail', 'cider',
            'coffee', 'tea', 'juice', 'soft', 'water', 'non_alcoholic',
        ];

        $tags = collect($item->tags ?? [])->map(fn ($tag) => strtolower((string) $tag));

        return $tags->intersect($drinkTags)->isNotEmpty();
    }

    private function bulkAddTag(MenuItem $item, string $tag): void
    {
        $tag = trim($tag);
        if ($tag === '') {
            return;
        }

        $tags = collect($item->tags ?? [])->map(fn ($value) => trim((string) $value))->filter();
        if (! $tags->contains($tag)) {
            $tags->push($tag);
            $item->update(['tags' => $tags->values()->all()]);
        }
    }

    private function bulkRemoveTag(MenuItem $item, string $tag): void
    {
        $tag = trim($tag);
        if ($tag === '') {
            return;
        }

        $tags = collect($item->tags ?? [])->map(fn ($value) => trim((string) $value))->reject(fn ($value) => $value === $tag)->values();
        $item->update(['tags' => $tags->isEmpty() ? null : $tags->all()]);
    }

    private function bulkAdjustPrice(MenuItem $item, float $pct): void
    {
        $factor = 1 + ($pct / 100);
        $newPrice = max(0, round((float) $item->price * $factor, 2));
        $item->update(['price' => $newPrice]);
    }

    private function bulkRemoveImage(
        MenuItem $item,
        Request $request,
        ImageUploadService $imageUploadService,
        ?string &$firstUndoToken
    ): void {
        if (empty($item->image_path)) {
            return;
        }

        $trash = $imageUploadService->moveToTrash(
            $item->image_path,
            MenuItem::class,
            (int) $item->id,
            'image_path',
            $request->user()?->id
        );

        if ($trash && ! $firstUndoToken) {
            $firstUndoToken = $trash->token;
        }

        $item->update(['image_path' => null]);
    }

    private function cropFromRequest(Request $request): ?array
    {
        if (! $request->filled('crop_w') || ! $request->filled('crop_h')) {
            return null;
        }

        return [
            'x' => (int) $request->integer('crop_x'),
            'y' => (int) $request->integer('crop_y'),
            'w' => (int) $request->integer('crop_w'),
            'h' => (int) $request->integer('crop_h'),
        ];
    }

    private function nextSortOrder(int $restaurantId): int
    {
        $max = (int) MenuItem::query()->where('restaurant_id', $restaurantId)->max('sort_order');
        return $max > 0 ? $max + 10 : 10;
    }

    private function audit(int $restaurantId, ?int $menuItemId, ?int $actorUserId, string $action, array $changes = []): void
    {
        MenuItemAudit::query()->create([
            'restaurant_id' => $restaurantId,
            'menu_item_id' => $menuItemId,
            'actor_user_id' => $actorUserId,
            'action' => $action,
            'changes' => $changes,
            'created_at' => now(),
        ]);
    }
}
