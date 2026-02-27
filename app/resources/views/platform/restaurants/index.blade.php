<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-900">Plattform · Restauranger</h2>
    </x-slot>

    <section class="vf-container py-8 space-y-5">
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-slate-600">Hantera alla restauranger, admins och aktiviteter.</p>
            <div class="flex gap-2">
                <a class="vf-btn-secondary" href="{{ route('platform.dish-templates.index') }}">Rättkatalog</a>
                <a class="vf-btn-secondary" href="{{ route('platform.drink-templates.index') }}">Dryckeskatalog</a>
                <a class="vf-btn-primary" href="{{ route('platform.restaurants.create') }}">Skapa restaurang</a>
            </div>
        </div>

        <div class="vf-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Namn</th>
                        <th class="px-4 py-3 text-left">Slug</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Åtgärder</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($restaurants as $restaurant)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">{{ $restaurant->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $restaurant->slug }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $restaurant->active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $restaurant->active ? 'Aktiv' : 'Inaktiv' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a class="vf-btn-secondary" href="{{ route('platform.restaurants.admins', $restaurant) }}">Admins</a>
                                <a class="vf-btn-secondary" href="{{ route('platform.restaurants.activities', $restaurant) }}">Aktiviteter</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
