<x-public-layout :restaurant="$restaurant">

    {{-- HERO --}}
    <section class="flex min-h-[100dvh] flex-col justify-end px-6 pb-16 pt-24 sm:px-10 lg:px-20">
        <div class="max-w-5xl vf-reveal">

            <p class="font-display text-xs font-semibold uppercase tracking-[0.3em] text-red-500">
                Välkommen till
            </p>

            <h1 class="font-display mt-3 text-[clamp(4rem,12vw,10rem)] font-bold uppercase leading-none tracking-tight text-white" style="text-wrap:balance">
                {{ $restaurant->name }}
            </h1>

            <p class="mt-6 max-w-xl text-base leading-relaxed text-white/60">
                Boka bord, aktiviteter och events direkt — utan konto och utan krångel.
            </p>

            <div class="mt-10 flex flex-wrap gap-4">
                <a
                    href="{{ route('public.booking.create', $restaurant->slug) }}"
                    class="inline-flex items-center gap-2 bg-red-700 px-8 py-4 font-display text-sm font-semibold uppercase tracking-widest text-white transition duration-200 hover:bg-red-600 active:scale-[0.98]"
                >
                    Boka nu
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
                </a>
                <a
                    href="{{ route('public.menu', $restaurant->slug) }}"
                    class="inline-flex items-center gap-2 border border-white/30 px-8 py-4 font-display text-sm font-semibold uppercase tracking-widest text-white/80 transition duration-200 hover:border-white/60 hover:bg-white/10 hover:text-white active:scale-[0.98]"
                >
                    Se menyn
                </a>
            </div>
        </div>
    </section>

    {{-- INFO STRIP --}}
    <section class="relative z-10 border-t border-white/10 bg-black/40 backdrop-blur-md">
        <div class="mx-auto grid max-w-5xl grid-cols-1 divide-y divide-white/10 px-6 sm:grid-cols-3 sm:divide-x sm:divide-y-0 sm:px-10 lg:px-20">

            @if($restaurant->phone)
            <div class="flex flex-col gap-1 py-8 sm:px-8 sm:first:pl-0 sm:last:pr-0">
                <span class="font-display text-[10px] font-semibold uppercase tracking-[0.25em] text-white/40">Telefon</span>
                <span class="text-sm font-medium text-white/80">{{ $restaurant->phone }}</span>
            </div>
            @endif

            @if($restaurant->email)
            <div class="flex flex-col gap-1 py-8 sm:px-8">
                <span class="font-display text-[10px] font-semibold uppercase tracking-[0.25em] text-white/40">E-post</span>
                <span class="text-sm font-medium text-white/80">{{ $restaurant->email }}</span>
            </div>
            @endif

            <div class="flex flex-col gap-1 py-8 sm:px-8 sm:first:pl-0 sm:last:pr-0">
                <span class="font-display text-[10px] font-semibold uppercase tracking-[0.25em] text-white/40">Inga tider?</span>
                <a
                    href="{{ route('public.waitlist.create', $restaurant->slug) }}"
                    class="text-sm font-medium text-red-400 transition hover:text-red-300"
                >
                    Ställ dig i kö →
                </a>
            </div>
        </div>
    </section>

</x-public-layout>
