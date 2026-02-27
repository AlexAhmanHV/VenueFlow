<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Aktiviteter · {{ $restaurant->name }}</h2>
    </x-slot>

    <section class="vf-container space-y-5 py-8">
        <div class="vf-card p-6">
            <h3 class="text-lg font-semibold">Lägg till aktivitet</h3>
            <form method="POST" action="{{ route('platform.restaurants.activities.store', $restaurant) }}" class="mt-4 grid gap-3 md:grid-cols-5">
                @csrf
                <select name="type" class="vf-input">
                    @foreach(['GOLF','SHUFFLEBOARD','DART','BILLIARDS','TABLE'] as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
                <input name="name" placeholder="Namn" class="vf-input" required>
                <input name="capacity_min" type="number" min="1" value="1" class="vf-input" required>
                <input name="capacity_max" type="number" min="1" value="8" class="vf-input" required>
                <button class="vf-btn-primary">Lägg till</button>
            </form>
        </div>

        <div class="vf-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Namn</th>
                        <th class="px-4 py-3 text-left">Typ</th>
                        <th class="px-4 py-3 text-left">Kapacitet</th>
                        <th class="px-4 py-3 text-right">Åtgärd</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($resources as $resource)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $resource->name }}</td>
                            <td class="px-4 py-3">{{ $resource->type->value }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $resource->capacity_min }} - {{ $resource->capacity_max }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('platform.restaurants.activities.destroy', [$restaurant, $resource]) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50">Ta bort</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Inga aktiviteter skapade ännu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
