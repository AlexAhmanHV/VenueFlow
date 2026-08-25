# Embeddable Booking Widget — Design

## Overview

Restaurants/venues using VenueFlow currently send guests to `venueflow.alexahman.se/r/{slug}` to book. This feature lets a venue embed the booking flow directly on their own website: they paste a single `<script>` tag, and a fully working, self-resizing booking widget appears inline on their page.

## Goals

- A venue can copy one `<script>` snippet from their VenueFlow admin panel and paste it anywhere on their own site to get a working booking flow.
- The embedded flow covers the entire booking journey (pick activity → add details → confirmation), not just the first step.
- The iframe resizes itself to fit its content as the visitor moves through steps — no scrollbars, no manual height guessing.
- The flow works correctly in Safari and Firefox, which block third-party cookies by default.

## Non-goals (out of scope for this spec)

- A CORS-enabled JSON API or a JS-rendered (non-iframe) widget. The iframe reuses the existing server-rendered booking flow as-is.
- Visual customization of the widget (colors, fonts, spacing) for the venue's brand. All venues get the same VenueFlow-styled embed for v1.
- Multiple simultaneous widgets with shared state on one host page. Including the script twice will naturally produce two independent iframes, but this isn't a designed-for use case.
- Any change to the non-embedded booking flow's behavior or appearance.

## Architecture

The embed reuses the existing public booking flow (`App\Http\Controllers\PublicSite\BookingController`, routes already under `/r/{slug}/book*`) almost entirely unchanged. Three additions make it embeddable:

1. **`embed=1` query flag.** When present, the booking flow renders with a stripped-down layout (no background art, no footer) sized to fit cleanly in an iframe, and the flag is carried through every internal link, form action, and redirect in the flow (create → add-item → details → store → show/confirmation) so the whole journey stays embedded.

2. **`embed.js` loader.** A static JS file served from the app that a venue includes via:
   ```html
   <script src="https://venueflow.alexahman.se/embed.js" data-slug="golfbaren"></script>
   ```
   It builds an `<iframe src="https://venueflow.alexahman.se/r/golfbaren/book?embed=1">`, inserts it immediately after the script tag, and listens for `postMessage` height updates from the iframe to resize it. No build step, no dependencies — plain vanilla JS, since it runs on a third-party page that can't assume anything else is loaded.

   On the embedded-page side, the `embed=1` layout variant includes a small inline script that reports its content height on load and on any layout change (via `ResizeObserver`, covering step navigation and validation errors) by posting `{venueflowEmbedHeight: <px>}` to the parent window.

3. **Partitioned session cookie for embed requests.** The guest booking cart lives in the PHP session. Inside a cross-origin iframe, that session cookie is a third-party cookie from the browser's perspective — Safari (ITP) and Firefox block these by default, which would silently break cart persistence between steps. A new `SetEmbedSessionCookieAttributes` middleware, applied only to `embed=1`-flagged public booking routes, overrides the session cookie attributes for that request to `SameSite=None; Secure; Partitioned` (the CHIPS standard, natively supported by Laravel 11's `session.same_site` / `session.secure` / `session.partitioned` config keys). This is scoped to embed requests only — the rest of the site (admin login, non-embedded guest booking) keeps the current `Lax` default untouched.

## Admin UI: Embed widget page

A new page in the restaurant admin, alongside Settings:

- Route: `GET /r/{slug}/admin/embed` → `EmbedController@show`, under the existing `restaurant.admin.*` route group (same `resolve_restaurant:any`, `restaurant_member`, `tenant_bindings` middleware stack already wrapping that group). No `manage` gate — any staff member can view it, since it's read-only and non-destructive.
- Shows the ready-to-copy `<script>` snippet for that restaurant's slug, with a "Copy" button.
- A live preview: the actual embed, rendered via the real `embed.js` loader inside a representative-width container (e.g. `max-w-md`) — not a static mockup — so the admin sees exactly what a visitor would see.
- A short explanation of what to do with the snippet.
- Added to `restaurant-admin-nav.blade.php` alongside the existing links.

## Testing plan (Pest feature tests)

- A request through an `embed=1`-flagged route receives `Set-Cookie` with `SameSite=None; Secure; Partitioned`; the same route without `embed=1` keeps the default `Lax` cookie (regression guard that the change is scoped correctly).
- Starting the flow with `?embed=1` and progressing through add-item → details → store → show, every redirect and link still carries `embed=1` and every response uses the stripped-down layout.
- Without `embed=1`, the flow renders exactly as it does today (regression guard — background art and footer present).
- Any restaurant member (not just managers) can view `/r/{slug}/admin/embed`; it renders that tenant's correct slug in the snippet; a member of a different tenant cannot access another restaurant's embed page (covered by the existing `restaurant_member`/`tenant_bindings` middleware, confirmed to apply here too).
- `embed.js` itself is not covered by Pest (static JS, no backend logic) — verified manually during implementation by embedding it in a throwaway HTML page and completing a full booking end-to-end, with particular attention to Safari's cookie behavior.

## Future ideas (not in this spec)

- Visual customization (accent color, font, border radius) to match the venue's branding.
- A JS-rendered (non-iframe) widget backed by a CORS API, for venues wanting deeper visual integration.
- Usage analytics on the admin embed page (e.g. bookings originated via the widget vs. the main site).
