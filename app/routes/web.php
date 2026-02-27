<?php

use App\Http\Controllers\Platform\RestaurantController as PlatformRestaurantController;
use App\Http\Controllers\Platform\DishTemplateController as PlatformDishTemplateController;
use App\Http\Controllers\Platform\DrinkTemplateController as PlatformDrinkTemplateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicSite\BookingController as PublicBookingController;
use App\Http\Controllers\PublicSite\RestaurantController as PublicRestaurantController;
use App\Http\Controllers\MediaRestoreController;
use App\Http\Controllers\RestaurantAdmin\BookingController as AdminBookingController;
use App\Http\Controllers\RestaurantAdmin\DashboardController;
use App\Http\Controllers\RestaurantAdmin\MenuController as AdminMenuController;
use App\Http\Controllers\RestaurantAdmin\OperationsController;
use App\Http\Controllers\RestaurantAdmin\ResourceController as AdminResourceController;
use App\Http\Controllers\RestaurantAdmin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\RestaurantAdmin\SettingsController as AdminSettingsController;
use App\Http\Controllers\RestaurantAdmin\StaffController as AdminStaffController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'demo-hub')->name('home');

Route::middleware(['resolve_restaurant'])
    ->prefix('/r/{slug}')
    ->group(function () {
        Route::get('/', [PublicRestaurantController::class, 'landing'])->name('public.landing');
        Route::get('/menu', [PublicRestaurantController::class, 'menu'])->name('public.menu');
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
        Route::get('/cancel', [PublicBookingController::class, 'cancel'])->name('public.booking.cancel');
    });

Route::middleware(['auth', 'verified', 'super_admin'])
    ->prefix('/platform/restaurants')
    ->group(function () {
        Route::get('/', [PlatformRestaurantController::class, 'index'])->name('platform.restaurants.index');
        Route::get('/create', [PlatformRestaurantController::class, 'create'])->name('platform.restaurants.create');
        Route::post('/', [PlatformRestaurantController::class, 'store'])->name('platform.restaurants.store');
        Route::get('/{restaurant}/admins', [PlatformRestaurantController::class, 'admins'])->name('platform.restaurants.admins');
        Route::post('/{restaurant}/invite-admin', [PlatformRestaurantController::class, 'inviteAdmin'])->name('platform.restaurants.invite-admin');
        Route::get('/{restaurant}/activities', [PlatformRestaurantController::class, 'activities'])->name('platform.restaurants.activities');
        Route::post('/{restaurant}/activities', [PlatformRestaurantController::class, 'storeActivity'])->name('platform.restaurants.activities.store');
        Route::delete('/{restaurant}/activities/{resource}', [PlatformRestaurantController::class, 'destroyActivity'])->name('platform.restaurants.activities.destroy');
    });

Route::middleware(['auth', 'verified'])
    ->post('/media/restore/{token}', MediaRestoreController::class)
    ->name('media.restore');

Route::middleware(['auth', 'verified', 'super_admin'])
    ->prefix('/platform/dish-templates')
    ->group(function () {
        Route::get('/', [PlatformDishTemplateController::class, 'index'])->name('platform.dish-templates.index');
        Route::post('/', [PlatformDishTemplateController::class, 'store'])->name('platform.dish-templates.store');
        Route::put('/{dishTemplate}', [PlatformDishTemplateController::class, 'update'])->name('platform.dish-templates.update');
        Route::delete('/{dishTemplate}', [PlatformDishTemplateController::class, 'destroy'])->name('platform.dish-templates.destroy');
    });

Route::middleware(['auth', 'verified', 'super_admin'])
    ->prefix('/platform/drink-templates')
    ->group(function () {
        Route::get('/', [PlatformDrinkTemplateController::class, 'index'])->name('platform.drink-templates.index');
        Route::post('/', [PlatformDrinkTemplateController::class, 'store'])->name('platform.drink-templates.store');
        Route::put('/{drinkTemplate}', [PlatformDrinkTemplateController::class, 'update'])->name('platform.drink-templates.update');
        Route::delete('/{drinkTemplate}', [PlatformDrinkTemplateController::class, 'destroy'])->name('platform.drink-templates.destroy');
    });

Route::middleware(['auth', 'verified', 'resolve_restaurant:any', 'restaurant_member', 'tenant_bindings'])
    ->prefix('/r/{slug}/admin')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('restaurant.admin.dashboard');
        Route::get('/operations', OperationsController::class)->name('restaurant.admin.operations');

        Route::get('/resources', [AdminResourceController::class, 'index'])->name('restaurant.admin.resources.index');
        Route::post('/resources', [AdminResourceController::class, 'store'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.resources.store');
        Route::put('/resources/{resource}', [AdminResourceController::class, 'update'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.resources.update');
        Route::delete('/resources/{resource}', [AdminResourceController::class, 'destroy'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.resources.destroy');

        Route::get('/schedule', [AdminScheduleController::class, 'index'])->name('restaurant.admin.schedule.index');
        Route::post('/schedule/opening-hours', [AdminScheduleController::class, 'storeOpening'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.schedule.opening.store');
        Route::post('/schedule/blackout', [AdminScheduleController::class, 'storeBlackout'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.schedule.blackout.store');
        Route::delete('/schedule/blackout/{blackout}', [AdminScheduleController::class, 'destroyBlackout'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.schedule.blackout.destroy');

        Route::get('/settings', [AdminSettingsController::class, 'edit'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.settings.edit');
        Route::post('/settings', [AdminSettingsController::class, 'update'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.settings.update');

        Route::get('/menu', [AdminMenuController::class, 'index'])->name('restaurant.admin.menu.index');
        Route::post('/menu', [AdminMenuController::class, 'store'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.menu.store');
        Route::post('/menu/from-template', [AdminMenuController::class, 'storeFromTemplate'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.menu.store-template');
        Route::post('/menu/from-drink-template', [AdminMenuController::class, 'storeFromDrinkTemplate'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.menu.store-drink-template');
        Route::put('/menu/{menu}', [AdminMenuController::class, 'update'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.menu.update');
        Route::post('/menu/bulk', [AdminMenuController::class, 'bulkUpdate'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.menu.bulk');
        Route::post('/menu/reorder', [AdminMenuController::class, 'reorder'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.menu.reorder');
        Route::delete('/menu/{menu}', [AdminMenuController::class, 'destroy'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.menu.destroy');

        Route::get('/bookings', [AdminBookingController::class, 'index'])->name('restaurant.admin.bookings.index');
        Route::get('/bookings/live-board', [AdminBookingController::class, 'liveBoard'])->name('restaurant.admin.bookings.live');
        Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('restaurant.admin.bookings.show');
        Route::post('/bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('restaurant.admin.bookings.status');
        Route::post('/bookings/{booking}/note', [AdminBookingController::class, 'storeNote'])->name('restaurant.admin.bookings.note');
        Route::post('/bookings/{booking}/items/{item}/move', [AdminBookingController::class, 'moveItem'])->name('restaurant.admin.bookings.move-item');

        Route::get('/staff', [AdminStaffController::class, 'index'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.staff.index');
        Route::post('/staff', [AdminStaffController::class, 'store'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.staff.store');
        Route::delete('/staff/{membership}', [AdminStaffController::class, 'destroy'])
            ->middleware('restaurant_member:MANAGER')
            ->name('restaurant.admin.staff.destroy');
    });

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('platform.restaurants.index');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
