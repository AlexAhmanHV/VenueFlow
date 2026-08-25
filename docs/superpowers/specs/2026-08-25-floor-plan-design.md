# Visual Floor Plan for the Admin Live Board — Design

## Overview

The restaurant admin live board (`/r/{slug}/admin/bookings/live-board`) currently shows resources as rows in a resource × time-slot grid for the whole day, with native HTML5 drag-and-drop to reassign a booking to a different resource/time. This is functional but doesn't match how staff actually think about the floor during service — as a physical layout of tables/bays, not a spreadsheet.

This feature adds a **visual floor plan**: a spatial view where each resource (table, golf bay, dart lane, etc.) sits at a position matching the venue's real layout, colored by its current occupancy. It ships alongside the existing grid as a second view, not a replacement.

## Goals

- Let a manager/admin arrange resources on a canvas to match their real floor layout, once, via drag-and-drop.
- Let staff see, at a glance during service, which tables are free/occupied/reserved-soon, spatially.
- Let staff click a table to see its current/next booking and take the same quick actions already available today (check-in, no-show, add note).
- Update live via the existing WebSocket channel, same as the grid.

## Non-goals (out of scope for this spec)

- Dragging bookings onto tables on the floor plan to reassign them (the floor plan has no time axis, so "drop here" doesn't map cleanly to a time slot — that stays exclusively in the existing grid view).
- A time scrubber / projected-occupancy-at-a-future-time view. The floor plan shows *now* only.
- Free resizing or rotation of table icons. Each `ResourceType` gets one fixed icon/size.
- Removing or replacing the existing grid view.

## Data model

New migration adds two nullable columns to `resources`:

```php
Schema::table('resources', function (Blueprint $table) {
    $table->float('position_x')->nullable(); // 0–100, percentage of canvas width
    $table->float('position_y')->nullable(); // 0–100, percentage of canvas height
});
```

Percentage-based coordinates (not fixed pixels) so the layout stays correct if the canvas renders at a different size (e.g. a narrower admin viewport).

A resource with `position_x`/`position_y` both `null` has not been placed yet. Un-placed resources are shown in a simple auto-arranged fallback row (computed at render time, not persisted) so they're visible and draggable into place, and so newly-created resources always appear somewhere rather than vanishing from the floor plan.

`App\Models\Resource` gets the two columns added to `$fillable` and a `isPositioned(): bool` accessor (`position_x !== null && position_y !== null`).

## Routes & authorization

Two new routes, both nested under the existing `resolve_restaurant:any`, `restaurant_member`, `tenant_bindings` middleware group that already wraps `restaurant.admin.*` routes:

```php
Route::get('/floor-plan/edit', [FloorPlanController::class, 'edit'])->name('restaurant.admin.floor-plan.edit');
Route::patch('/floor-plan', [FloorPlanController::class, 'update'])->name('restaurant.admin.floor-plan.update');
```

- `edit`: authorized via `$this->authorize('manage', $restaurant)` — the same gate `AdminResourceController` already uses for resource CRUD. Only MANAGER/RESTAURANT_ADMIN staff can rearrange the layout.
- `update`: same `manage` gate. Accepts `positions: [{resource_id, x, y}, ...]`, validated so every `resource_id` belongs to the current tenant (reuses the existing `tenant_bindings` pattern already applied to route-bound models elsewhere) and `x`/`y` are numeric 0–100.

The live board's floor plan *view* reuses the existing `liveBoard` action's authorization (`$this->authorize('view', $restaurant)`) — no new route needed there, it's a tab within the existing page.

## UI: Floor plan editor (`/floor-plan/edit`)

- A canvas (a fixed-aspect-ratio `<div>`, e.g. 4:3) rendering every active resource as an absolutely-positioned icon (icon/color keyed off `ResourceType`), labeled with the resource name.
- Dragging is implemented with native Pointer Events (`pointerdown` → `pointermove` → `pointerup`) in a small Alpine `x-data` component — no new JS dependency, consistent with the project's existing Alpine-only approach (the grid's drag-and-drop uses native HTML5 DnD, which is the right tool for discrete drop targets but doesn't suit continuous x/y placement, hence Pointer Events here instead).
- Dragging updates position in local Alpine state immediately; nothing is persisted until the admin clicks **"Save layout"**, which submits all positions in one `PATCH` request. This avoids a write per pixel of movement while dragging.
- Un-positioned resources render in a fallback strip along one edge of the canvas so they're reachable to drag in.

## UI: Live board floor plan tab

- The existing live board page (`live-board.blade.php`) gains a view toggle: **Grid** (today's existing table) and **Floor plan** (new). Both read from data already loaded for the page — no separate page load.
- Floor plan tab renders the same canvas layout as the editor, but read-only (no drag), with each resource tinted by current status:
  - **Free** — no booking item covering the current moment.
  - **Occupied** — a booking item where `start_time <= now <= end_time`.
  - **Reserved soon** — a booking item starting within the next 30 minutes.
- Clicking a resource opens a detail panel using the project's existing `x-modal` component (already used elsewhere, e.g. the profile deletion confirmation), reusing the same markup/data already used for a booking's card in the grid view — customer name, party size, status, and the existing status-update form — for that resource's current or next booking.

## Occupancy status computation

`BookingController::liveBoard` already loads today's bookings with `bookingItems.resource` eager-loaded. Add a small computed collection, keyed by `resource_id`, resolving each active resource's current status by checking its booking items against `now()` in the restaurant's timezone. This is pure computation over already-loaded data — no additional query.

## Real-time updates

The existing live board listens on `restaurant.{id}.bookings` for `BookingCreated`/`BookingStatusUpdated` and does a **full page reload** (with a brief loading-skeleton flash) on either event — it does not do granular partial updates. The floor plan tab reuses this exact same listener and reload behavior for consistency with the grid, rather than introducing new granular per-tile update logic.

## Testing (Pest feature tests)

- Staff without `manage` permission cannot access `floor-plan/edit` or submit `PATCH /floor-plan` (403).
- Submitting valid positions persists them; resources from another tenant in the payload are rejected.
- Un-positioned resources appear in the fallback layout on both the editor and live board floor plan tab.
- Given seeded booking items spanning "now", the live board floor plan tab reports the correct status (free/occupied/reserved-soon) per resource.
- Clicking a resource's detail panel shows the correct current/next booking, scoped to the correct tenant.

## Future ideas (not in this spec)

- Drag bookings directly onto floor-plan tables once/if a "select a time" step is added to the interaction.
- Multiple floor-plan "rooms" or a zoomable multi-floor layout for larger venues.
- Resizing/rotating table icons for non-standard table shapes.
