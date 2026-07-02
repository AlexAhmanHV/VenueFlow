<x-restaurant-admin-layout>

    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Personal</h1>
        <p class="mt-1 text-sm text-slate-400">Hantera anställda och deras roller.</p>
    </div>

    <div class="space-y-4">

        {{-- Add form --}}
        <div class="rounded-2xl border border-black/[0.08] bg-white p-6 dark:border-white/10 dark:bg-slate-800">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Lägg till personal</h2>
            <form method="POST" action="{{ route('restaurant.admin.staff.store', $restaurant->slug) }}"
                  class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-6">
                @csrf

                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Namn</label>
                    <input type="text" name="name" id="name"
                           class="mt-1.5 block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
                </div>

                <div class="sm:col-span-2">
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">E-post</label>
                    <input type="email" name="email" id="email"
                           class="mt-1.5 block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
                </div>

                <div class="sm:col-span-1">
                    <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Roll</label>
                    <select id="role" name="role"
                            class="mt-1.5 block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
                        @foreach(\App\Enums\StaffRole::cases() as $role)
                            <option value="{{ $role->value }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end sm:col-span-1">
                    <button type="submit" class="vf-btn-primary w-full">Lägg till</button>
                </div>
            </form>
        </div>

        {{-- Staff list --}}
        <div class="rounded-2xl border border-black/[0.08] bg-white dark:border-white/10 dark:bg-slate-800">
            @if($memberships->count() > 0)
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08]">
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Namn</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">E-post</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Roll</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.05]">
                        @foreach($memberships as $membership)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">{{ $membership->user->name }}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">{{ $membership->user->email }}</td>
                                <td class="px-6 py-4">
                                    @php $isManager = $membership->staff_role === \App\Enums\StaffRole::MANAGER; @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize
                                        {{ $isManager ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300' }}">
                                        {{ strtolower($membership->staff_role->value ?? $membership->role->value) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($membership->user_id !== auth()->id())
                                        <form method="POST" action="{{ route('restaurant.admin.staff.destroy', [$restaurant->slug, $membership]) }}"
                                              onsubmit="return confirm('Ta bort {{ $membership->user->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-rose-500 transition hover:text-rose-700">Ta bort</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-300">Du själv</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-12 text-center">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Ingen personal tillagd</p>
                    <p class="mt-1 text-xs text-slate-400">Använd formuläret ovan för att bjuda in den första.</p>
                </div>
            @endif
        </div>

    </div>

</x-restaurant-admin-layout>
