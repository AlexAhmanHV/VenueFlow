<x-restaurant-admin-layout>

    @php
        $todayLabel = \Carbon\Carbon::now($restaurant->timezone ?? 'Europe/Stockholm')
            ->locale('sv')
            ->isoFormat('dddd D MMMM');
    @endphp

    {{-- Page header --}}
    <div class="mb-8 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <h1 class="font-display text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
            {{ $restaurant->name }}
        </h1>
        <p class="text-sm text-slate-400 dark:text-slate-500">{{ ucfirst($todayLabel) }}</p>
    </div>

    {{-- Stats: 3-column asymmetric --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

        <div class="rounded-2xl border border-black/[0.08] bg-white p-6 dark:border-white/10 dark:bg-slate-800">
            <p class="font-display text-5xl font-black tabular-nums tracking-tight text-slate-900 dark:text-white">
                {{ $bookingsToday }}
            </p>
            <div class="mt-3 border-t border-black/[0.06] pt-3 dark:border-white/[0.08]">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Bokningar idag</p>
            </div>
        </div>

        <div class="rounded-2xl border border-black/[0.08] bg-white p-6 dark:border-white/10 dark:bg-slate-800">
            <p class="font-display text-5xl font-black tabular-nums tracking-tight text-slate-900 dark:text-white">
                {{ $checkedInToday }}
            </p>
            <div class="mt-3 border-t border-black/[0.06] pt-3 dark:border-white/[0.08]">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Incheckade idag</p>
            </div>
        </div>

        <div class="rounded-2xl border border-black/[0.08] bg-white p-6 dark:border-white/10 dark:bg-slate-800">
            <div class="flex items-start justify-between gap-4">
                <p class="font-display text-5xl font-black tabular-nums tracking-tight text-slate-900 dark:text-white">
                    {{ $occupancyRate }}<span class="text-2xl font-bold text-slate-300 dark:text-slate-600">%</span>
                </p>
                @if($noShowsToday > 0)
                    <div class="shrink-0 text-right">
                        <p class="font-display text-xl font-bold tabular-nums text-rose-500">{{ $noShowsToday }}</p>
                        <p class="text-xs text-slate-400">no-show</p>
                    </div>
                @endif
            </div>
            <div class="mt-4 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                <div
                    class="h-full rounded-full bg-emerald-600 transition-all duration-700"
                    style="width: {{ min((int)$occupancyRate, 100) }}%"
                ></div>
            </div>
            <div class="mt-3 border-t border-black/[0.06] pt-3 dark:border-white/[0.08]">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Beläggning</p>
            </div>
        </div>
    </div>

    {{-- Second row: bookings table + revenue --}}
    <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-[1fr_280px]">

        {{-- Upcoming bookings --}}
        <div class="rounded-2xl border border-black/[0.08] bg-white p-6 dark:border-white/10 dark:bg-slate-800">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Kommande bokningar</h2>

            @if($upcomingBookings->count() > 0)
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-black/[0.06] dark:border-white/[0.08]">
                                <th class="pb-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Kund</th>
                                <th class="pb-3 pl-6 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">Tid</th>
                                <th class="pb-3 pl-6 text-right text-xs font-semibold uppercase tracking-wider text-slate-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.05]">
                            @foreach($upcomingBookings as $booking)
                                <tr>
                                    <td class="py-3.5 text-sm font-medium text-slate-800 dark:text-slate-200">
                                        {{ $booking->customer_name }}
                                    </td>
                                    <td class="py-3.5 pl-6 text-sm tabular-nums text-slate-500">
                                        {{ $booking->created_at->timezone($restaurant->timezone)->format('H:i') }}
                                    </td>
                                    <td class="py-3.5 pl-6 text-right">
                                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                            {{ $booking->status->value }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="mt-6 rounded-xl border border-dashed border-slate-200 p-8 text-center dark:border-slate-700">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Inga bokningar att visa</p>
                    <p class="mt-1 text-xs text-slate-400">Allt lugnt just nu.</p>
                </div>
            @endif
        </div>

        {{-- Preorder revenue --}}
        <div class="rounded-2xl border border-black/[0.08] bg-white p-6 dark:border-white/10 dark:bg-slate-800">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Förbeställningar</h2>
            <div class="mt-5">
                <p class="font-display text-4xl font-bold tabular-nums tracking-tight text-slate-900 dark:text-white">
                    {{ number_format((float)$preorderRevenueToday, 0, ',', '\u{00a0}') }}
                </p>
                <p class="mt-0.5 text-sm font-medium text-slate-400">kr idag</p>
            </div>
        </div>

    </div>

</x-restaurant-admin-layout>
