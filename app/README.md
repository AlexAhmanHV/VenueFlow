# VenueFlow (Laravel 11, PHP 8.3)

Multi-tenant bokningssystem for aktivitets-/restaurangstallen.

## Stack
- Laravel 11
- PHP 8.3
- Postgres (primart, Supabase-kompatibelt)
- Laravel Breeze (Blade + Tailwind)
- Mail via `MAIL_MAILER=log`

## Multi-tenant modell
- Tenant-scope via `restaurant_id` pa alla tenant-tabeller.
- Publik identifiering via slug: `/r/{slug}`.
- Roller:
  - `SUPER_ADMIN` (plattform)
  - `RESTAURANT_ADMIN` (per restaurang)
  - `STAFF` (per restaurang)
- Medlemskap: `restaurant_memberships`.
- Admin-rutter har automatisk tenant-validering av route-bound modeller via middleware `tenant_bindings`.

## Supabase setup (viktigt)
1. Skapa ett Supabase-projekt.
2. Ga till Database -> Connect och hamta **Direct connection** (inte pooler for migrations).
3. Satt `.env`:

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

4. Kor migration + seed:

```bash
php artisan migrate
php artisan db:seed
```

Notering: Supabase API-nycklar behovs inte. Appen anvander endast Postgres-anslutning.

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
- Restaurang: `Golfbaren` (`/r/golfbaren`)
- Resurser, oppettider (alla dagar 12:00-23:00) och menyartiklar skapas.

## Testflode
1. Logga in pa `/login` med `super@demo.test` / `password`.
2. Ga till `/platform/restaurants` och skapa restaurang eller lagg till forsta admin via `/platform/restaurants/{id}/admins`.
3. Ga till `/r/golfbaren/book` och skapa bokning som gast (utan konto).
4. Bekraftelse visas pa `/r/golfbaren/booking/{public_id}`.
5. Avbokningslank skickas via mail-logg (`storage/logs/laravel.log`) och anvander hashad token.
6. Staff/Admin ser bokningar pa `/r/golfbaren/admin/bookings`.

## Tidzon och UTC
- Restaurang har `timezone` (default `Europe/Stockholm`).
- Bokningstider sparas i UTC.
- Slot-generering och UI visas i restaurangens lokala tid.

## Konfliktkontroll
- Overlapp: `new.start < existing.end AND new.end > existing.start`.
- Buffert (per restaurang i `restaurant_settings`) inkluderas i tillganglighet + bokningskontroll.
- Skapande av bokning sker i DB-transaktion med `lockForUpdate()`.
- For PostgreSQL laggs dessutom en DB-level `EXCLUDE` constraint pa `booking_items` (GIST + tsrange) for hard dubbelbokningssparr.
- Vid avbokning soft-deletas `booking_items` sa tidigare tider frigors.

## Nya funktioner (utbyggnad)
- Tvastegs-bokning: valj aktiviteter i steg 1 och slutför i steg 2.
- Multi-aktivitet i samma bokning (`booking_items` 1..N).
- Realtids hold av slot i 5 minuter via `booking_slot_holds`.
- Staff live board: `/r/{slug}/admin/bookings/live-board`.
- Snabbnoteringar per bokning (`booking_notes`).
- Dashboard med nyckeltal: bokningar idag, incheckade, no-show, belaggning (estimat), forbestallningsintakt.
- Rate limiting pa publika boknings-POST routes.
- Bokningsmail skickas queue-bart (notification implementerar `ShouldQueue`).
- Restauranginställningar i admin:
  - buffert, avbokningsgrans, slot-intervall, max samtidiga bokningar, standardlangd per aktivitet.
- Personalnivaer inom restaurang: `STAFF` och `MANAGER` (utover `RESTAURANT_ADMIN`).
- Daglig driftvy: `/r/{slug}/admin/operations`.
- SuperAdmin kan satta start-aktiviteter vid skapande av restaurang och hantera aktiviteter pa `/platform/restaurants/{restaurant}/activities`.

## Kor tester
```bash
php artisan test
```
