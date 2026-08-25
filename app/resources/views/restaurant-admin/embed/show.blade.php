<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Bädda in bokning · {{ $restaurant->name }}</h2>
    </x-slot>

    <section class="vf-container space-y-6 py-8">
        <x-restaurant-admin-nav :restaurant="$restaurant" />

        <div class="vf-card p-5" x-data="{ copied: false, snippet: {{ Illuminate\Support\Js::from($embedSnippet) }} }">
            <h3 class="text-lg font-semibold">Bädda in bokningsflödet på er egen webbplats</h3>
            <p class="mt-1 text-sm text-slate-600">
                Klistra in koden nedan var som helst på er sida. Bokningsflödet visas där taggen placeras och anpassar sin höjd automatiskt.
            </p>

            <div class="mt-4 flex items-start gap-2">
                <code class="flex-1 overflow-x-auto rounded-lg bg-slate-900 px-4 py-3 text-xs text-emerald-300">{{ $embedSnippet }}</code>
                <button
                    type="button"
                    class="vf-btn-secondary shrink-0"
                    @click="navigator.clipboard.writeText(snippet); copied = true; setTimeout(() => copied = false, 2000)"
                >
                    <span x-show="!copied">Kopiera</span>
                    <span x-show="copied" x-cloak>Kopierat!</span>
                </button>
            </div>
        </div>

        <div class="vf-card p-5">
            <h3 class="text-lg font-semibold">Förhandsvisning</h3>
            <p class="mt-1 text-sm text-slate-600">Så här ser det ut när er bokningswidget är inbäddad.</p>
            <div class="mt-4 max-w-md">
                {!! $embedSnippet !!}
            </div>
        </div>
    </section>
</x-app-layout>
