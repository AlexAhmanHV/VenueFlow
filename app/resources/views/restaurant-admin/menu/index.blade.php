<x-restaurant-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900">
            {{ $restaurant->name }} · Menyhantering
        </h2>
    </x-slot>

    <div class="space-y-8">
        @if(session('undo_restore_url'))
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span>{{ session('undo_message', 'Bild borttagen.') }}</span>
                    <form method="POST" action="{{ session('undo_restore_url') }}">
                        @csrf
                        <button class="rounded-lg border border-amber-300 px-3 py-1.5 font-semibold hover:bg-amber-100">Ångra</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
            <form method="GET" class="lg:col-span-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 md:grid-cols-6">
                    <input name="q" value="{{ request('q') }}" placeholder="Sök menyartikel" class="rounded-lg border-gray-300 text-sm">
                    <select name="type" class="rounded-lg border-gray-300 text-sm">
                        <option value="all" @selected(request('type', 'all') === 'all')>Alla typer</option>
                        <option value="food" @selected(request('type') === 'food')>Mat</option>
                        <option value="drink" @selected(request('type') === 'drink')>Dryck</option>
                    </select>
                    <select name="status" class="rounded-lg border-gray-300 text-sm">
                        <option value="">Alla statusar</option>
                        <option value="active" @selected(request('status') === 'active')>Aktiv</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inaktiv</option>
                    </select>
                    <select name="source" class="rounded-lg border-gray-300 text-sm">
                        <option value="">Alla källor</option>
                        <option value="template" @selected(request('source') === 'template')>Från mall</option>
                        <option value="manual" @selected(request('source') === 'manual')>Manuell</option>
                    </select>
                    <select name="sort" class="rounded-lg border-gray-300 text-sm">
                        <option value="custom" @selected(request('sort', 'custom') === 'custom')>Egen ordning</option>
                        <option value="name_asc" @selected(request('sort') === 'name_asc')>Namn A-Ö</option>
                        <option value="name_desc" @selected(request('sort') === 'name_desc')>Namn Ö-A</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Pris stigande</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Pris fallande</option>
                        <option value="recent" @selected(request('sort') === 'recent')>Senast uppdaterad</option>
                    </select>
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Filtrera</button>
                </div>
            </form>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm lg:col-span-2">
                <h3 class="text-base font-semibold text-gray-900">Lägg till mat från katalog</h3>
                <form method="POST" action="{{ route('restaurant.admin.menu.store-template', $restaurant->slug) }}" class="mt-3 space-y-3">
                    @csrf
                    <select id="dish_template_id" name="dish_template_id" class="w-full rounded-lg border-gray-300 text-sm" required>
                        <option value="">Välj rättmall</option>
                        @foreach($dishTemplates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }} ({{ number_format($template->base_price, 2, ',', ' ') }} kr)</option>
                        @endforeach
                    </select>
                    <input type="text" name="price" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Eget pris (valfritt)">
                    <label class="flex items-center gap-2 text-sm">
                        <input name="active" type="checkbox" value="1" checked>
                        Aktiv på menyn
                    </label>
                    <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Lägg till mat</button>
                </form>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm lg:col-span-2">
                <h3 class="text-base font-semibold text-gray-900">Lägg till dryck från katalog</h3>
                <form method="POST" action="{{ route('restaurant.admin.menu.store-drink-template', $restaurant->slug) }}" class="mt-3 space-y-3">
                    @csrf
                    <select id="drink_template_id" name="drink_template_id" class="w-full rounded-lg border-gray-300 text-sm" required>
                        <option value="">Välj dryckmall</option>
                        @foreach($drinkTemplates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }} ({{ number_format($template->base_price, 2, ',', ' ') }} kr)</option>
                        @endforeach
                    </select>
                    <input type="text" name="price" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Eget pris (valfritt)">
                    <label class="flex items-center gap-2 text-sm">
                        <input name="active" type="checkbox" value="1" checked>
                        Aktiv på menyn
                    </label>
                    <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Lägg till dryck</button>
                </form>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm lg:col-span-1">
                <h3 class="text-base font-semibold text-gray-900">Snabbstatistik</h3>
                <dl class="mt-3 space-y-2 text-sm text-gray-600">
                    <div class="flex justify-between"><dt>Totalt</dt><dd class="font-semibold text-gray-900">{{ $items->count() }}</dd></div>
                    <div class="flex justify-between"><dt>Mat</dt><dd class="font-semibold text-gray-900">{{ $foodItems->count() }}</dd></div>
                    <div class="flex justify-between"><dt>Dryck</dt><dd class="font-semibold text-gray-900">{{ $drinkItems->count() }}</dd></div>
                </dl>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900">Skapa manuell menyartikel</h3>
            <form method="POST" action="{{ route('restaurant.admin.menu.store', $restaurant->slug) }}" enctype="multipart/form-data" class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-6">
                @csrf
                <input type="text" name="name" class="rounded-lg border-gray-300 text-sm md:col-span-2" placeholder="Namn" required>
                <input type="number" name="price" step="0.01" class="rounded-lg border-gray-300 text-sm md:col-span-1" placeholder="Pris" required>
                <input type="text" name="tags" class="rounded-lg border-gray-300 text-sm md:col-span-2" placeholder="taggar, kommaseparerat">
                <label class="flex items-center gap-2 text-sm md:col-span-1">
                    <input type="checkbox" name="active" value="1" checked>
                    Aktiv
                </label>
                <textarea name="description" rows="2" class="rounded-lg border-gray-300 text-sm md:col-span-4" placeholder="Beskrivning"></textarea>
                <input type="hidden" name="crop_x" id="manual-crop-x">
                <input type="hidden" name="crop_y" id="manual-crop-y">
                <input type="hidden" name="crop_w" id="manual-crop-w">
                <input type="hidden" name="crop_h" id="manual-crop-h">
                <div id="manual-dropzone" class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-3 text-sm text-gray-600 md:col-span-2">
                    <p class="font-medium text-gray-700">Dra bild hit eller välj fil.</p>
                    <input type="file" id="manual-image-input" name="image" accept="image/*" class="mt-2 block w-full text-sm">
                    <img id="manual-image-preview" class="mt-2 hidden h-28 w-full rounded-lg object-cover" alt="Preview">
                </div>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 md:col-span-6">Skapa artikel</button>
            </form>
        </div>

        <form method="POST" action="{{ route('restaurant.admin.menu.bulk', $restaurant->slug) }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" id="bulk-form">
            @csrf
            <div class="grid gap-3 md:grid-cols-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Bulk action</label>
                    <select name="action" id="bulk-action" class="mt-1 w-full rounded-lg border-gray-300 text-sm" required>
                        <option value="set_active">Aktivera valda</option>
                        <option value="set_inactive">Inaktivera valda</option>
                        <option value="remove_image">Ta bort bild</option>
                        <option value="add_tag">Lägg till tagg</option>
                        <option value="remove_tag">Ta bort tagg</option>
                        <option value="increase_price_pct">Öka pris (%)</option>
                        <option value="decrease_price_pct">Minska pris (%)</option>
                        <option value="delete">Ta bort valda</option>
                    </select>
                </div>
                <div class="md:col-span-2" id="bulk-tag-wrap" style="display:none;">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Tagg</label>
                    <input name="tag" class="mt-1 w-full rounded-lg border-gray-300 text-sm" placeholder="ex: vego">
                </div>
                <div class="md:col-span-1" id="bulk-pct-wrap" style="display:none;">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Procent</label>
                    <input name="percentage" type="number" step="0.01" min="1" max="90" class="mt-1 w-full rounded-lg border-gray-300 text-sm" value="5">
                </div>
                <div class="flex items-end gap-2 md:col-span-1">
                    <button type="button" id="check-all" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Markera alla</button>
                    <button class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Kör</button>
                </div>
            </div>
        </form>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3" id="sortable-grid">
            @foreach($items as $item)
                @php $isDrink = $item->drink_template_id !== null; @endphp
                <article class="group rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md" draggable="true" data-item-id="{{ $item->id }}">
                    <div class="relative">
                        <img src="{{ $item->image_url ?: asset('images/menu/fallback.svg') }}" alt="{{ $item->name }}" class="h-44 w-full rounded-t-2xl object-cover">
                        <div class="absolute left-3 top-3 rounded-full bg-white/90 px-2 py-1 text-xs font-semibold text-gray-800">
                            {{ $isDrink ? 'Dryck' : 'Mat' }}
                        </div>
                        <label class="absolute right-3 top-3 rounded-full bg-white/90 px-2 py-1 text-xs font-semibold text-gray-700">
                            <input type="checkbox" class="bulk-checkbox mr-1" name="item_ids[]" value="{{ $item->id }}" form="bulk-form">
                            Välj
                        </label>
                    </div>
                    <div class="space-y-3 p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $item->name }}</h4>
                                <p class="text-xs text-gray-500">{{ ($item->dish_template_id || $item->drink_template_id) ? 'Från mall' : 'Manuell' }}</p>
                            </div>
                            <p class="text-sm font-bold text-indigo-700">{{ number_format($item->price, 2, ',', ' ') }} kr</p>
                        </div>
                        @if($item->description)
                            <p class="text-sm text-gray-600">{{ $item->description }}</p>
                        @endif

                        <form method="POST" action="{{ route('restaurant.admin.menu.update', [$restaurant->slug, $item]) }}" enctype="multipart/form-data" class="space-y-2 border-t border-gray-100 pt-3">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $item->name }}" class="w-full rounded-lg border-gray-300 text-sm" required>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" name="price" value="{{ $item->price }}" step="0.01" class="rounded-lg border-gray-300 text-sm" required>
                                <input type="text" name="tags" value="{{ is_array($item->tags) ? implode(',', $item->tags) : '' }}" class="rounded-lg border-gray-300 text-sm" placeholder="taggar">
                            </div>
                            <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 text-sm">{{ $item->description }}</textarea>
                            <input type="file" name="image" accept="image/*" class="w-full rounded-lg border-gray-300 text-sm">
                            <div class="flex flex-wrap items-center gap-3 text-xs">
                                @if($item->image_path)
                                    <label class="flex items-center gap-1 text-gray-600">
                                        <input type="checkbox" name="remove_image" value="1">
                                        Ta bort bild
                                    </label>
                                @endif
                                <label class="flex items-center gap-1 text-gray-600">
                                    <input type="checkbox" name="active" value="1" @checked($item->active)>
                                    Aktiv
                                </label>
                            </div>
                            <button type="submit" class="w-full rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Spara ändringar</button>
                        </form>

                        <form method="POST" action="{{ route('restaurant.admin.menu.destroy', [$restaurant->slug, $item]) }}" onsubmit="return confirm('Ta bort menyartikeln?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full rounded-lg border border-rose-200 px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50">Ta bort</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <h3 class="text-base font-semibold text-gray-900">Senaste menyändringar</h3>
            <div class="mt-3 space-y-2 text-sm text-gray-600">
                @forelse($recentAudits as $audit)
                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-gray-100 p-3">
                        <div>
                            <p class="font-medium text-gray-900">{{ $audit->action }} @if($audit->menuItem)· {{ $audit->menuItem->name }}@endif</p>
                            <p class="text-xs text-gray-500">{{ optional($audit->actor)->email ?? 'system' }}</p>
                        </div>
                        <span class="text-xs text-gray-500">{{ $audit->created_at?->format('Y-m-d H:i') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Ingen historik ännu.</p>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        (() => {
            const bulkAction = document.getElementById('bulk-action');
            const tagWrap = document.getElementById('bulk-tag-wrap');
            const pctWrap = document.getElementById('bulk-pct-wrap');
            const checkAllBtn = document.getElementById('check-all');
            const checkboxes = () => document.querySelectorAll('.bulk-checkbox');

            const refreshBulkInputs = () => {
                const value = bulkAction.value;
                tagWrap.style.display = (value === 'add_tag' || value === 'remove_tag') ? 'block' : 'none';
                pctWrap.style.display = (value === 'increase_price_pct' || value === 'decrease_price_pct') ? 'block' : 'none';
            };

            bulkAction.addEventListener('change', refreshBulkInputs);
            refreshBulkInputs();

            checkAllBtn.addEventListener('click', () => {
                const all = [...checkboxes()];
                const shouldCheck = all.some((input) => !input.checked);
                all.forEach((input) => input.checked = shouldCheck);
            });

            const imageInput = document.getElementById('manual-image-input');
            const imagePreview = document.getElementById('manual-image-preview');
            const dropzone = document.getElementById('manual-dropzone');
            const cropX = document.getElementById('manual-crop-x');
            const cropY = document.getElementById('manual-crop-y');
            const cropW = document.getElementById('manual-crop-w');
            const cropH = document.getElementById('manual-crop-h');

            const assignFile = (file) => {
                const reader = new FileReader();
                reader.onload = (event) => {
                    imagePreview.src = event.target.result;
                    imagePreview.classList.remove('hidden');

                    const img = new Image();
                    img.onload = () => {
                        const targetRatio = 16 / 9;
                        let width = img.width;
                        let height = Math.round(width / targetRatio);
                        if (height > img.height) {
                            height = img.height;
                            width = Math.round(height * targetRatio);
                        }
                        cropX.value = Math.max(0, Math.floor((img.width - width) / 2));
                        cropY.value = Math.max(0, Math.floor((img.height - height) / 2));
                        cropW.value = width;
                        cropH.value = height;
                    };
                    img.src = event.target.result;
                };
                reader.readAsDataURL(file);
            };

            imageInput.addEventListener('change', () => {
                if (imageInput.files && imageInput.files[0]) {
                    assignFile(imageInput.files[0]);
                }
            });

            ['dragenter', 'dragover'].forEach((eventName) => {
                dropzone.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    dropzone.classList.add('ring-2', 'ring-indigo-300');
                });
            });

            ['dragleave', 'drop'].forEach((eventName) => {
                dropzone.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    dropzone.classList.remove('ring-2', 'ring-indigo-300');
                });
            });

            dropzone.addEventListener('drop', (event) => {
                if (!event.dataTransfer?.files?.[0]) {
                    return;
                }
                imageInput.files = event.dataTransfer.files;
                assignFile(event.dataTransfer.files[0]);
            });

            const grid = document.getElementById('sortable-grid');
            if (!grid) {
                return;
            }

            let dragged = null;
            grid.querySelectorAll('[data-item-id]').forEach((card) => {
                card.addEventListener('dragstart', () => {
                    dragged = card;
                    card.classList.add('opacity-60');
                });

                card.addEventListener('dragend', () => {
                    card.classList.remove('opacity-60');
                });

                card.addEventListener('dragover', (event) => {
                    event.preventDefault();
                });

                card.addEventListener('drop', async (event) => {
                    event.preventDefault();
                    if (!dragged || dragged === card) {
                        return;
                    }

                    const cards = [...grid.querySelectorAll('[data-item-id]')];
                    const draggedIndex = cards.indexOf(dragged);
                    const targetIndex = cards.indexOf(card);
                    if (draggedIndex < targetIndex) {
                        card.after(dragged);
                    } else {
                        card.before(dragged);
                    }

                    const orderedIds = [...grid.querySelectorAll('[data-item-id]')].map((el) => Number(el.dataset.itemId));
                    await fetch('{{ route('restaurant.admin.menu.reorder', $restaurant->slug) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ ordered_ids: orderedIds }),
                    });
                });
            });
        })();
    </script>
</x-restaurant-admin-layout>
