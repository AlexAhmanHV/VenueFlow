<x-restaurant-admin-layout>

    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Resurser</h1>
        <p class="mt-1 text-sm text-slate-400">Hantera bokningsbara ytor och aktiviteter.</p>
    </div>

    <div class="space-y-4">

        {{-- Add form --}}
        <div class="rounded-2xl border border-black/[0.08] bg-white p-6 dark:border-white/10 dark:bg-slate-800">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Skapa ny resurs</h2>
            <form method="POST" action="{{ route('restaurant.admin.resources.store', $restaurant->slug) }}"
                  class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-6">
                @csrf

                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Namn</label>
                    <input type="text" name="name" id="name" placeholder="T.ex. Bana 1, Bord 3"
                           class="mt-1.5 block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
                </div>

                <div class="sm:col-span-2">
                    <label for="type" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Typ</label>
                    <select id="type" name="type"
                            class="mt-1.5 block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
                        @foreach(\App\Enums\ResourceType::cases() as $type)
                            <option value="{{ $type->value }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-1">
                    <label for="capacity_min" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Min</label>
                    <input type="number" name="capacity_min" id="capacity_min" value="1"
                           class="mt-1.5 block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
                </div>

                <div class="sm:col-span-1">
                    <label for="capacity_max" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Max</label>
                    <input type="number" name="capacity_max" id="capacity_max" value="8"
                           class="mt-1.5 block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
                </div>

                <div class="flex items-end sm:col-span-6">
                    <button type="submit" class="vf-btn-primary">Skapa resurs</button>
                </div>
            </form>
        </div>

        {{-- Resource list --}}
        <div class="rounded-2xl border border-black/[0.08] bg-white dark:border-white/10 dark:bg-slate-800">
            @if($resources->count() > 0)
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08]">
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Namn</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Typ</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Kapacitet</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-400">Status</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.05]">
                        @foreach($resources as $resource)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">{{ $resource->name }}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                        {{ $resource->type->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm tabular-nums text-slate-500">{{ $resource->capacity_min }}–{{ $resource->capacity_max }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $resource->active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $resource->active ? 'Aktiv' : 'Inaktiv' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form method="POST" action="{{ route('restaurant.admin.resources.destroy', [$restaurant->slug, $resource]) }}"
                                          onsubmit="return confirm('Ta bort {{ $resource->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-rose-500 transition hover:text-rose-700">Ta bort</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-12 text-center">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Inga resurser skapade</p>
                    <p class="mt-1 text-xs text-slate-400">Använd formuläret ovan för att lägga till den första.</p>
                </div>
            @endif
        </div>

    </div>

</x-restaurant-admin-layout>
