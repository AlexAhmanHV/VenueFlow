<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Admins · {{ $restaurant->name }}</h2>
    </x-slot>

    <section class="vf-container space-y-5 py-8">
        <div class="vf-card p-6">
            <h3 class="text-lg font-semibold">Lägg till restaurangadmin</h3>
            <form method="POST" action="{{ route('platform.restaurants.invite-admin', $restaurant) }}" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                @csrf
                <input name="name" placeholder="Namn" class="vf-input" required>
                <input name="email" type="email" placeholder="E-post" class="vf-input" required>
                <button class="vf-btn-primary">Lägg till admin</button>
            </form>
        </div>

        <div class="vf-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Namn</th>
                        <th class="px-4 py-3 text-left">E-post</th>
                        <th class="px-4 py-3 text-left">Roll</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($admins as $membership)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $membership->user->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $membership->user->email }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{{ $membership->role->value }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-slate-500">Inga admins tillagda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
