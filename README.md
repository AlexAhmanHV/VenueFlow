## VenueFlow Additions (Menu Catalog)

- Demo Hub startsida finns pa `/` med lankar till alla huvudfloden.
- Inloggningsuppgifter for demo finns har i README (inte hardkodat i UI).
- SuperAdmin can now maintain a global dish catalog at `/platform/dish-templates`.
- SuperAdmin can now maintain a global drink catalog at `/platform/drink-templates`.
- Restaurant admins/managers can open `/r/{slug}/admin/menu` and add dishes from that catalog to the restaurant menu.
- Restaurant admins/managers can also add drinks from the drink catalog to the same restaurant menu.
- Imported dishes are saved in tenant-scoped `menu_items` (with `restaurant_id`) and can have restaurant-specific prices.
- You can now upload images when creating/updating dish templates, drink templates, and manual restaurant menu items.
- Existing images can be replaced or removed from template/menu edit forms.
- Uploaded files are saved in `public/uploads/...` and shown automatically in admin/public menu cards.

### Quick Test

1. Run migrations + seed:
   - `php artisan migrate:fresh --seed`
2. Open Demo Hub:
   - `http://127.0.0.1:8000/`
3. Login credentials:
   - `super@demo.test` / `password`
4. Open platform pages:
   - `/platform/dish-templates`
   - `/platform/drink-templates`
5. Open restaurant admin pages:
   - `/r/golfbaren/admin/dashboard`
   - `/r/golfbaren/admin/bookings/live-board`
   - `/r/golfbaren/admin/menu`
6. Verify public pages:
   - `/r/golfbaren/menu`
   - `/r/golfbaren/book/details`

### Menu Images (Food + Drinks)

- Local SVG images are generated for all dish templates, drink templates and menu items.
- Generate or refresh images anytime with:
  - `php artisan menu:generate-images`
- Images are stored under:
  - `public/images/menu`
- Uploaded images now use Laravel `public` storage (`storage/app/public/uploads/...`) with public URL via `/storage/...`.
- Run once if needed:
  - `php artisan storage:link`
- Image flow now includes:
  - Drag/drop upload
  - Preview before save
  - Auto center crop + resize/compression
  - WebP output when supported by GD
  - Replace and remove image in edit forms
  - Undo remove (20 min token-based restore)
- Cleanup command for expired soft-deleted images:
  - `php artisan images:cleanup-trash`
  - Scheduled daily at `03:15` via `routes/console.php`

### Menu Admin Upgrades

- Filter/search/sort controls on `/r/{slug}/admin/menu`.
- Bulk actions for selected items:
  - Activate / deactivate
  - Add/remove tag
  - Increase/decrease price by %
  - Remove image
  - Delete
- Drag-and-drop sort order for menu cards (persisted in `menu_items.sort_order`).
- Audit log of menu changes (actor + action + timestamps) shown at bottom of menu admin page.

### Tests Added

- `tests/Feature/MenuImageFlowTest.php` verifies:
  - Manual upload stores image on public storage
  - Replace/remove image behavior
  - Tenant isolation for bulk actions
  - Drag-sort reorder persistence

### Drag & Drop Rebooking (Admin)

- Live board now supports drag-and-drop moving of a booking item to a different resource/time slot.
- URL:
  - `/r/{slug}/admin/bookings/live-board`
- How it works:
  1. Open live board and select date.
  2. Drag a booking item card.
  3. Drop it on a target slot in the resource grid.
  4. Server validates conflict in a DB transaction with row locks and returns error/success.
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
