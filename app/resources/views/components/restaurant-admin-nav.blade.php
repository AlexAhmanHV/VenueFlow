@props(['restaurant'])

@php
    $user = auth()->user();
    $membership = $user?->membershipFor($restaurant);

    $isRestaurantAdmin = $membership?->role?->value === 'RESTAURANT_ADMIN';
    $isStaff = $membership?->role?->value === 'STAFF';
    $staffRole = $membership?->staff_role?->value;
    $isManager = $isRestaurantAdmin || ($isStaff && $staffRole === 'MANAGER');

    $roleLabel = $isRestaurantAdmin ? 'RESTAURANT_ADMIN' : ($staffRole ?? ($isStaff ? 'STAFF' : 'UNKNOWN'));

    $canManage = $isManager;
@endphp

<div class="vf-card p-4 sm:p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Intern admin</p>
            <p class="text-sm text-slate-700">Din roll: <span class="font-semibold">{{ $roleLabel }}</span></p>
        </div>

        <div class="flex flex-wrap gap-2 text-sm">
            <a class="vf-btn-secondary" href="{{ route('restaurant.admin.dashboard', $restaurant->slug) }}">Dashboard</a>
            <a class="vf-btn-secondary" href="{{ route('restaurant.admin.operations', $restaurant->slug) }}">Driftvy</a>
            <a class="vf-btn-secondary" href="{{ route('restaurant.admin.bookings.live', $restaurant->slug) }}">Live board</a>

            @if($canManage)
                <a class="vf-btn-secondary" href="{{ route('restaurant.admin.resources.index', $restaurant->slug) }}">Resurser</a>
                <a class="vf-btn-secondary" href="{{ route('restaurant.admin.schedule.index', $restaurant->slug) }}">Schema</a>
                <a class="vf-btn-secondary" href="{{ route('restaurant.admin.menu.index', $restaurant->slug) }}">Meny</a>
                <a class="vf-btn-secondary" href="{{ route('restaurant.admin.staff.index', $restaurant->slug) }}">Personal</a>
                <a class="vf-btn-secondary" href="{{ route('restaurant.admin.settings.edit', $restaurant->slug) }}">Inst&auml;llningar</a>
            @else
                <span class="vf-btn-secondary pointer-events-none opacity-45" aria-disabled="true" title="Kr&auml;ver MANAGER">Resurser</span>
                <span class="vf-btn-secondary pointer-events-none opacity-45" aria-disabled="true" title="Kr&auml;ver MANAGER">Schema</span>
                <span class="vf-btn-secondary pointer-events-none opacity-45" aria-disabled="true" title="Kr&auml;ver MANAGER">Meny</span>
                <span class="vf-btn-secondary pointer-events-none opacity-45" aria-disabled="true" title="Kr&auml;ver MANAGER">Personal</span>
                <span class="vf-btn-secondary pointer-events-none opacity-45" aria-disabled="true" title="Kr&auml;ver MANAGER">Inst&auml;llningar</span>
            @endif
        </div>
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
        <table class="min-w-full text-xs sm:text-sm">
            <thead class="bg-slate-50 text-slate-700">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold">Beh&ouml;righet</th>
                    <th class="px-3 py-2 text-center font-semibold">Staff</th>
                    <th class="px-3 py-2 text-center font-semibold">Manager</th>
                    <th class="px-3 py-2 text-center font-semibold">Restaurant Admin</th>
                    <th class="px-3 py-2 text-center font-semibold">SuperAdmin</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-slate-700">
                <tr>
                    <td class="px-3 py-2">Se bokningar + live board</td>
                    <td class="px-3 py-2 text-center text-emerald-600">&#10003;</td>
                    <td class="px-3 py-2 text-center text-emerald-600">&#10003;</td>
                    <td class="px-3 py-2 text-center text-emerald-600">&#10003;</td>
                    <td class="px-3 py-2 text-center text-slate-500">Via plattform</td>
                </tr>
                <tr>
                    <td class="px-3 py-2">Uppdatera status + noteringar</td>
                    <td class="px-3 py-2 text-center text-emerald-600">&#10003;</td>
                    <td class="px-3 py-2 text-center text-emerald-600">&#10003;</td>
                    <td class="px-3 py-2 text-center text-emerald-600">&#10003;</td>
                    <td class="px-3 py-2 text-center text-slate-500">Via plattform</td>
                </tr>
                <tr>
                    <td class="px-3 py-2">Hantera resurser, schema, meny</td>
                    <td class="px-3 py-2 text-center text-rose-500">&#8722;</td>
                    <td class="px-3 py-2 text-center text-emerald-600">&#10003;</td>
                    <td class="px-3 py-2 text-center text-emerald-600">&#10003;</td>
                    <td class="px-3 py-2 text-center text-slate-500">Via plattform</td>
                </tr>
                <tr>
                    <td class="px-3 py-2">Hantera personal + inst&auml;llningar</td>
                    <td class="px-3 py-2 text-center text-rose-500">&#8722;</td>
                    <td class="px-3 py-2 text-center text-emerald-600">&#10003;</td>
                    <td class="px-3 py-2 text-center text-emerald-600">&#10003;</td>
                    <td class="px-3 py-2 text-center text-slate-500">Via plattform</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if(! $canManage)
        <p class="mt-3 text-xs text-slate-500">Vissa genv&auml;gar &auml;r l&aring;sta f&ouml;r din roll. Be en manager/admin om ut&ouml;kad access.</p>
    @endif
</div>