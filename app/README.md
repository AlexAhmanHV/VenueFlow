# VenueFlow (Laravel 11, PHP 8.3)

Multi-tenant bokningssystem för aktivitets-/restaurangställen.

## Stack
- Laravel 11
- PHP 8.3
- Postgres (primärt, Supabase-kompatibelt)
- Laravel Breeze (Blade + Tailwind)
- Mail via `MAIL_MAILER=log`

## Multi-tenant modell
- Tenant-scope via `restaurant_id` på alla tenant-tabeller.
- Publik identifiering via slug: `/r/{slug}`.
- Roller:
  - `SUPER_ADMIN` (plattform)
  - `RESTAURANT_ADMIN` (per restaurang)
  - `STAFF` (per restaurang)
- Medlemskap: `restaurant_memberships`.
- Admin-rutter har automatisk tenant-validering av route-bound modeller via middleware `tenant_bindings`.

## Supabase setup (viktigt)
1. Skapa ett Supabase-projekt.
2. Gå till Database -> Connect och hämta **Direct connection** (inte pooler för migrations).
3. Sätt `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=...
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=...
DB_SSLMODE=require
MAIL_MAILER=log
```

4. Kör migration + seed:

```bash
php artisan migrate
php artisan db:seed
```

Notering: Supabase API-nycklar behövs inte. Appen använder endast Postgres-anslutning.

## Lokal start
```bash
composer install
npm install
npm run build
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Demo-seed
- SuperAdmin
  - email: `super@demo.test`
  - password: `password`
- Read-only owner demo (public mode)
  - email: `owner@demo.test`
  - password: `password`
- Restaurang: `Golfbaren` (`/r/golfbaren`)
- Resurser, öppettider (alla dagar 12:00-23:00) och menyartiklar skapas.

## Public demo mode (recommended in production demo)
- Set in `.env`:
  - `DEMO_PUBLIC_MODE=true`
  - `DEMO_FULL_ACCESS_KEY=<secret>`
  - `DEMO_PRIVILEGED_EMAILS=super@demo.test`
  - `DEMO_READ_ONLY_EMAILS=owner@demo.test`
- In this mode, privileged routes and privileged login are locked until
  session unlock at `/demo/full-access`.
- Restaurant admin pages are viewable with read-only owner account. Write
  actions in `/r/{slug}/admin/*` require unlocked full access.

## Testflöde
1. Logga in på `/login` med `super@demo.test` / `password`.
2. Gå till `/platform/restaurants` och skapa restaurang eller lägg till första admin via `/platform/restaurants/{id}/admins`.
3. Gå till `/r/golfbaren/book` och skapa bokning som gäst (utan konto).
4. Bekräftelse visas på `/r/golfbaren/booking/{public_id}`.
5. Avbokningslänk skickas via mail-logg (`storage/logs/laravel.log`) och använder hashad token.
6. Staff/Admin ser bokningar på `/r/golfbaren/admin/bookings`.

## Tidzon och UTC
- Restaurang har `timezone` (default `Europe/Stockholm`).
- Bokningstider sparas i UTC.
- Slot-generering och UI visas i restaurangens lokala tid.

## Konfliktkontroll
- Överlapp: `new.start < existing.end AND new.end > existing.start`.
- Buffert (per restaurang i `restaurant_settings`) inkluderas i tillgänglighet + bokningskontroll.
- Skapande av bokning sker i DB-transaktion med `lockForUpdate()`.
- För PostgreSQL läggs dessutom en DB-level `EXCLUDE` constraint på `booking_items` (GIST + tsrange) för hård dubbelbokningsspärr.
- Vid avbokning soft-deletas `booking_items` så tidigare tider frigörs.

## Nya funktioner (utbyggnad)
- Tvåstegsbokning: välj aktiviteter i steg 1 och slutför i steg 2.
- Multi-aktivitet i samma bokning (`booking_items` 1..N).
- Realtids hold av slot i 5 minuter via `booking_slot_holds`.
- Staff live board: `/r/{slug}/admin/bookings/live-board`.
- Snabbnoteringar per bokning (`booking_notes`).
- Dashboard med nyckeltal: bokningar idag, incheckade, no-show, beläggning (estimat), förbeställningsintäkt.
- Rate limiting på publika boknings-POST routes.
- Bokningsmail skickas queue-bart (notification implementerar `ShouldQueue`).
- Restauranginställningar i admin:
  - buffert, avbokningsgräns, slot-intervall, max samtidiga bokningar, standardlängd per aktivitet.
- Personalnivåer inom restaurang: `STAFF` och `MANAGER` (utöver `RESTAURANT_ADMIN`).
- Daglig driftvy: `/r/{slug}/admin/operations`.
- SuperAdmin kan sätta start-aktiviteter vid skapande av restaurang och hantera aktiviteter på `/platform/restaurants/{restaurant}/activities`.

## Kör tester
```bash
php artisan test
```

## Render deployment (utan databortfall)
- Använd Supabase som persistent Postgres.
- I Render, sätt Post Deploy Command till:

```bash
php artisan migrate --force
php artisan db:seed --class=DemoSeeder --force
```

- Kör inte:
  - `php artisan migrate:fresh`
- `DemoSeeder` är idempotent och fyller bara på saknad demodata (t.ex. `golfbaren`, resurser, öppettider, grundmeny) utan att rensa befintlig data.
