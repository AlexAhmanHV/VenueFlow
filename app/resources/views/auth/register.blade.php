<x-public-layout>
    <div class="flex min-h-[100dvh] flex-col items-center justify-center px-5 py-16">
        <div class="w-full max-w-sm">

            <div class="mb-8">
                <a href="/" class="inline-flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500 text-[11px] font-black text-white">VF</div>
                    <span class="font-display text-base font-bold tracking-tight text-white">VenueFlow</span>
                </a>
                <h1 class="font-display mt-6 text-2xl font-bold tracking-tight text-white">Skapa konto</h1>
                <p class="mt-1 text-sm text-white/40">Kom igång med VenueFlow.</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-semibold text-white/40" style="letter-spacing:0.08em; text-transform:uppercase">Namn</label>
                    <input id="name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}"
                           class="mt-2 block w-full rounded-none border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white placeholder-white/20 transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-white/40" style="letter-spacing:0.08em; text-transform:uppercase">E-postadress</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                           class="mt-2 block w-full rounded-none border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white placeholder-white/20 transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-white/40" style="letter-spacing:0.08em; text-transform:uppercase">Lösenord</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                           class="mt-2 block w-full rounded-none border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white placeholder-white/20 transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-white/40" style="letter-spacing:0.08em; text-transform:uppercase">Bekräfta lösenord</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                           class="mt-2 block w-full rounded-none border border-white/10 bg-white/5 px-3.5 py-2.5 text-sm text-white placeholder-white/20 transition focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <button type="submit"
                        class="mt-2 flex w-full justify-center rounded-none bg-emerald-500 px-3 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600 active:scale-[0.98]">
                    Registrera
                </button>
            </form>

            <p class="mt-8 text-center text-sm text-white/30">
                Redan konto?
                <a href="{{ route('login') }}" class="font-semibold text-white/60 underline underline-offset-4 transition hover:text-white">Logga in</a>
            </p>

        </div>
    </div>
</x-public-layout>
