<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Kapacitetsanalys · {{ $restaurant->name }}</h2>
    </x-slot>

    <section class="vf-container space-y-6 py-8">
        <x-restaurant-admin-nav :restaurant="$restaurant" />

        {{-- Range selector --}}
        <div class="flex flex-wrap gap-2">
            @foreach([7 => '7 dagar', 30 => '30 dagar', 90 => '90 dagar', 365 => '1 år'] as $days => $label)
                <a
                    href="{{ route('restaurant.admin.analytics', ['slug' => $restaurant->slug, 'range' => $days]) }}"
                    class="rounded-lg border px-4 py-2 text-sm font-medium transition
                        {{ $range === $days
                            ? 'border-indigo-600 bg-indigo-600 text-white'
                            : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200' }}"
                >{{ $label }}</a>
            @endforeach
        </div>

        {{-- KPI cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            @foreach([
                ['Totalt bokningar', $totalBookings, 'text-slate-700'],
                ['Bekräftade', $confirmedCount, 'text-emerald-600'],
                ['Avbokade', $cancelledCount, 'text-rose-500'],
                ['Uteblev', $noShowCount, 'text-amber-500'],
                ['Incheckade', $checkedInCount, 'text-indigo-600'],
                ['Totalt gäster', $totalGuests, 'text-slate-700'],
            ] as [$label, $value, $color])
                <div class="vf-card p-5 text-center">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500">{{ $label }}</p>
                    <p class="mt-1 text-3xl font-bold {{ $color }}">{{ number_format($value) }}</p>
                </div>
            @endforeach
        </div>

        {{-- Bookings per day --}}
        <div class="vf-card p-6">
            <h3 class="mb-4 text-base font-semibold text-slate-700 dark:text-slate-200">Bokningar per dag</h3>
            @if($byDay->isEmpty())
                <p class="text-sm text-slate-500">Inga bokningar i valt intervall.</p>
            @else
                @php $maxDay = $byDay->max() ?: 1; @endphp
                <div class="space-y-2">
                    @foreach($byDay as $date => $cnt)
                        <div class="flex items-center gap-3 text-sm">
                            <span class="w-24 shrink-0 text-slate-500">{{ $date }}</span>
                            <div class="relative flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700" style="height:12px">
                                <div
                                    class="absolute inset-y-0 left-0 rounded-full bg-indigo-500"
                                    style="width:{{ round($cnt / $maxDay * 100) }}%"
                                ></div>
                            </div>
                            <span class="w-6 shrink-0 text-right font-semibold text-slate-700 dark:text-slate-200">{{ $cnt }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Peak hour --}}
        <div class="vf-card p-6">
            <h3 class="mb-4 text-base font-semibold text-slate-700 dark:text-slate-200">Populäraste timmarna</h3>
            @if($byHour->isEmpty())
                <p class="text-sm text-slate-500">Inga bokningsrader i valt intervall.</p>
            @else
                @php $maxHour = $byHour->max() ?: 1; @endphp
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8">
                    @foreach(range(0, 23) as $h)
                        @php $cnt = $byHour->get($h, 0); @endphp
                        <div class="flex flex-col items-center gap-1">
                            <div class="relative w-full overflow-hidden rounded bg-slate-100 dark:bg-slate-700" style="height:60px">
                                <div
                                    class="absolute bottom-0 left-0 right-0 rounded bg-indigo-400"
                                    style="height:{{ $cnt ? round($cnt / $maxHour * 100) : 0 }}%"
                                ></div>
                            </div>
                            <span class="text-xs text-slate-500">{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}</span>
                            @if($cnt)
                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $cnt }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
