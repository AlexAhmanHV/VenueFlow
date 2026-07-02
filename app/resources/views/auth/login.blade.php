<x-public-layout>
    <div class="flex min-h-[100dvh] flex-col items-center justify-center px-5 py-16">
        <div class="w-full max-w-sm">

            <div class="mb-8">
                <a href="/" class="inline-flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500 text-[11px] font-black text-white">VF</div>
                    <span class="font-display text-base font-bold tracking-tight text-white">VenueFlow</span>
                </a>
                <h1 class="font-display mt-6 text-2xl font-bold tracking-tight text-white">Logga in</h1>
                <p class="mt-1 text-sm text-white/40">Hantera bokningar, resurser och personal.</p>
            </div>

            <div class="mb-6">
                <a
                    href="{{ route('demo.login') }}"
                    class="flex w-full items-center justify-center gap-2 rounded-none py-3 text-sm font-semibold text-emerald-400 transition hover:text-emerald-300 active:scale-[0.98]"
                    style="background-color:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.20)"
                >
                    <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M8 1.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13zM0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8z" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" opacity=".4"/>
                        <path d="M8 5a1 1 0 0 1 1 1v2h2a1 1 0 1 1 0 2H8a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1z" fill="currentColor"/>
                    </svg>
                    Prova som demo
                </a>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-white/40" style="letter-spacing:0.08em; text-transform:uppercase">E-postadress</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                           class="mt-2 block w-full rounded-none border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white placeholder-white/20 transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-xs font-semibold text-white/40" style="letter-spacing:0.08em; text-transform:uppercase">Lösenord</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-medium text-white/35 transition hover:text-white/70">Glömt lösenord?</a>
                        @endif
                    </div>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="mt-2 block w-full rounded-none border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white placeholder-white/20 transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center gap-2.5">
                    <input id="remember_me" name="remember" type="checkbox"
                           class="h-4 w-4 rounded border-white/20 bg-white/5 text-emerald-500 focus:ring-emerald-500">
                    <label for="remember_me" class="text-sm text-white/50">Kom ihåg mig</label>
                </div>

                <button type="submit"
                        class="mt-2 flex w-full justify-center rounded-none bg-emerald-500 px-3 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600 active:scale-[0.98]">
                    Logga in
                </button>
            </form>

            @if (Route::has('register'))
            <p class="mt-8 text-center text-sm text-white/30">
                Inget konto?
                <a href="{{ route('register') }}" class="font-semibold text-white/60 underline underline-offset-4 transition hover:text-white">Registrera dig</a>
            </p>
            @endif

        </div>
    </div>
</x-public-layout>
