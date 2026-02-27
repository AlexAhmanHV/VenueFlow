<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Skapa restaurang</h2>
    </x-slot>

    <section class="vf-container py-8">
        <div class="mx-auto max-w-4xl vf-card p-6 sm:p-8">
            <h3 class="text-lg font-semibold">Ny restaurang</h3>
            <p class="mt-1 text-sm text-slate-600">Skapa restaurang och välj vilka aktiviteter som ska finnas från start.</p>

            <form method="POST" action="{{ route('platform.restaurants.store') }}" class="mt-6 grid gap-4 md:grid-cols-2">
                @csrf
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium">Namn</label>
                    <input name="name" placeholder="Namn" class="vf-input" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Slug</label>
                    <input name="slug" placeholder="t.ex. golfbaren" class="vf-input" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Tidszon</label>
                    <input name="timezone" value="Europe/Stockholm" class="vf-input" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Standardbuffert (min)</label>
                    <input name="default_buffer_minutes" type="number" value="10" class="vf-input" required>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Avbokningsgräns (min, valfri)</label>
                    <input name="cancellation_cutoff_minutes" type="number" placeholder="t.ex. 60" class="vf-input">
                </div>

                <div class="md:col-span-2 mt-2 rounded-xl border border-slate-200 p-4">
                    <h4 class="font-semibold">Start-aktiviteter</h4>
                    <p class="mt-1 text-sm text-slate-600">Ange hur många resurser du vill skapa av varje typ.</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <label class="text-sm">Golf<input type="number" min="0" name="activity_counts[GOLF]" value="0" class="vf-input mt-1"></label>
                        <label class="text-sm">Shuffleboard<input type="number" min="0" name="activity_counts[SHUFFLEBOARD]" value="0" class="vf-input mt-1"></label>
                        <label class="text-sm">Dart<input type="number" min="0" name="activity_counts[DART]" value="0" class="vf-input mt-1"></label>
                        <label class="text-sm">Biljard<input type="number" min="0" name="activity_counts[BILLIARDS]" value="0" class="vf-input mt-1"></label>
                        <label class="text-sm">Bord<input type="number" min="0" name="activity_counts[TABLE]" value="0" class="vf-input mt-1"></label>
                    </div>

                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="text-sm">Bordskapacitet min<input type="number" min="1" name="table_capacity_min" value="2" class="vf-input mt-1"></label>
                        <label class="text-sm">Bordskapacitet max<input type="number" min="1" name="table_capacity_max" value="8" class="vf-input mt-1"></label>
                    </div>
                </div>

                <div class="md:col-span-2 flex justify-end">
                    <button class="vf-btn-primary">Spara restaurang</button>
                </div>
            </form>
        </div>
    </section>
</x-app-layout>
