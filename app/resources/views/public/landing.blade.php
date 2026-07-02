<x-public-layout :restaurant="$restaurant">

    @php
        $resourceCount = $restaurant->resources()->where('active', true)->count();
        $todayLabel = \Carbon\Carbon::now($restaurant->timezone ?? 'Europe/Stockholm')
            ->locale('sv')
            ->isoFormat('dddd D MMMM');
    @endphp

    {{-- ── HERO ──────────────────────────────────────────────────── --}}
    <section class="mx-auto max-w-6xl px-6 pb-16 pt-20 sm:px-10 sm:pt-24 lg:px-8 lg:pt-28">

        <div class="grid items-end gap-12 lg:grid-cols-[1fr_320px] lg:gap-10">

            {{-- Left: main content --}}
            <div>
                <h1 class="pub-reveal font-display text-[clamp(3.6rem,9vw,7.5rem)] font-extrabold leading-[0.88] tracking-tight text-white"
                    style="text-wrap:balance">
                    {{ $restaurant->name }}
                </h1>

                <p class="pub-reveal-2 mt-7 max-w-[38ch] text-base leading-7 text-white/50">
                    Boka aktiviteter, mat och events direkt - utan konto och utan krångel.
                </p>

                <div class="pub-reveal-3 mt-9 flex flex-wrap items-center gap-5">
                    <a
                        href="{{ route('public.booking.create', $restaurant->slug) }}"
                        class="inline-flex items-center gap-2.5 px-7 py-3.5 font-display text-sm font-bold text-white transition-all duration-150 active:scale-[0.98]"
                        style="background-color:#c1121f; border-radius:0; letter-spacing:0.04em"
                        onmouseover="this.style.backgroundColor='#a30e19'"
                        onmouseout="this.style.backgroundColor='#c1121f'"
                    >
                        Boka nu
                        <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>

                    <a
                        href="{{ route('public.menu', $restaurant->slug) }}"
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-white/45 transition-colors duration-150 hover:text-white/80"
                        style="border-bottom:1px solid rgba(255,255,255,0.15); padding-bottom:2px"
                    >
                        Se menyn
                    </a>
                </div>
            </div>

            {{-- Right: live panel --}}
            <div class="pub-reveal-r">
                <div
                    class="rounded-2xl p-6"
                    style="background-color:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); backdrop-filter:blur(8px)"
                >
                    {{-- Live indicator --}}
                    <div class="flex items-center gap-2.5">
                        <span class="relative flex h-2.5 w-2.5 shrink-0">
                            <span class="pub-pulse-dot absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-80"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        </span>
                        <span class="text-xs font-semibold text-emerald-400" style="letter-spacing:0.1em; text-transform:uppercase">
                            Bokningar öppna
                        </span>
                    </div>

                    {{-- Resource count --}}
                    <div class="mt-5 border-t pt-5" style="border-color:rgba(255,255,255,0.08)">
                        @if($resourceCount > 0)
                            <p class="font-display text-3xl font-bold text-white">{{ $resourceCount }}</p>
                            <p class="mt-1 text-sm text-white/45">
                                {{ $resourceCount === 1 ? 'aktivitet tillgänglig' : 'aktiviteter tillgängliga' }}
                            </p>
                        @else
                            <p class="font-display text-lg font-bold text-white">Kö öppen</p>
                            <p class="mt-1 text-sm text-white/45">Inga aktiva aktiviteter just nu</p>
                        @endif
                    </div>

                    {{-- Date --}}
                    <div class="mt-4 border-t pt-4" style="border-color:rgba(255,255,255,0.06)">
                        <p class="text-xs text-white/30">{{ ucfirst($todayLabel) }}</p>
                    </div>

                    @if($resourceCount === 0)
                    <div class="mt-5">
                        <a
                            href="{{ route('public.waitlist.create', $restaurant->slug) }}"
                            class="inline-flex items-center gap-1.5 text-xs font-medium text-white/45 transition hover:text-white/70"
                            style="border-bottom:1px solid rgba(255,255,255,0.12); padding-bottom:1px"
                        >
                            Ställ dig i kö
                        </a>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </section>

    {{-- ── HOW IT WORKS ──────────────────────────────────────────── --}}
    <section class="border-t" style="border-color:rgba(255,255,255,0.07)">
        <div class="mx-auto max-w-6xl px-6 py-12 sm:px-10 lg:px-8">
            <ol class="grid gap-0 divide-y sm:grid-cols-3 sm:divide-x sm:divide-y-0" style="--tw-divide-opacity:1; border-color:rgba(255,255,255,0.07)">
                @foreach([
                    ['Välj aktivitet', 'Bläddra bland tillgängliga aktiviteter och tider.'],
                    ['Ange uppgifter', 'Namn, antal personer - klart på 30 sekunder.'],
                    ['Bekräftelse direkt', 'Få bokning bekräftad utan konto eller väntan.'],
                ] as $i => [$step, $desc])
                <li class="px-0 py-8 sm:px-7 sm:py-0 sm:first:pl-0 sm:last:pr-0">
                    <p class="text-[11px] font-medium text-white/20" style="letter-spacing:0.12em; text-transform:uppercase">{{ $i + 1 }}/3</p>
                    <p class="mt-3 font-display text-base font-semibold text-white">{{ $step }}</p>
                    <p class="mt-1.5 text-sm leading-6 text-white/40">{{ $desc }}</p>
                </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ── INFO STRIP ────────────────────────────────────────────── --}}
    @if($restaurant->phone || $restaurant->email)
    <section class="border-t" style="border-color:rgba(255,255,255,0.07); background-color:rgba(0,0,0,0.25)">
        <div class="mx-auto max-w-6xl px-6 sm:px-10 lg:px-8">
            <div class="flex flex-col gap-0 divide-y sm:flex-row sm:divide-x sm:divide-y-0" style="border-color:rgba(255,255,255,0.07)">

                @if($restaurant->phone)
                <div class="flex flex-col gap-1 py-6 sm:pr-10">
                    <span class="text-[10px] font-medium text-white/30" style="letter-spacing:0.15em; text-transform:uppercase">Telefon</span>
                    <a href="tel:{{ $restaurant->phone }}" class="text-sm font-medium text-white/70 transition hover:text-white">
                        {{ $restaurant->phone }}
                    </a>
                </div>
                @endif

                @if($restaurant->email)
                <div class="flex flex-col gap-1 py-6 sm:px-10">
                    <span class="text-[10px] font-medium text-white/30" style="letter-spacing:0.15em; text-transform:uppercase">E-post</span>
                    <a href="mailto:{{ $restaurant->email }}" class="text-sm font-medium text-white/70 transition hover:text-white">
                        {{ $restaurant->email }}
                    </a>
                </div>
                @endif

                <div class="flex flex-col gap-1 py-6 sm:pl-10">
                    <span class="text-[10px] font-medium text-white/30" style="letter-spacing:0.15em; text-transform:uppercase">Ingen ledig tid?</span>
                    <a
                        href="{{ route('public.waitlist.create', $restaurant->slug) }}"
                        class="text-sm font-medium transition"
                        style="color:rgba(193,18,31,0.85)"
                        onmouseover="this.style.color='#c1121f'"
                        onmouseout="this.style.color='rgba(193,18,31,0.85)'"
                    >
                        Ställ dig i kö &rarr;
                    </a>
                </div>

            </div>
        </div>
    </section>
    @endif

</x-public-layout>
