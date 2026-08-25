# Embeddable Booking Widget Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let a venue embed VenueFlow's existing booking flow on their own website via a single `<script>` tag, with the whole journey (pick activity → details → confirmation) working seamlessly inside a self-resizing iframe, including in Safari and Firefox.

**Architecture:** An `embed=1` query flag switches the existing public booking flow (`App\Http\Controllers\PublicSite\BookingController`) to a stripped-down layout and is threaded through every link/form/redirect in the journey. A `SetEmbedSessionCookieAttributes` middleware, applied to the same routes, gives the session cookie CHIPS-partitioned attributes (`SameSite=None; Secure; Partitioned`) only when `embed=1` is present, so the guest's in-progress cart survives across steps even when third-party cookies are blocked. A static `/embed.js` loader (plain JS, no build step) builds the iframe and auto-resizes it via `postMessage`. A new admin page shows the copy-paste snippet with a live, working preview.

**Tech Stack:** Laravel 11, Blade, plain vanilla JS (no new dependency), Pest.

## Global Constraints

- No new JS dependency; `embed.js` is plain vanilla JS with no build step, matching the project's existing Alpine-only philosophy for anything more.
- The `Partitioned`/`SameSite=None`/`Secure` cookie override must apply **only** to `embed=1`-flagged requests — the rest of the site (admin login, non-embedded guest booking) keeps the current `Lax` default untouched.
- The embedded journey covers exactly: create → add-item → remove-item → details → store → show/confirmation. The landing page, waitlist, and cancel flows are explicitly out of scope (per the spec's non-goals) and are not modified.
- No visual customization (colors/fonts) of the widget for v1.
- The admin embed page requires no `manage` gate — any restaurant member can view it (matches `RestaurantPolicy::view`).

---

### Task 1: Embed session cookie middleware + embed flag threading through the controller

**Files:**
- Create: `app/Http/Middleware/SetEmbedSessionCookieAttributes.php`
- Modify: `bootstrap/app.php` (register middleware alias)
- Modify: `routes/web.php:42-51` (attach the new middleware to the public booking routes)
- Modify: `app/Http/Controllers/PublicSite/BookingController.php` (compute and pass/propagate `$embed` on every action)
- Test: `tests/Feature/EmbedSessionCookieTest.php`
- Test: `tests/Feature/EmbedQueryFlagPropagationTest.php`

**Interfaces:**
- Produces: `SetEmbedSessionCookieAttributes` middleware, registered under the alias `embed_session_cookie`.
- Produces: every `BookingController` action that renders a public booking view now includes an `$embed` boolean in its `compact()` call (consumed by Task 2's layout changes).
- Produces: every redirect inside the booking journey (`addItem`, `details`'s empty-cart redirect, `store`) carries an `embed` query parameter of `'1'` (string) when the incoming request had `embed=1`, and omits it (native `null`, which Laravel's `route()` helper drops from the query string) otherwise.

- [ ] **Step 1: Write the failing cookie-attribute test**

Create `tests/Feature/EmbedSessionCookieTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmbedSessionCookieTest extends TestCase
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

    private function sessionCookie($response)
    {
        $name = config('session.cookie');

        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $name) {
                return $cookie;
            }
        }

        return null;
    }

    public function test_embed_request_gets_a_partitioned_cross_site_session_cookie(): void
    {
        $restaurant = $this->makeRestaurant();

        $response = $this->get("/r/{$restaurant->slug}/book?embed=1");

        $cookie = $this->sessionCookie($response);
        $this->assertNotNull($cookie);
        $this->assertSame('none', $cookie->getSameSite());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isPartitioned());
    }

    public function test_non_embed_request_keeps_the_default_lax_session_cookie(): void
    {
        $restaurant = $this->makeRestaurant();

        $response = $this->get("/r/{$restaurant->slug}/book");

        $cookie = $this->sessionCookie($response);
        $this->assertNotNull($cookie);
        $this->assertSame('lax', $cookie->getSameSite());
        $this->assertFalse($cookie->isPartitioned());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=EmbedSessionCookieTest`
Expected: FAIL — both assertions fail because no middleware exists yet to change the cookie attributes (the default `same_site` is `lax` and `partitioned` is `false` for every request).

- [ ] **Step 3: Create the middleware**

Create `app/Http/Middleware/SetEmbedSessionCookieAttributes.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetEmbedSessionCookieAttributes
{
    /**
     * Gives the session cookie CHIPS-partitioned, cross-site-safe attributes
     * when the request is inside an embedded iframe (embed=1), so the guest
     * booking cart survives across steps even when the browser blocks
     * third-party cookies (Safari ITP, Firefox). Left untouched otherwise.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->boolean('embed')) {
            config([
                'session.same_site' => 'none',
                'session.secure' => true,
                'session.partitioned' => true,
            ]);
        }

        return $next($request);
    }
}
```

- [ ] **Step 4: Register the middleware alias**

In `bootstrap/app.php`, add the import and alias:

```php
use App\Http\Middleware\SetEmbedSessionCookieAttributes;
```

Add to the `$middleware->alias([...])` array (after the existing entries):

```php
            'embed_session_cookie' => SetEmbedSessionCookieAttributes::class,
```

- [ ] **Step 5: Attach the middleware to the public booking routes**

In `routes/web.php`, the public booking route group currently reads (lines 42-51):

```php
        Route::get('/book', [PublicBookingController::class, 'create'])->name('public.booking.create');
        Route::post('/book/add-item', [PublicBookingController::class, 'addItem'])
            ->middleware('throttle:public-booking')
            ->name('public.booking.add-item');
        Route::post('/book/remove-item', [PublicBookingController::class, 'removeItem'])->name('public.booking.remove-item');
        Route::get('/book/details', [PublicBookingController::class, 'details'])->name('public.booking.details');
        Route::post('/book/details', [PublicBookingController::class, 'store'])
            ->middleware('throttle:public-booking')
            ->name('public.booking.store');
        Route::get('/booking/{public_id}', [PublicBookingController::class, 'show'])->name('public.booking.show');
```

Replace with:

```php
        Route::get('/book', [PublicBookingController::class, 'create'])
            ->middleware('embed_session_cookie')
            ->name('public.booking.create');
        Route::post('/book/add-item', [PublicBookingController::class, 'addItem'])
            ->middleware(['embed_session_cookie', 'throttle:public-booking'])
            ->name('public.booking.add-item');
        Route::post('/book/remove-item', [PublicBookingController::class, 'removeItem'])
            ->middleware('embed_session_cookie')
            ->name('public.booking.remove-item');
        Route::get('/book/details', [PublicBookingController::class, 'details'])
            ->middleware('embed_session_cookie')
            ->name('public.booking.details');
        Route::post('/book/details', [PublicBookingController::class, 'store'])
            ->middleware(['embed_session_cookie', 'throttle:public-booking'])
            ->name('public.booking.store');
        Route::get('/booking/{public_id}', [PublicBookingController::class, 'show'])
            ->middleware('embed_session_cookie')
            ->name('public.booking.show');
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=EmbedSessionCookieTest`
Expected: PASS (2 tests)

- [ ] **Step 7: Write the failing query-flag propagation test**

Create `tests/Feature/EmbedQueryFlagPropagationTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmbedQueryFlagPropagationTest extends TestCase
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

    private function makeResource(Restaurant $restaurant): Resource
    {
        return Resource::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Table 1',
            'type' => 'TABLE',
            'capacity_min' => 2,
            'capacity_max' => 4,
            'active' => true,
        ]);
    }

    public function test_add_item_redirect_carries_embed_flag(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = $this->makeResource($restaurant);

        $response = $this->post("/r/{$restaurant->slug}/book/add-item?embed=1", [
            'resource_id' => $resource->id,
            'start_time_local' => '2026-09-01 18:00',
            'end_time_local' => '2026-09-01 20:00',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('embed=1', $response->headers->get('Location'));
    }

    public function test_add_item_redirect_omits_embed_flag_when_absent(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = $this->makeResource($restaurant);

        $response = $this->post("/r/{$restaurant->slug}/book/add-item", [
            'resource_id' => $resource->id,
            'start_time_local' => '2026-09-01 18:00',
            'end_time_local' => '2026-09-01 20:00',
        ]);

        $response->assertRedirect();
        $this->assertStringNotContainsString('embed=', $response->headers->get('Location'));
    }

    public function test_details_empty_cart_redirect_carries_embed_flag(): void
    {
        $restaurant = $this->makeRestaurant();

        $response = $this->get("/r/{$restaurant->slug}/book/details?embed=1");

        $response->assertRedirect();
        $this->assertStringContainsString('embed=1', $response->headers->get('Location'));
    }

    public function test_store_redirect_to_show_carries_embed_flag(): void
    {
        Notification::fake();

        $restaurant = $this->makeRestaurant();
        $resource = $this->makeResource($restaurant);

        $items = [[
            'resource_id' => $resource->id,
            'resource_name' => $resource->name,
            'start_time_local' => '2026-09-01 18:00',
            'end_time_local' => '2026-09-01 20:00',
        ]];

        $response = $this
            ->withSession(["booking_wizard.{$restaurant->id}.items" => $items])
            ->post("/r/{$restaurant->slug}/book/details?embed=1", [
                'customer_name' => 'Anna Andersson',
                'email' => 'anna@example.com',
                'party_size' => 2,
            ]);

        $response->assertRedirect();
        $this->assertStringContainsString('embed=1', $response->headers->get('Location'));
    }
}
```

- [ ] **Step 8: Run test to verify it fails**

Run: `php artisan test --filter=EmbedQueryFlagPropagationTest`
Expected: FAIL — `addItem`'s and `details`'s redirects don't yet include `embed`, and `store`'s redirect to `show` doesn't either (the first test may pass by coincidence of `null` producing no query string, but the `embed=1` assertions will fail).

- [ ] **Step 9: Thread `$embed` through the controller**

Modify `app/Http/Controllers/PublicSite/BookingController.php`:

In `create()`, after the existing `$selectedItems = $this->selectedItems($request, $restaurant->id);` line, add:

```php
        $embed = $request->boolean('embed');
```

Change the `return view(...)` line from:

```php
        return view('public.book', compact('restaurant', 'slots', 'resourceType', 'date', 'partySize', 'duration', 'selectedItems'));
```

to:

```php
        return view('public.book', compact('restaurant', 'slots', 'resourceType', 'date', 'partySize', 'duration', 'selectedItems', 'embed'));
```

In `addItem()`, after `$restaurant = $request->attributes->get('restaurant');`, add:

```php
        $embed = $request->boolean('embed');
```

Change the redirect at the end of `addItem()` from:

```php
        return redirect()
            ->route('public.booking.create', [
                'slug' => $restaurant->slug,
                'resource_type' => $data['resource_type'] ?? null,
                'date' => $data['date'] ?? null,
                'party_size' => $data['party_size'] ?? null,
                'duration_minutes' => $data['duration_minutes'] ?? null,
            ])
            ->with('status', $duplicate ? 'Aktiviteten fanns redan i bokningen.' : 'Aktivitet tillagd. Välj fler eller gå vidare.');
```

to:

```php
        return redirect()
            ->route('public.booking.create', [
                'slug' => $restaurant->slug,
                'resource_type' => $data['resource_type'] ?? null,
                'date' => $data['date'] ?? null,
                'party_size' => $data['party_size'] ?? null,
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'embed' => $embed ? '1' : null,
            ])
            ->with('status', $duplicate ? 'Aktiviteten fanns redan i bokningen.' : 'Aktivitet tillagd. Välj fler eller gå vidare.');
```

In `details()`, after `$restaurant = $request->attributes->get('restaurant');`, add:

```php
        $embed = $request->boolean('embed');
```

Change the empty-cart redirect from:

```php
        if (empty($selectedItems)) {
            return redirect()
                ->route('public.booking.create', ['slug' => $restaurant->slug])
                ->with('status', 'Lägg till minst en aktivitet först.');
        }
```

to:

```php
        if (empty($selectedItems)) {
            return redirect()
                ->route('public.booking.create', ['slug' => $restaurant->slug, 'embed' => $embed ? '1' : null])
                ->with('status', 'Lägg till minst en aktivitet först.');
        }
```

Change the `return view(...)` line at the end of `details()` from:

```php
        return view('public.booking-details', compact('restaurant', 'selectedItems', 'menuItems', 'serveTimeMin', 'serveTimeMax', 'hasTableBooking', 'partySize'));
```

to:

```php
        return view('public.booking-details', compact('restaurant', 'selectedItems', 'menuItems', 'serveTimeMin', 'serveTimeMax', 'hasTableBooking', 'partySize', 'embed'));
```

In `store()`, after `$restaurant = $request->attributes->get('restaurant');`, add:

```php
        $embed = $request->boolean('embed');
```

Change the final redirect of `store()` from:

```php
        return redirect()->route('public.booking.show', [
            'slug' => $restaurant->slug,
            'public_id' => $booking->public_id,
        ]);
```

to:

```php
        return redirect()->route('public.booking.show', [
            'slug' => $restaurant->slug,
            'public_id' => $booking->public_id,
            'embed' => $embed ? '1' : null,
        ]);
```

In `show()`, after `$restaurant = $request->attributes->get('restaurant');`, add:

```php
        $embed = $request->boolean('embed');
```

Change the `return view(...)` line of `show()` from:

```php
        return view('public.booking-confirmation', compact('restaurant', 'booking'));
```

to:

```php
        return view('public.booking-confirmation', compact('restaurant', 'booking', 'embed'));
```

- [ ] **Step 10: Run tests to verify they pass**

Run: `php artisan test --filter=EmbedQueryFlagPropagationTest`
Expected: PASS (4 tests)

Run: `php artisan test --filter=EmbedSessionCookieTest`
Expected: PASS (2 tests, still — confirms Step 9's controller changes didn't disturb Step 1-6's middleware behavior)

- [ ] **Step 11: Commit**

```bash
git add app/Http/Middleware/SetEmbedSessionCookieAttributes.php bootstrap/app.php routes/web.php app/Http/Controllers/PublicSite/BookingController.php tests/Feature/EmbedSessionCookieTest.php tests/Feature/EmbedQueryFlagPropagationTest.php
git commit -m "feat: add embed session cookie middleware and thread embed flag through the booking controller"
```

---

### Task 2: Stripped-down embed layout, auto-resize script, and view-level embed threading

**Files:**
- Modify: `resources/views/layouts/public.blade.php`
- Modify: `resources/views/public/book.blade.php`
- Modify: `resources/views/public/booking-details.blade.php`
- Modify: `resources/views/public/booking-confirmation.blade.php`
- Test: `tests/Feature/EmbedLayoutTest.php`

**Interfaces:**
- Consumes: the `$embed` boolean already passed by every `BookingController` view call (Task 1).
- Produces: `<x-public-layout :restaurant="$restaurant" :embed="$embed">` is the new required usage in every public booking view (consumed by nothing further — this is the leaf UI layer).

- [ ] **Step 1: Write the failing layout test**

Create `tests/Feature/EmbedLayoutTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Resource;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmbedLayoutTest extends TestCase
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

    private function makeResource(Restaurant $restaurant): Resource
    {
        return Resource::query()->create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Table 1',
            'type' => 'TABLE',
            'capacity_min' => 2,
            'capacity_max' => 4,
            'active' => true,
        ]);
    }

    public function test_embed_request_strips_footer_and_reports_height(): void
    {
        $restaurant = $this->makeRestaurant();

        $response = $this->get("/r/{$restaurant->slug}/book?embed=1");

        $response->assertOk();
        $response->assertDontSee('Bokningssystem av');
        $response->assertSee('venueflowEmbedHeight');
    }

    public function test_non_embed_request_keeps_the_full_layout(): void
    {
        $restaurant = $this->makeRestaurant();

        $response = $this->get("/r/{$restaurant->slug}/book");

        $response->assertOk();
        $response->assertSee('Bokningssystem av');
        $response->assertDontSee('venueflowEmbedHeight');
    }

    public function test_book_page_add_item_form_carries_embed_hidden_field(): void
    {
        $restaurant = $this->makeRestaurant();
        $this->makeResource($restaurant);

        $response = $this->get("/r/{$restaurant->slug}/book?embed=1&resource_type=TABLE&date=2026-09-01&party_size=2&duration_minutes=60");

        $response->assertOk();
        $response->assertSee('name="embed" value="1"', false);
    }

    public function test_booking_details_page_carries_embed_through_form_and_link(): void
    {
        $restaurant = $this->makeRestaurant();
        $resource = $this->makeResource($restaurant);

        $items = [[
            'resource_id' => $resource->id,
            'resource_name' => $resource->name,
            'start_time_local' => '2026-09-01 18:00',
            'end_time_local' => '2026-09-01 20:00',
        ]];

        $response = $this
            ->withSession(["booking_wizard.{$restaurant->id}.items" => $items])
            ->get("/r/{$restaurant->slug}/book/details?embed=1");

        $response->assertOk();
        $response->assertSee('name="embed" value="1"', false);
        $response->assertSee(route('public.booking.create', ['slug' => $restaurant->slug, 'embed' => '1']), false);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=EmbedLayoutTest`
Expected: FAIL — the layout doesn't yet branch on `$embed`, and no view threads the hidden `embed` field or link yet.

- [ ] **Step 3: Update the shared public layout**

Read the current file (`resources/views/layouts/public.blade.php`) before editing — it currently renders a `<div class="pub-shell relative min-h-[100dvh] ...">` wrapping a background-image div, a gradient-vignette div, `<main>{{ $slot }}</main>`, and a `<footer>` with the "Bokningssystem av" attribution.

Replace the `<body>` element's contents (everything from `<body class="font-body antialiased">` to the closing `</body>`) with:

```blade
    <body class="font-body antialiased">
        @php
            $embed = $embed ?? false;
            $slugSeed = $restaurant?->slug ?? 'venueflow';
            $rotationSeed = crc32($slugSeed) + (int) now()->dayOfYear;
            $backgroundDirs = [
                'images/restaurant-backgrounds',
                'images/restaurantbackgrounds',
                'public-images-restaurantbackgrounds',
            ];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
            $bgCandidates = collect($backgroundDirs)
                ->flatMap(function (string $dir) use ($allowedExtensions) {
                    $path = public_path($dir);
                    if (! is_dir($path)) return [];
                    return collect(\Illuminate\Support\Facades\File::files($path))
                        ->filter(fn ($file) => in_array(strtolower($file->getExtension()), $allowedExtensions, true))
                        ->map(fn ($file) => $dir.'/'.$file->getFilename())
                        ->all();
                })
                ->sort()->values()->all();

            $bgImagePath = count($bgCandidates)
                ? $bgCandidates[$rotationSeed % count($bgCandidates)]
                : null;
        @endphp

        <div class="pub-shell relative {{ $embed ? '' : 'min-h-[100dvh]' }} overflow-x-hidden bg-[#0c0c0c] text-white">

            @unless($embed)
                {{-- Background image --}}
                @if($bgImagePath)
                    <div
                        class="pointer-events-none fixed inset-0 bg-cover bg-center bg-no-repeat"
                        style="background-image: url('{{ asset($bgImagePath) }}'); opacity: 0.18;"
                        aria-hidden="true"
                    ></div>
                @endif

                {{-- Gradient vignette - darker at edges, lighter center to let photo breathe --}}
                <div
                    class="pointer-events-none fixed inset-0"
                    style="background: linear-gradient(to bottom, rgba(12,12,12,0.85) 0%, rgba(12,12,12,0.45) 40%, rgba(12,12,12,0.75) 100%);"
                    aria-hidden="true"
                ></div>
            @endunless

            <main class="relative z-10">
                {{ $slot }}
            </main>

            @unless($embed)
                <footer class="relative z-10 border-t py-7" style="border-color:rgba(255,255,255,0.07)">
                    <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 sm:flex-row sm:px-10">
                        <p class="font-display text-[11px] font-semibold tracking-[0.18em] text-white/25" style="text-transform:uppercase">
                            {{ $restaurant->name ?? 'VenueFlow' }}
                        </p>
                        <p class="text-xs text-white/25">
                            Bokningssystem av
                            <a href="https://alexahman.se" target="_blank" rel="noopener noreferrer"
                               class="text-white/40 underline underline-offset-4 transition hover:text-white/70">
                                AlexAhman.se
                            </a>
                        </p>
                    </div>
                </footer>
            @endunless
        </div>

        @if($embed)
            <script>
                (function () {
                    function reportHeight() {
                        window.parent.postMessage({ venueflowEmbedHeight: document.documentElement.scrollHeight }, '*');
                    }
                    new ResizeObserver(reportHeight).observe(document.body);
                    window.addEventListener('load', reportHeight);
                    reportHeight();
                })();
            </script>
        @endif
    </body>
```

- [ ] **Step 4: Pass `:embed` from every public booking view**

In `resources/views/public/book.blade.php`, change the opening tag from:

```blade
<x-public-layout :restaurant="$restaurant">
```

to:

```blade
<x-public-layout :restaurant="$restaurant" :embed="$embed">
```

In the same file, add a hidden embed field to the "find availability" GET form. Change:

```blade
                    <form method="GET" class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
```

to:

```blade
                    <form method="GET" class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @if($embed)
                            <input type="hidden" name="embed" value="1">
                        @endif
```

Add a hidden embed field to the add-item POST form. Change:

```blade
                    <form method="POST" action="{{ route('public.booking.add-item', $restaurant->slug) }}" class="mt-5 space-y-4">
                        @csrf
                        <input type="hidden" name="resource_type" value="{{ $resourceType }}">
```

to:

```blade
                    <form method="POST" action="{{ route('public.booking.add-item', $restaurant->slug) }}" class="mt-5 space-y-4">
                        @csrf
                        @if($embed)
                            <input type="hidden" name="embed" value="1">
                        @endif
                        <input type="hidden" name="resource_type" value="{{ $resourceType }}">
```

Add a hidden embed field to the remove-item POST form. Change:

```blade
                            <form method="POST" action="{{ route('public.booking.remove-item', $restaurant->slug) }}">
                                @csrf
                                <input type="hidden" name="index" value="{{ $index }}">
```

to:

```blade
                            <form method="POST" action="{{ route('public.booking.remove-item', $restaurant->slug) }}">
                                @csrf
                                @if($embed)
                                    <input type="hidden" name="embed" value="1">
                                @endif
                                <input type="hidden" name="index" value="{{ $index }}">
```

Append the embed flag to the "Fortsätt →" link to step 2. Change:

```blade
                            <a href="{{ route('public.booking.details', $restaurant->slug) }}" class="pub-btn-primary block w-full text-center">
                                Fortsätt →
                            </a>
```

to:

```blade
                            <a href="{{ route('public.booking.details', ['slug' => $restaurant->slug, 'embed' => $embed ? '1' : null]) }}" class="pub-btn-primary block w-full text-center">
                                Fortsätt →
                            </a>
```

- [ ] **Step 5: Thread embed through the details page**

In `resources/views/public/booking-details.blade.php`, change the opening tag from:

```blade
<x-public-layout :restaurant="$restaurant">
```

to:

```blade
<x-public-layout :restaurant="$restaurant" :embed="$embed">
```

Append the embed flag to the "Ändra aktiviteter" link. Change:

```blade
                        <a href="{{ route('public.booking.create', $restaurant->slug) }}" class="pub-btn-secondary block w-full text-center text-xs">
                            ← Ändra aktiviteter
                        </a>
```

to:

```blade
                        <a href="{{ route('public.booking.create', ['slug' => $restaurant->slug, 'embed' => $embed ? '1' : null]) }}" class="pub-btn-secondary block w-full text-center text-xs">
                            ← Ändra aktiviteter
                        </a>
```

Add a hidden embed field to the store POST form. Change:

```blade
                    <form method="POST" action="{{ route('public.booking.store', $restaurant->slug) }}" class="space-y-8">
                        @csrf
```

to:

```blade
                    <form method="POST" action="{{ route('public.booking.store', $restaurant->slug) }}" class="space-y-8">
                        @csrf
                        @if($embed)
                            <input type="hidden" name="embed" value="1">
                        @endif
```

- [ ] **Step 6: Thread embed through the confirmation page**

In `resources/views/public/booking-confirmation.blade.php`, change the opening tag from:

```blade
<x-public-layout :restaurant="$restaurant">
```

to:

```blade
<x-public-layout :restaurant="$restaurant" :embed="$embed">
```

Append the embed flag to the "Gör en ny bokning" link only (the "Till startsidan" link intentionally breaks out of the widget to the full site, unchanged). Change:

```blade
                     <a href="{{ route('public.booking.create', $restaurant->slug) }}" class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Gör en ny bokning
                    </a>
```

to:

```blade
                     <a href="{{ route('public.booking.create', ['slug' => $restaurant->slug, 'embed' => $embed ? '1' : null]) }}" class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Gör en ny bokning
                    </a>
```

- [ ] **Step 7: Run tests to verify they pass**

Run: `php artisan test --filter=EmbedLayoutTest`
Expected: PASS (4 tests)

Run the full Task 1 tests again to confirm no regression:
`php artisan test --filter=EmbedSessionCookieTest`
`php artisan test --filter=EmbedQueryFlagPropagationTest`
Expected: PASS (2 tests, 4 tests)

- [ ] **Step 8: Commit**

```bash
git add resources/views/layouts/public.blade.php resources/views/public/book.blade.php resources/views/public/booking-details.blade.php resources/views/public/booking-confirmation.blade.php tests/Feature/EmbedLayoutTest.php
git commit -m "feat: strip embed layout to fit an iframe and thread the embed flag through every booking-flow view"
```

---

### Task 3: `embed.js` loader and the admin "Embed widget" page

**Files:**
- Create: `public/embed.js`
- Create: `app/Http/Controllers/RestaurantAdmin/EmbedController.php`
- Create: `resources/views/restaurant-admin/embed/show.blade.php`
- Modify: `routes/web.php` (add the admin embed route)
- Modify: `resources/views/components/restaurant-admin-nav.blade.php` (add nav link)
- Test: `tests/Feature/EmbedAdminPageTest.php`

**Interfaces:**
- Consumes: `RestaurantPolicy::view($user, $restaurant)` (existing — returns true for any member or super admin).
- Consumes: the `restaurant.admin.*` route group's existing middleware stack (`auth`, `verified`, `demo_read_only_admin`, `resolve_restaurant:any`, `restaurant_member`, `tenant_bindings`).
- Produces: route `restaurant.admin.embed.show` (`GET /r/{slug}/admin/embed`).
- Produces: static asset served at `/embed.js` (no route needed — `public/` is the document root).

- [ ] **Step 1: Write the failing admin page test**

Create `tests/Feature/EmbedAdminPageTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Enums\MembershipRole;
use App\Enums\StaffRole;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmbedAdminPageTest extends TestCase
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

    public function test_any_staff_member_can_view_the_embed_page(): void
    {
        $restaurant = $this->makeRestaurant();
        $staff = $this->staffFor($restaurant);

        $this->actingAs($staff)
            ->get("/r/{$restaurant->slug}/admin/embed")
            ->assertOk()
            ->assertSee('data-slug="'.$restaurant->slug.'"', false);
    }

    public function test_member_of_another_tenant_cannot_view_the_embed_page(): void
    {
        $restaurantA = $this->makeRestaurant('restaurant-a');
        $restaurantB = $this->makeRestaurant('restaurant-b');
        $staffB = $this->staffFor($restaurantB);

        $this->actingAs($staffB)
            ->get("/r/{$restaurantA->slug}/admin/embed")
            ->assertForbidden();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=EmbedAdminPageTest`
Expected: FAIL with a 404 (route doesn't exist yet).

- [ ] **Step 3: Create the embed.js loader**

Create `public/embed.js`:

```js
(function () {
    var script = document.currentScript;
    var slug = script.getAttribute('data-slug');
    var origin = new URL(script.src).origin;

    var iframe = document.createElement('iframe');
    iframe.src = origin + '/r/' + slug + '/book?embed=1';
    iframe.style.cssText = 'width:100%;border:0;display:block;min-height:400px';
    iframe.setAttribute('scrolling', 'no');
    script.insertAdjacentElement('afterend', iframe);

    window.addEventListener('message', function (event) {
        if (event.origin !== origin) return;
        if (event.data && typeof event.data.venueflowEmbedHeight === 'number') {
            iframe.style.height = event.data.venueflowEmbedHeight + 'px';
        }
    });
})();
```

- [ ] **Step 4: Create the admin controller**

Create `app/Http/Controllers/RestaurantAdmin/EmbedController.php`:

```php
<?php

namespace App\Http\Controllers\RestaurantAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmbedController extends Controller
{
    public function show(Request $request)
    {
        $restaurant = $request->attributes->get('restaurant');
        $this->authorize('view', $restaurant);

        $embedSnippet = sprintf(
            '<script src="%s" data-slug="%s"></script>',
            url('/embed.js'),
            $restaurant->slug
        );

        return view('restaurant-admin.embed.show', compact('restaurant', 'embedSnippet'));
    }
}
```

- [ ] **Step 5: Create the admin view**

Create `resources/views/restaurant-admin/embed/show.blade.php`:

```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Bädda in bokning · {{ $restaurant->name }}</h2>
    </x-slot>

    <section class="vf-container space-y-6 py-8">
        <x-restaurant-admin-nav :restaurant="$restaurant" />

        <div class="vf-card p-5" x-data="{ copied: false, snippet: {{ Illuminate\Support\Js::from($embedSnippet) }} }">
            <h3 class="text-lg font-semibold">Bädda in bokningsflödet på er egen webbplats</h3>
            <p class="mt-1 text-sm text-slate-600">
                Klistra in koden nedan var som helst på er sida. Bokningsflödet visas där taggen placeras och anpassar sin höjd automatiskt.
            </p>

            <div class="mt-4 flex items-start gap-2">
                <code class="flex-1 overflow-x-auto rounded-lg bg-slate-900 px-4 py-3 text-xs text-emerald-300">{{ $embedSnippet }}</code>
                <button
                    type="button"
                    class="vf-btn-secondary shrink-0"
                    @click="navigator.clipboard.writeText(snippet); copied = true; setTimeout(() => copied = false, 2000)"
                >
                    <span x-show="!copied">Kopiera</span>
                    <span x-show="copied" x-cloak>Kopierat!</span>
                </button>
            </div>
        </div>

        <div class="vf-card p-5">
            <h3 class="text-lg font-semibold">Förhandsvisning</h3>
            <p class="mt-1 text-sm text-slate-600">Så här ser det ut när er bokningswidget är inbäddad.</p>
            <div class="mt-4 max-w-md">
                {!! $embedSnippet !!}
            </div>
        </div>
    </section>
</x-app-layout>
```

- [ ] **Step 6: Add the admin route**

In `routes/web.php`, add the import:

```php
use App\Http\Controllers\RestaurantAdmin\EmbedController;
```

Inside the `restaurant.admin.*` group (after the `restaurant.admin.analytics` route), add:

```php
        Route::get('/embed', [EmbedController::class, 'show'])->name('restaurant.admin.embed.show');
```

- [ ] **Step 7: Add the nav link**

In `resources/views/components/restaurant-admin-nav.blade.php`, change:

```blade
            <a class="vf-btn-secondary" href="{{ route('restaurant.admin.analytics', $restaurant->slug) }}">Analys</a>
```

to:

```blade
            <a class="vf-btn-secondary" href="{{ route('restaurant.admin.analytics', $restaurant->slug) }}">Analys</a>
            <a class="vf-btn-secondary" href="{{ route('restaurant.admin.embed.show', $restaurant->slug) }}">Bädda in</a>
```

- [ ] **Step 8: Run test to verify it passes**

Run: `php artisan test --filter=EmbedAdminPageTest`
Expected: PASS (2 tests)

- [ ] **Step 9: Manual verification of embed.js**

This step has no automated test (per the spec's testing plan — `embed.js` is static JS with no backend logic). Verify by hand:

1. Start the app locally (`php artisan serve`).
2. Create a throwaway HTML file anywhere with:
   ```html
   <!DOCTYPE html><html><body>
   <h1>Some venue's own website</h1>
   <script src="http://localhost:8000/embed.js" data-slug="golfbaren"></script>
   </body></html>
   ```
3. Open it in a browser. Confirm the booking widget appears inline, resizes as you move between steps, and completing a booking end-to-end works.
4. Repeat in Safari specifically, watching dev tools' Application/Storage panel to confirm the session cookie is present and marked `Partitioned`, and that the cart persists across steps (add an item, then confirm it's still there on the details page).

- [ ] **Step 10: Commit**

```bash
git add public/embed.js app/Http/Controllers/RestaurantAdmin/EmbedController.php resources/views/restaurant-admin/embed/show.blade.php routes/web.php resources/views/components/restaurant-admin-nav.blade.php tests/Feature/EmbedAdminPageTest.php
git commit -m "feat: add embed.js loader and the admin embed widget page"
```

---

## Self-Review

**Spec coverage:**
- `embed=1` flag + stripped layout: Task 2. ✅
- Flag threaded through every link/form/redirect in create → add-item → remove-item → details → store → show: Task 1 (controller/redirects) + Task 2 (views). ✅
- `SetEmbedSessionCookieAttributes` middleware, CHIPS partitioned cookie, scoped to embed requests only: Task 1. ✅
- `embed.js` loader with postMessage auto-resize: Task 3. ✅
- Inline resize-reporting script via `ResizeObserver` + `postMessage`: Task 2. ✅
- Admin embed page with copy snippet + live preview, no `manage` gate: Task 3. ✅
- Testing plan (cookie attributes, flag propagation, layout stripping, admin page authorization): Tasks 1-3. Manual verification of `embed.js` itself: Task 3, Step 9.

**Deliberate deviations from the spec's illustrative code** (documented here per this project's established pattern of flagging such corrections rather than silently diverging):
- The spec's `embed.js` example hardcodes `https://venueflow.alexahman.se`. The implementation instead derives the origin from `document.currentScript.src` at runtime. This is necessary for the loader to work in any environment (local dev, a future staging domain) without a code change, and doesn't contradict anything the spec requires — it only specifies *what* the loader must do (build an iframe, listen for postMessage), not that the domain be literally hardcoded.
- The `SetEmbedSessionCookieAttributes` middleware is registered as an alias and attached per-route (not prepended into the global `web` middleware group), and works correctly despite `StartSession`'s "before" phase running first, because Laravel's `StartSession::addCookieToResponse()` reads `config('session.*')` fresh *after* `$next($request)` resolves — i.e., after our middleware and the controller have already run. This was verified directly against the framework source before writing this plan; no global middleware registration was needed.
- The `postMessage` call inside the embed layout's resize script uses `'*'` as the target origin, since the iframe cannot know in advance which domain will embed it. Only a non-sensitive height number is sent, so this is a deliberate, safe simplification — the *inbound* message listener in `embed.js` does check `event.origin` strictly.

**Placeholder scan:** none found — every step has complete, exact code.

**Type consistency:** `$embed` is a `bool` everywhere in PHP (via `$request->boolean('embed')`); only converted to the string `'1'` (or `null`) at the point of building a `route()` query-parameter array, consistent across all four call sites (Task 1's `addItem`, `details`, `store`, and Task 2's Blade links).
