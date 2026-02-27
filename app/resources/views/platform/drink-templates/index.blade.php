<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-900">Plattform · Dryckeskatalog</h2>
    </x-slot>

    <section class="vf-container space-y-5 py-8">
        @if(session('undo_restore_url'))
            <div class="vf-card border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span>{{ session('undo_message', 'Bild borttagen.') }}</span>
                    <form method="POST" action="{{ session('undo_restore_url') }}">
                        @csrf
                        <button class="rounded-lg border border-amber-300 px-3 py-1.5 font-semibold hover:bg-amber-100">Ångra</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="vf-card p-6">
            <h3 class="text-lg font-semibold">Ny dryckmall</h3>
            <form method="POST" action="{{ route('platform.drink-templates.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-3 md:grid-cols-5">
                @csrf
                <input name="name" placeholder="Namn" class="vf-input" required>
                <input name="base_price" placeholder="Standardpris" class="vf-input" required>
                <input name="tags" placeholder="taggar, kommaseparerat" class="vf-input">
                <input name="description" placeholder="Beskrivning" class="vf-input md:col-span-2">
                <input type="hidden" name="crop_x" id="drink-crop-x">
                <input type="hidden" name="crop_y" id="drink-crop-y">
                <input type="hidden" name="crop_w" id="drink-crop-w">
                <input type="hidden" name="crop_h" id="drink-crop-h">

                <div id="drink-dropzone" class="md:col-span-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600">
                    <p class="font-medium text-slate-700">Dra och släpp en bild här eller klicka för att välja.</p>
                    <p class="text-xs text-slate-500">Förhandsvisning + centrerad crop (16:9) används vid uppladdning.</p>
                    <input type="file" id="drink-image-input" name="image" accept="image/*" class="mt-3 block w-full text-sm">
                    <img id="drink-image-preview" class="mt-3 hidden h-40 w-full rounded-lg object-cover" alt="Preview">
                </div>

                <label class="flex items-center gap-2 text-sm md:col-span-5">
                    <input type="checkbox" name="active" value="1" checked>
                    Aktiv i katalogen
                </label>
                <button class="vf-btn-primary md:col-span-5">Skapa dryckmall</button>
            </form>
        </div>

        <div class="vf-card p-4">
            <form method="GET" class="grid gap-3 md:grid-cols-4">
                <input name="q" value="{{ request('q') }}" placeholder="Sök namn eller beskrivning" class="vf-input">
                <select name="status" class="vf-input">
                    <option value="">Alla statusar</option>
                    <option value="active" @selected(request('status') === 'active')>Aktiva</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inaktiva</option>
                </select>
                <select name="sort" class="vf-input">
                    <option value="name_asc" @selected(request('sort', 'name_asc') === 'name_asc')>Namn A-Ö</option>
                    <option value="name_desc" @selected(request('sort') === 'name_desc')>Namn Ö-A</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>Pris stigande</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>Pris fallande</option>
                    <option value="latest" @selected(request('sort') === 'latest')>Senast skapad</option>
                </select>
                <button class="vf-btn-secondary">Filtrera</button>
            </form>
        </div>

        <div class="vf-card overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Bild</th>
                        <th class="px-4 py-3 text-left">Namn</th>
                        <th class="px-4 py-3 text-left">Pris</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Beskrivning</th>
                        <th class="px-4 py-3 text-right">Åtgärder</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($templates as $template)
                        <tr>
                            <td class="px-4 py-3">
                                <img src="{{ $template->image_url ?: asset('images/menu/fallback.svg') }}" alt="{{ $template->name }}" class="h-14 w-24 rounded-lg object-cover">
                            </td>
                            <td class="px-4 py-3 font-medium">{{ $template->name }}</td>
                            <td class="px-4 py-3">{{ number_format($template->base_price, 2, ',', ' ') }} kr</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $template->active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                                    {{ $template->active ? 'Aktiv' : 'Inaktiv' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $template->description }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('platform.drink-templates.update', $template) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" value="{{ $template->name }}" class="vf-input w-40" required>
                                        <input type="text" name="base_price" value="{{ $template->base_price }}" class="vf-input w-28" required>
                                        <input type="text" name="description" value="{{ $template->description }}" class="vf-input w-56">
                                        <input type="text" name="tags" value="{{ is_array($template->tags) ? implode(',', $template->tags) : '' }}" class="vf-input w-40">
                                        <input type="file" name="image" accept="image/*" class="vf-input w-56">
                                        @if($template->image_path)
                                            <label class="flex items-center gap-1 text-xs">
                                                <input type="checkbox" name="remove_image" value="1">
                                                Ta bort bild
                                            </label>
                                        @endif
                                        <label class="flex items-center gap-1 text-xs">
                                            <input type="checkbox" name="active" value="1" @checked($template->active)>
                                            Aktiv
                                        </label>
                                        <button class="vf-btn-secondary">Spara</button>
                                    </form>
                                    <form method="POST" action="{{ route('platform.drink-templates.destroy', $template) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">Ta bort</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">Inga dryckmallar ännu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $templates->links() }}
        </div>
    </section>

    <script>
        (function () {
            const input = document.getElementById('drink-image-input');
            const preview = document.getElementById('drink-image-preview');
            const dropzone = document.getElementById('drink-dropzone');
            const xInput = document.getElementById('drink-crop-x');
            const yInput = document.getElementById('drink-crop-y');
            const wInput = document.getElementById('drink-crop-w');
            const hInput = document.getElementById('drink-crop-h');

            const applyPreview = (file) => {
                const reader = new FileReader();
                reader.onload = (event) => {
                    preview.src = event.target.result;
                    preview.classList.remove('hidden');

                    const img = new Image();
                    img.onload = () => {
                        const targetRatio = 16 / 9;
                        let cropW = img.width;
                        let cropH = Math.round(cropW / targetRatio);
                        if (cropH > img.height) {
                            cropH = img.height;
                            cropW = Math.round(cropH * targetRatio);
                        }
                        xInput.value = Math.max(0, Math.floor((img.width - cropW) / 2));
                        yInput.value = Math.max(0, Math.floor((img.height - cropH) / 2));
                        wInput.value = cropW;
                        hInput.value = cropH;
                    };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
            };

            input.addEventListener('change', () => {
                if (input.files && input.files[0]) {
                    applyPreview(input.files[0]);
                }
            });

            ['dragenter', 'dragover'].forEach((eventName) => {
                dropzone.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    dropzone.classList.add('ring-2', 'ring-slate-400');
                });
            });

            ['dragleave', 'drop'].forEach((eventName) => {
                dropzone.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    dropzone.classList.remove('ring-2', 'ring-slate-400');
                });
            });

            dropzone.addEventListener('drop', (event) => {
                if (!event.dataTransfer || !event.dataTransfer.files || !event.dataTransfer.files[0]) {
                    return;
                }
                input.files = event.dataTransfer.files;
                applyPreview(event.dataTransfer.files[0]);
            });
        })();
    </script>
</x-app-layout>
