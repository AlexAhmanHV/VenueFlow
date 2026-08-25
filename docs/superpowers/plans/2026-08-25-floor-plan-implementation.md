# Visual Floor Plan for the Admin Live Board Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a visual, drag-to-arrange floor plan to VenueFlow's restaurant admin: a one-time editor for placing resources on a canvas, and a read-only "Floor plan" tab on the existing live board showing real-time occupancy, alongside the existing grid.

**Architecture:** Two nullable percentage-based coordinate columns on `resources` drive both surfaces. A new `FloorPlanController` (editor, manager-gated via the existing `restaurant_member:MANAGER` route middleware) handles layout setup with a Pointer-Events-based Alpine drag component and a single "save" PATCH. The existing `BookingController::liveBoard` action gains a computed occupancy map (from data it already loads) and passes it to a new floor-plan tab in the existing Blade view, which reuses the existing Echo/Reverb full-page-reload listener already on that page.

**Tech Stack:** Laravel 11, PHP 8.3, Blade, Alpine.js (Pointer Events for drag — no new JS dependency), Pest.

## Global Constraints

- Coordinates are floats 0–100 (percentage of canvas width/height), not fixed pixels — spec section "Data model".
- Floor plan editor and its save endpoint are gated the same way every other manager-level action in this app already is: `->middleware('restaurant_member:MANAGER')` on the route (see `routes/web.php:103-111` for the exact existing pattern on resource CRUD). Do **not** use `RestaurantPolicy::manage` — that gate is reserved for RESTAURANT_ADMIN-only actions elsewhere and would incorrectly exclude MANAGER staff.
- The floor plan tab itself (viewing, not editing) uses the live board's existing authorization — no new gate, it's a tab on an existing page.
- No new JS dependency. No drag-and-drop of bookings onto the floor plan (out of scope per spec).
- Real-time updates reuse the existing `restaurant.{id}.bookings` Echo channel and its existing full-page-reload behavior (see `resources/views/restaurant-admin/bookings/live-board.blade.php:164-168`) — do not build granular partial updates.
- "Reserved soon" = a non-cancelled, non-no-show booking item starting within the next 30 minutes.

---

### Task 1: `position_x`/`position_y` on resources

**Files:**
- Create: `database/migrations/2026_08_25_100000_add_position_to_resources_table.php`
- Modify: `app/Models/Resource.php`
- Test: `tests/Feature/ResourcePositionTest.php`

**Interfaces:**
- Produces: `Resource::isPositioned(): bool` — true only when both `position_x` and `position_y` are non-null. Later tasks use this to decide fallback-layout placement.
- Produces: `position_x`, `position_y` (nullable `float`) added to `Resource::$fillable`.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourcePositionTest extends TestCase
{
    use RefreshDatabase;

    private function makeRestaurant(): Restaurant
    {
        return Restaurant::query()->create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'timezone' => 'Europe/Stockholm',
            'active' => true,
        ]);
    }

    public function test_resource_is_not_positioned_by_default(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = Resource::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Table 1',
            'type' => 'TABLE',
            'capacity_min' => 2,
            'capacity_max' => 4,
            'active' => true,
        ]);

        $this->assertFalse($resource->isPositioned());
    }

    public function test_resource_position_can_be_saved_and_read_back(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = Resource::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Table 1',
            'type' => 'TABLE',
            'capacity_min' => 2,
            'capacity_max' => 4,
            'active' => true,
            'position_x' => 12.5,
            'position_y' => 87.25,
        ]);

        $this->assertTrue($resource->fresh()->isPositioned());
        $this->assertSame(12.5, $resource->fresh()->position_x);
        $this->assertSame(87.25, $resource->fresh()->position_y);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ResourcePositionTest`
Expected: FAIL — `position_x` column does not exist / `isPositioned` method does not exist.

- [ ] **Step 3: Write the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->float('position_x')->nullable()->after('active');
            $table->float('position_y')->nullable()->after('position_x');
        });
    }

    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn(['position_x', 'position_y']);
        });
    }
};
```

- [ ] **Step 4: Update the Resource model**

Modify `app/Models/Resource.php`:

```php
<?php

namespace App\Models;

use App\Enums\ResourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'type',
        'name',
        'capacity_min',
        'capacity_max',
        'active',
        'position_x',
        'position_y',
    ];

    protected function casts(): array
    {
        return [
            'type' => ResourceType::class,
            'active' => 'boolean',
            'position_x' => 'float',
            'position_y' => 'float',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function isPositioned(): bool
    {
        return $this->position_x !== null && $this->position_y !== null;
    }
}
```

- [ ] **Step 5: Run migration and test to verify it passes**

Run: `php artisan migrate && php artisan test --filter=ResourcePositionTest`
Expected: PASS (2 tests)

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_08_25_100000_add_position_to_resources_table.php app/Models/Resource.php tests/Feature/ResourcePositionTest.php
git commit -m "feat: add position_x/position_y to resources for floor plan"
```

---

### Task 2: Occupancy status computation

**Files:**
- Create: `app/Services/ResourceOccupancyResolver.php`
- Test: `tests/Unit/ResourceOccupancyResolverTest.php`

**Interfaces:**
- Consumes: `Resource` (Task 1), `BookingItem` (existing, with `guestBooking` and `resource` relations), `GuestBooking::status` (existing `BookingStatus` enum: `CONFIRMED`, `CANCELLED`, `NO_SHOW`, `CHECKED_IN`).
- Produces: `ResourceOccupancyResolver::resolve(Collection $bookings, CarbonInterface $now): array` — returns `[resource_id => 'free'|'occupied'|'reserved_soon']`. `$bookings` is a collection of `GuestBooking` models with `bookingItems.resource` eager-loaded (the same collection `BookingController::liveBoard` already builds). Task 4 calls this directly.

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Unit;

use App\Enums\BookingStatus;
use App\Models\BookingItem;
use App\Models\GuestBooking;
use App\Models\Resource;
use App\Models\Restaurant;
use App\Services\ResourceOccupancyResolver;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResourceOccupancyResolverTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;

    private function makeResource(string $name): Resource
    {
        return Resource::query()->create([
            'restaurant_id' => $this->restaurant->id,
            'name' => $name,
            'type' => 'TABLE',
            'capacity_min' => 2,
            'capacity_max' => 4,
            'active' => true,
        ]);
    }

    private function makeBooking(Resource $resource, Carbon $start, Carbon $end, BookingStatus $status): GuestBooking
    {
        $booking = GuestBooking::query()->create([
            'restaurant_id' => $this->restaurant->id,
            'public_id' => (string) Str::uuid(),
            'status' => $status,
            'customer_name' => 'Test Guest',
            'email' => 'guest@example.com',
            'party_size' => 2,
            'cancel_token_hash' => 'hash-'.Str::random(8),
        ]);

        BookingItem::query()->create([
            'guest_booking_id' => $booking->id,
            'resource_id' => $resource->id,
            'start_time' => $start,
            'end_time' => $end,
        ]);

        return $booking->load('bookingItems.resource');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::query()->create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'timezone' => 'Europe/Stockholm',
            'active' => true,
        ]);
    }

    public function test_resource_with_no_bookings_is_free(): void
    {
        $resource = $this->makeResource('Table 1');
        $now = Carbon::parse('2026-08-25 12:00:00', 'UTC');

        $statuses = ResourceOccupancyResolver::resolve(collect(), $now);

        $this->assertArrayNotHasKey($resource->id, $statuses);
    }

    public function test_resource_with_a_booking_covering_now_is_occupied(): void
    {
        $resource = $this->makeResource('Table 1');
        $now = Carbon::parse('2026-08-25 12:00:00', 'UTC');
        $booking = $this->makeBooking(
            $resource,
            $now->copy()->subMinutes(30),
            $now->copy()->addMinutes(30),
            BookingStatus::CHECKED_IN,
        );

        $statuses = ResourceOccupancyResolver::resolve(collect([$booking]), $now);

        $this->assertSame('occupied', $statuses[$resource->id]);
    }

    public function test_resource_with_a_booking_starting_within_30_minutes_is_reserved_soon(): void
    {
        $resource = $this->makeResource('Table 1');
        $now = Carbon::parse('2026-08-25 12:00:00', 'UTC');
        $booking = $this->makeBooking(
            $resource,
            $now->copy()->addMinutes(20),
            $now->copy()->addMinutes(80),
            BookingStatus::CONFIRMED,
        );

        $statuses = ResourceOccupancyResolver::resolve(collect([$booking]), $now);

        $this->assertSame('reserved_soon', $statuses[$resource->id]);
    }

    public function test_cancelled_booking_does_not_count_as_occupied(): void
    {
        $resource = $this->makeResource('Table 1');
        $now = Carbon::parse('2026-08-25 12:00:00', 'UTC');
        $booking = $this->makeBooking(
            $resource,
            $now->copy()->subMinutes(30),
            $now->copy()->addMinutes(30),
            BookingStatus::CANCELLED,
        );

        $statuses = ResourceOccupancyResolver::resolve(collect([$booking]), $now);

        $this->assertArrayNotHasKey($resource->id, $statuses);
    }

    public function test_no_show_booking_does_not_count_as_occupied(): void
    {
        $resource = $this->makeResource('Table 1');
        $now = Carbon::parse('2026-08-25 12:00:00', 'UTC');
        $booking = $this->makeBooking(
            $resource,
            $now->copy()->subMinutes(30),
            $now->copy()->addMinutes(30),
            BookingStatus::NO_SHOW,
        );

        $statuses = ResourceOccupancyResolver::resolve(collect([$booking]), $now);

        $this->assertArrayNotHasKey($resource->id, $statuses);
    }

    public function test_booking_far_in_the_future_leaves_resource_out_of_the_map(): void
    {
        $resource = $this->makeResource('Table 1');
        $now = Carbon::parse('2026-08-25 12:00:00', 'UTC');
        $booking = $this->makeBooking(
            $resource,
            $now->copy()->addHours(3),
            $now->copy()->addHours(4),
            BookingStatus::CONFIRMED,
        );

        $statuses = ResourceOccupancyResolver::resolve(collect([$booking]), $now);

        $this->assertArrayNotHasKey($resource->id, $statuses);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ResourceOccupancyResolverTest`
Expected: FAIL — `App\Services\ResourceOccupancyResolver` does not exist.

- [ ] **Step 3: Write the implementation**

```php
<?php

namespace App\Services;

use App\Enums\BookingStatus;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ResourceOccupancyResolver
{
    /**
     * @param  Collection  $bookings  GuestBooking models with bookingItems.resource eager-loaded.
     * @return array<int, string> resource_id => 'occupied'|'reserved_soon'. Resources with
     *                            neither state are simply absent from the map (treat as free).
     */
    public static function resolve(Collection $bookings, CarbonInterface $now): array
    {
        $reservedSoonCutoff = $now->copy()->addMinutes(30);
        $statuses = [];

        foreach ($bookings as $booking) {
            if (in_array($booking->status, [BookingStatus::CANCELLED, BookingStatus::NO_SHOW], true)) {
                continue;
            }

            foreach ($booking->bookingItems as $item) {
                $resourceId = $item->resource_id;

                if ($item->start_time->lte($now) && $item->end_time->gte($now)) {
                    $statuses[$resourceId] = 'occupied';
                    continue;
                }

                if (($statuses[$resourceId] ?? null) === 'occupied') {
                    continue;
                }

                if ($item->start_time->gt($now) && $item->start_time->lte($reservedSoonCutoff)) {
                    $statuses[$resourceId] = 'reserved_soon';
                }
            }
        }

        return $statuses;
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ResourceOccupancyResolverTest`
Expected: PASS (6 tests)

- [ ] **Step 5: Commit**

```bash
git add app/Services/ResourceOccupancyResolver.php tests/Unit/ResourceOccupancyResolverTest.php
git commit -m "feat: add ResourceOccupancyResolver for floor plan status"
```

---

### Task 3: Floor plan editor

**Files:**
- Create: `app/Http/Requests/RestaurantAdmin/UpdateFloorPlanRequest.php`
- Create: `app/Http/Controllers/RestaurantAdmin/FloorPlanController.php`
- Create: `resources/views/restaurant-admin/floor-plan/edit.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/components/restaurant-admin-nav.blade.php`
- Test: `tests/Feature/FloorPlanEditorTest.php`

**Interfaces:**
- Consumes: `Resource::isPositioned()`, `position_x`/`position_y` fillable (Task 1).
- Produces: named routes `restaurant.admin.floor-plan.edit` (GET) and `restaurant.admin.floor-plan.update` (PATCH). Task 4's nav/tab links reference `restaurant.admin.floor-plan.edit` for the "edit layout" link shown to managers.

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Feature;

use App\Enums\MembershipRole;
use App\Enums\StaffRole;
use App\Models\Resource;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FloorPlanEditorTest extends TestCase
{
    use RefreshDatabase;

    private function makeRestaurant(string $slug = 'test-restaurant'): Restaurant
    {
        return Restaurant::query()->create([
            'name' => ucfirst($slug),
            'slug' => $slug,
            'timezone' => 'Europe/Stockholm',
            'active' => true,
        ]);
    }

    private function makeResource(Restaurant $restaurant, string $name = 'Table 1'): Resource
    {
        return Resource::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => $name,
            'type' => 'TABLE',
            'capacity_min' => 2,
            'capacity_max' => 4,
            'active' => true,
        ]);
    }

    private function managerFor(Restaurant $restaurant): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        RestaurantMembership::query()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'role' => MembershipRole::STAFF->value,
            'staff_role' => StaffRole::MANAGER->value,
        ]);

        return $user;
    }

    private function staffFor(Restaurant $restaurant): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        RestaurantMembership::query()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'role' => MembershipRole::STAFF->value,
            'staff_role' => StaffRole::STAFF->value,
        ]);

        return $user;
    }

    public function test_plain_staff_cannot_view_the_editor(): void
    {
        $restaurant = $this->makeRestaurant();
        $staff = $this->staffFor($restaurant);

        $this->actingAs($staff)
            ->get("/r/{$restaurant->slug}/admin/floor-plan/edit")
            ->assertForbidden();
    }

    public function test_plain_staff_cannot_save_positions(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = $this->makeResource($restaurant);
        $staff = $this->staffFor($restaurant);

        $this->actingAs($staff)
            ->patch("/r/{$restaurant->slug}/admin/floor-plan", [
                'positions' => [
                    ['resource_id' => $resource->id, 'x' => 10, 'y' => 20],
                ],
            ])
            ->assertForbidden();
    }

    public function test_manager_can_save_positions(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = $this->makeResource($restaurant);
        $manager = $this->managerFor($restaurant);

        $this->actingAs($manager)
            ->patch("/r/{$restaurant->slug}/admin/floor-plan", [
                'positions' => [
                    ['resource_id' => $resource->id, 'x' => 33.5, 'y' => 66.25],
                ],
            ])
            ->assertRedirect();

        $this->assertSame(33.5, $resource->fresh()->position_x);
        $this->assertSame(66.25, $resource->fresh()->position_y);
    }

    public function test_cannot_save_a_position_for_another_tenants_resource(): void
    {
        $restaurantA = $this->makeRestaurant('restaurant-a');
        $restaurantB = $this->makeRestaurant('restaurant-b');
        $resourceB = $this->makeResource($restaurantB, 'Other Tenant Table');
        $managerA = $this->managerFor($restaurantA);

        $this->actingAs($managerA)
            ->patch("/r/{$restaurantA->slug}/admin/floor-plan", [
                'positions' => [
                    ['resource_id' => $resourceB->id, 'x' => 10, 'y' => 10],
                ],
            ])
            ->assertStatus(422);

        $this->assertNull($resourceB->fresh()->position_x);
    }

    public function test_editor_page_lists_unpositioned_resources(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = $this->makeResource($restaurant, 'Unplaced Table');
        $manager = $this->managerFor($restaurant);

        $this->actingAs($manager)
            ->get("/r/{$restaurant->slug}/admin/floor-plan/edit")
            ->assertOk()
            ->assertSee('Unplaced Table');
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=FloorPlanEditorTest`
Expected: FAIL — routes don't exist yet (404s instead of the expected statuses).

- [ ] **Step 3: Write the FormRequest**

```php
<?php

namespace App\Http\Requests\RestaurantAdmin;

use App\Models\Resource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateFloorPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'positions' => ['required', 'array'],
            'positions.*.resource_id' => ['required', 'integer', 'exists:resources,id'],
            'positions.*.x' => ['required', 'numeric', 'min:0', 'max:100'],
            'positions.*.y' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $restaurant = $this->attributes->get('restaurant');
            $resourceIds = collect($this->input('positions', []))->pluck('resource_id')->filter();

            if ($resourceIds->isEmpty()) {
                return;
            }

            $ownedCount = Resource::query()
                ->where('restaurant_id', $restaurant->id)
                ->whereIn('id', $resourceIds)
                ->count();

            if ($ownedCount !== $resourceIds->unique()->count()) {
                $validator->errors()->add('positions', 'One or more resources do not belong to this restaurant.');
            }
        });
    }
}
```

- [ ] **Step 4: Write the controller**

```php
<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantAdmin\UpdateFloorPlanRequest;
use App\Models\Resource;
use Illuminate\Http\Request;

class FloorPlanController extends Controller
{
    public function edit(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        $resources = Resource::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('restaurant-admin.floor-plan.edit', compact('restaurant', 'resources'));
    }

    public function update(UpdateFloorPlanRequest $request)
    {
        $restaurant = $request->attributes->get('restaurant');

        foreach ($request->validated('positions') as $position) {
            Resource::query()
                ->where('restaurant_id', $restaurant->id)
                ->where('id', $position['resource_id'])
                ->update([
                    'position_x' => $position['x'],
                    'position_y' => $position['y'],
                ]);
        }

        return back()->with('status', 'Golvplan sparad.');
    }
}
```

- [ ] **Step 5: Add the routes**

Modify `routes/web.php` — add immediately after the existing resources routes block (after line 111, the `restaurant.admin.resources.destroy` route):

```php
        Route::get('/floor-plan/edit', [FloorPlanController::class, 'edit'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.floor-plan.edit');
        Route::patch('/floor-plan', [FloorPlanController::class, 'update'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.floor-plan.update');
```

Add the import near the other `RestaurantAdmin` controller imports at the top of the file:

```php
use App\Http\Controllers\RestaurantAdmin\FloorPlanController;
```

- [ ] **Step 6: Write the editor Blade view**

Create `resources/views/restaurant-admin/floor-plan/edit.blade.php`:

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Golvplan · {{ $restaurant->name }}</h2>
    </x-slot>

    <section class="vf-container space-y-6 py-8">
        <x-restaurant-admin-nav :restaurant="$restaurant" />

        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="vf-card p-5">
            <p class="text-sm text-slate-600">Dra varje resurs till sin plats i lokalen. Klicka "Spara golvplan" när du är klar.</p>
        </div>

        <div
            x-data="floorPlanEditor(@js($resources->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'type' => $r->type->value,
                'x' => $r->position_x,
                'y' => $r->position_y,
            ])), '{{ route('restaurant.admin.floor-plan.update', $restaurant->slug) }}')"
            x-init="init()"
            class="vf-card p-5"
        >
            <div
                x-ref="canvas"
                class="relative h-[420px] w-full rounded-xl border border-slate-200 bg-slate-50"
            >
                <template x-for="resource in placed" :key="resource.id">
                    <div
                        class="absolute flex h-16 w-16 -translate-x-1/2 -translate-y-1/2 cursor-move select-none items-center justify-center rounded-lg border border-slate-300 bg-white text-center text-xs font-medium shadow-sm"
                        :style="`left: ${resource.x}%; top: ${resource.y}%;`"
                        @pointerdown="startDrag($event, resource)"
                        x-text="resource.name"
                    ></div>
                </template>
            </div>

            <template x-if="unplaced.length > 0">
                <div class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Oplacerade resurser — dra in dem ovan</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <template x-for="resource in unplaced" :key="resource.id">
                            <div
                                class="flex h-16 w-16 cursor-move select-none items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white text-center text-xs font-medium"
                                @pointerdown="startDrag($event, resource, true)"
                                x-text="resource.name"
                            ></div>
                        </template>
                    </div>
                </div>
            </template>

            <div class="mt-5 flex items-center gap-3">
                <button type="button" class="vf-btn-primary" @click="save()" x-text="saving ? 'Sparar…' : 'Spara golvplan'"></button>
                <span x-show="saved" x-cloak class="text-sm text-emerald-700">Sparat!</span>
                <span x-show="error" x-cloak class="text-sm text-rose-700" x-text="error"></span>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        function floorPlanEditor(resources, saveUrl) {
            return {
                resources,
                saveUrl,
                saving: false,
                saved: false,
                error: null,
                dragging: null,
                get placed() {
                    return this.resources.filter(r => r.x !== null && r.y !== null);
                },
                get unplaced() {
                    return this.resources.filter(r => r.x === null || r.y === null);
                },
                init() {},
                startDrag(event, resource, isUnplaced = false) {
                    if (isUnplaced) {
                        resource.x = 50;
                        resource.y = 50;
                    }
                    this.dragging = resource;

                    const move = (moveEvent) => this.onDrag(moveEvent);
                    const up = () => {
                        this.dragging = null;
                        window.removeEventListener('pointermove', move);
                        window.removeEventListener('pointerup', up);
                    };

                    window.addEventListener('pointermove', move);
                    window.addEventListener('pointerup', up);
                },
                onDrag(event) {
                    if (!this.dragging) return;
                    const canvas = this.$refs.canvas.getBoundingClientRect();
                    const x = ((event.clientX - canvas.left) / canvas.width) * 100;
                    const y = ((event.clientY - canvas.top) / canvas.height) * 100;
                    this.dragging.x = Math.min(100, Math.max(0, x));
                    this.dragging.y = Math.min(100, Math.max(0, y));
                },
                async save() {
                    this.saving = true;
                    this.saved = false;
                    this.error = null;

                    const positions = this.placed.map(r => ({ resource_id: r.id, x: r.x, y: r.y }));
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    try {
                        const response = await fetch(this.saveUrl, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ positions }),
                        });

                        if (!response.ok) {
                            const data = await response.json().catch(() => ({}));
                            this.error = data?.message || 'Kunde inte spara golvplanen.';
                            return;
                        }

                        this.saved = true;
                    } catch (_) {
                        this.error = 'Nätverksfel vid sparning. Försök igen.';
                    } finally {
                        this.saving = false;
                    }
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
```

- [ ] **Step 7: Add the nav link**

Modify `resources/views/components/restaurant-admin-nav.blade.php` — in the `@if($canManage)` block (alongside the existing "Resurser"/"Schema"/"Meny" links), add:

```blade
<a class="vf-btn-secondary" href="{{ route('restaurant.admin.floor-plan.edit', $restaurant->slug) }}">Golvplan</a>
```

And in the matching `@else` block, add the disabled equivalent alongside the other locked links:

```blade
<span class="vf-btn-secondary pointer-events-none opacity-45" aria-disabled="true" title="Kr&auml;ver MANAGER">Golvplan</span>
```

- [ ] **Step 8: Run tests to verify they pass**

Run: `php artisan test --filter=FloorPlanEditorTest`
Expected: PASS (5 tests)

- [ ] **Step 9: Commit**

```bash
git add app/Http/Requests/RestaurantAdmin/UpdateFloorPlanRequest.php app/Http/Controllers/RestaurantAdmin/FloorPlanController.php resources/views/restaurant-admin/floor-plan/edit.blade.php routes/web.php resources/views/components/restaurant-admin-nav.blade.php tests/Feature/FloorPlanEditorTest.php
git commit -m "feat: add floor plan editor with drag-to-arrange layout"
```

---

### Task 4: Live board floor-plan tab

**Files:**
- Create: `resources/views/restaurant-admin/bookings/partials/_booking-card.blade.php`
- Modify: `app/Http/Controllers/RestaurantAdmin/BookingController.php`
- Modify: `resources/views/restaurant-admin/bookings/live-board.blade.php`
- Test: `tests/Feature/LiveBoardFloorPlanTest.php`

**Interfaces:**
- Consumes: `ResourceOccupancyResolver::resolve()` (Task 2), `Resource::isPositioned()` (Task 1), route `restaurant.admin.floor-plan.edit` (Task 3, linked from the tab for managers).
- Produces: nothing further downstream — this is the last task.

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\MembershipRole;
use App\Models\BookingItem;
use App\Models\GuestBooking;
use App\Models\Resource;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LiveBoardFloorPlanTest extends TestCase
{
    use RefreshDatabase;

    private function makeRestaurant(): Restaurant
    {
        return Restaurant::query()->create([
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
            'timezone' => 'UTC',
            'active' => true,
        ]);
    }

    private function makeResource(Restaurant $restaurant, string $name, ?float $x = null, ?float $y = null): Resource
    {
        return Resource::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => $name,
            'type' => 'TABLE',
            'capacity_min' => 2,
            'capacity_max' => 4,
            'active' => true,
            'position_x' => $x,
            'position_y' => $y,
        ]);
    }

    private function adminFor(Restaurant $restaurant): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        RestaurantMembership::query()->create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'role' => MembershipRole::RESTAURANT_ADMIN->value,
            'staff_role' => null,
        ]);

        return $user;
    }

    private function makeCheckedInBookingNow(Restaurant $restaurant, Resource $resource): GuestBooking
    {
        $booking = GuestBooking::query()->create([
            'restaurant_id' => $restaurant->id,
            'public_id' => (string) Str::uuid(),
            'status' => BookingStatus::CHECKED_IN,
            'customer_name' => 'Live Guest',
            'email' => 'live@example.com',
            'party_size' => 2,
            'cancel_token_hash' => 'hash-'.Str::random(8),
        ]);

        BookingItem::query()->create([
            'guest_booking_id' => $booking->id,
            'resource_id' => $resource->id,
            'start_time' => Carbon::now()->subMinutes(15),
            'end_time' => Carbon::now()->addMinutes(45),
        ]);

        return $booking;
    }

    public function test_floor_plan_tab_shows_positioned_resource_as_occupied(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = $this->makeResource($restaurant, 'Table 1', 25.0, 40.0);
        $this->makeCheckedInBookingNow($restaurant, $resource);
        $admin = $this->adminFor($restaurant);

        $response = $this->actingAs($admin)
            ->get("/r/{$restaurant->slug}/admin/bookings/live-board?view=floor")
            ->assertOk();

        $response->assertSee('data-occupancy="occupied"', false);
        $response->assertSee('Live Guest');
    }

    public function test_floor_plan_tab_shows_free_resource_with_no_bookings(): void
    {
        $restaurant = $this->makeRestaurant();
        $this->makeResource($restaurant, 'Empty Table', 60.0, 60.0);
        $admin = $this->adminFor($restaurant);

        $this->actingAs($admin)
            ->get("/r/{$restaurant->slug}/admin/bookings/live-board?view=floor")
            ->assertOk()
            ->assertSee('data-occupancy="free"', false);
    }

    public function test_unpositioned_resource_still_appears_on_floor_plan_tab(): void
    {
        $restaurant = $this->makeRestaurant();
        $this->makeResource($restaurant, 'Unplaced Table');
        $admin = $this->adminFor($restaurant);

        $this->actingAs($admin)
            ->get("/r/{$restaurant->slug}/admin/bookings/live-board?view=floor")
            ->assertOk()
            ->assertSee('Unplaced Table');
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=LiveBoardFloorPlanTest`
Expected: FAIL — the view doesn't render a floor plan tab or `data-occupancy` attributes yet.

- [ ] **Step 3: Extract the booking-card partial**

Create `resources/views/restaurant-admin/bookings/partials/_booking-card.blade.php` with exactly the content of the existing `<article class="vf-card p-4">...</article>` block currently inlined in `live-board.blade.php` (the block iterating `$booking->bookingItems`, lines 112–146 of the current file), unchanged, so it can be reused by both the existing card list and the new floor-plan detail modal:

```blade
@props(['restaurant', 'booking'])

<article class="vf-card p-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-lg font-semibold">{{ $booking->customer_name }}</p>
            <p class="text-xs text-slate-500">{{ $booking->public_id }} · {{ $booking->party_size }} pers</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $booking->status->value }}</span>
    </div>

    <div class="mt-3 space-y-2">
        @foreach($booking->bookingItems as $item)
            <div
                draggable="true"
                class="drag-item cursor-move rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm"
                data-booking-item-id="{{ $item->id }}"
                data-booking-id="{{ $booking->id }}"
                data-move-url="{{ route('restaurant.admin.bookings.move-item', [$restaurant->slug, $booking, $item]) }}"
                data-duration="{{ $item->end_time->diffInMinutes($item->start_time) }}"
            >
                <p class="font-medium">{{ $item->resource->name }}</p>
                <p class="text-slate-600">{{ $item->start_time->timezone($restaurant->timezone)->format('Y-m-d H:i') }}-{{ $item->end_time->timezone($restaurant->timezone)->format('H:i') }}</p>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('restaurant.admin.bookings.status', [$restaurant->slug, $booking]) }}" class="mt-3 flex gap-2">
        @csrf
        <select name="status" class="vf-input">
            <option value="CONFIRMED">CONFIRMED</option>
            <option value="CHECKED_IN">CHECKED_IN</option>
            <option value="NO_SHOW">NO_SHOW</option>
        </select>
        <button class="vf-btn-primary">Spara</button>
    </form>
</article>
```

In `live-board.blade.php`, replace the existing inlined `<article class="vf-card p-4">...</article>` block (inside the `@forelse($bookings as $booking)` loop, the "Real: booking cards" section) with:

```blade
<x-restaurant-admin.booking-card :restaurant="$restaurant" :booking="$booking" />
```

(Laravel resolves `<x-restaurant-admin.booking-card>` to `resources/views/components/restaurant-admin/booking-card.blade.php` by default for anonymous components — since this partial lives under `bookings/partials/` instead, use an explicit `@include` rather than a component tag: replace the block with `@include('restaurant-admin.bookings.partials._booking-card', ['restaurant' => $restaurant, 'booking' => $booking])`.)

- [ ] **Step 4: Extend the controller**

Modify `app/Http/Controllers/RestaurantAdmin/BookingController.php` — add the import at the top:

```php
use App\Services\ResourceOccupancyResolver;
```

In `liveBoard()`, after the existing `$resources = ...` line (currently line 62), add:

```php
$occupancy = ResourceOccupancyResolver::resolve($bookings, Carbon::now());
$activeView = $request->string('view')->toString() === 'floor' ? 'floor' : 'grid';
```

Add `'occupancy', 'activeView'` to the `compact(...)` call at the end of the method, so it reads:

```php
return view('restaurant-admin.bookings.live-board', compact('restaurant', 'bookings', 'resources', 'slots', 'boardDate', 'slotStep', 'occupancy', 'activeView'));
```

- [ ] **Step 5: Add the floor plan tab to the Blade view**

Modify `resources/views/restaurant-admin/bookings/live-board.blade.php`:

1. Add a view toggle immediately below the existing date-picker `vf-card` block (after the `</div>` that closes it, before the `<div id="move-status">` line):

```blade
<div class="flex gap-2">
    <a
        href="{{ route('restaurant.admin.bookings.live', array_filter(['slug' => $restaurant->slug, 'date' => $boardDate, 'view' => 'grid'])) }}"
        class="{{ $activeView === 'grid' ? 'vf-btn-primary' : 'vf-btn-secondary' }}"
    >Rutnät</a>
    <a
        href="{{ route('restaurant.admin.bookings.live', array_filter(['slug' => $restaurant->slug, 'date' => $boardDate, 'view' => 'floor'])) }}"
        class="{{ $activeView === 'floor' ? 'vf-btn-primary' : 'vf-btn-secondary' }}"
    >Golvplan</a>
</div>
```

2. Wrap the existing "Resource grid with skeleton" `<div x-data="liveBoard()" ...>` block (and everything inside it, down to its closing `</div>` right before `@push('scripts')`) in `@if($activeView === 'grid') ... @endif`.

3. Immediately after that `@endif`, add the floor plan tab:

```blade
@if($activeView === 'floor')
    <div x-data="floorPlanBoard()" x-init="init()" class="vf-card p-5">
        <div class="relative h-[420px] w-full rounded-xl border border-slate-200 bg-slate-50">
            @foreach($resources as $resource)
                @php
                    $status = $occupancy[$resource->id] ?? 'free';
                    $color = match ($status) {
                        'occupied' => 'bg-rose-100 border-rose-300 text-rose-800',
                        'reserved_soon' => 'bg-amber-100 border-amber-300 text-amber-800',
                        default => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                    };
                    $left = $resource->isPositioned() ? $resource->position_x : (10 + ($loop->index % 8) * 11);
                    $top = $resource->isPositioned() ? $resource->position_y : (15 + intdiv($loop->index, 8) * 20);
                @endphp
                <button
                    type="button"
                    class="absolute flex h-16 w-16 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-lg border text-center text-xs font-medium {{ $color }}"
                    :class="openResourceId === {{ $resource->id }} ? 'ring-2 ring-slate-900' : ''"
                    style="left: {{ $left }}%; top: {{ $top }}%;"
                    data-occupancy="{{ $status }}"
                    @click="openResource({{ $resource->id }})"
                >{{ $resource->name }}</button>
            @endforeach
        </div>

        <p class="mt-3 text-xs text-slate-500">
            <a class="underline" href="{{ route('restaurant.admin.floor-plan.edit', $restaurant->slug) }}">Redigera golvplan</a>
        </p>
    </div>

    <x-modal name="floor-plan-booking" :show="false" maxWidth="md">
        <div class="p-6" x-data="{}" x-show="$store.floorPlan?.activeBooking" x-cloak>
            <template x-if="$store.floorPlan?.activeBooking">
                <div>
                    <h3 class="text-lg font-semibold" x-text="$store.floorPlan.activeBooking.customer_name"></h3>
                    <p class="text-sm text-slate-600" x-text="$store.floorPlan.activeBooking.party_size + ' pers · ' + $store.floorPlan.activeBooking.status"></p>
                </div>
            </template>
        </div>
    </x-modal>

    <script>
        function floorPlanBoard() {
            return {
                bookingsByResource: @js(
                    $bookings->flatMap(fn ($booking) => $booking->bookingItems->map(fn ($item) => [
                        'resource_id' => $item->resource_id,
                        'booking' => [
                            'customer_name' => $booking->customer_name,
                            'party_size' => $booking->party_size,
                            'status' => $booking->status->value,
                        ],
                    ]))
                ),
                openResourceId: null,
                init() {
                    if (window.Echo) {
                        window.Echo.channel(`restaurant.{{ $restaurant->id }}.bookings`)
                            .listen('.BookingCreated', () => window.location.reload())
                            .listen('.BookingStatusUpdated', () => window.location.reload());
                    }
                },
                openResource(resourceId) {
                    const match = this.bookingsByResource.find(b => b.resource_id === resourceId);
                    if (!match) return;
                    this.openResourceId = resourceId;
                    Alpine.store('floorPlan', { activeBooking: match.booking });
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'floor-plan-booking' }));
                },
            };
        }
    </script>
@endif
```

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test --filter=LiveBoardFloorPlanTest`
Expected: PASS (3 tests)

- [ ] **Step 7: Run the full suite to check for regressions**

Run: `php artisan test`
Expected: PASS — no existing test broken by the partial extraction or controller change.

- [ ] **Step 8: Commit**

```bash
git add resources/views/restaurant-admin/bookings/partials/_booking-card.blade.php app/Http/Controllers/RestaurantAdmin/BookingController.php resources/views/restaurant-admin/bookings/live-board.blade.php tests/Feature/LiveBoardFloorPlanTest.php
git commit -m "feat: add read-only floor plan tab to the live board"
```

---

## Self-Review Notes

- **Spec coverage:** data model (Task 1), occupancy computation (Task 2), editor with drag + save + manager gate (Task 3), live board tab with occupancy colors + click-to-modal + tenant-scoped save rejection + Echo reload reuse (Task 4). All spec sections have a task.
- **Authorization correction from the spec:** the spec said "gated by the existing `manage` policy." Reading the actual codebase (`routes/web.php`, `EnsureRestaurantMembership` middleware) showed every other manager-level action (resources, schedule, settings, menu, staff) is gated by `restaurant_member:MANAGER` route middleware, not `RestaurantPolicy::manage` (which only allows RESTAURANT_ADMIN, excluding MANAGER staff — the wrong, narrower gate). This plan uses the correct, consistent mechanism.
- **Type/name consistency check:** `ResourceOccupancyResolver::resolve()` signature (Task 2) matches its call site in Task 4 exactly. `Resource::isPositioned()` (Task 1) is used in Task 4's Blade view. Route names (`restaurant.admin.floor-plan.edit`, `restaurant.admin.floor-plan.update`) are consistent between Task 3's route definitions and Task 4's link.
