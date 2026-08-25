<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">Golvplan · {{ $restaurant->name }}</h2>
    </x-slot>

    <section class="vf-container space-y-6 py-8">
        <x-restaurant-admin-nav :restaurant="$restaurant" />

        @if(session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="vf-card p-5">
            <p class="text-sm text-slate-600">Dra varje resurs till sin plats i lokalen. Klicka "Spara golvplan" när du är klar.</p>
        </div>

        <div
            x-data="floorPlanEditor(@js($resources->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'type' => $r->type->value,
                'x' => $r->position_x,
                'y' => $r->position_y,
            ])), '{{ route('restaurant.admin.floor-plan.update', $restaurant->slug) }}')"
            x-init="init()"
            class="vf-card p-5"
        >
            <div
                x-ref="canvas"
                class="relative h-[420px] w-full rounded-xl border border-slate-200 bg-slate-50"
            >
                <template x-for="resource in placed" :key="resource.id">
                    <div
                        class="absolute flex h-16 w-16 -translate-x-1/2 -translate-y-1/2 cursor-move select-none items-center justify-center rounded-lg border border-slate-300 bg-white text-center text-xs font-medium shadow-sm"
                        :style="`left: ${resource.x}%; top: ${resource.y}%;`"
                        @pointerdown="startDrag($event, resource)"
                        x-text="resource.name"
                    ></div>
                </template>
            </div>

            <template x-if="unplaced.length > 0">
                <div class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Oplacerade resurser — dra in dem ovan</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <template x-for="resource in unplaced" :key="resource.id">
                            <div
                                class="flex h-16 w-16 cursor-move select-none items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white text-center text-xs font-medium"
                                @pointerdown="startDrag($event, resource, true)"
                                x-text="resource.name"
                            ></div>
                        </template>
                    </div>
                </div>
            </template>

            <div class="mt-5 flex items-center gap-3">
                <button type="button" class="vf-btn-primary" @click="save()" x-text="saving ? 'Sparar…' : 'Spara golvplan'"></button>
                <span x-show="saved" x-cloak class="text-sm text-emerald-700">Sparat!</span>
                <span x-show="error" x-cloak class="text-sm text-rose-700" x-text="error"></span>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        function floorPlanEditor(resources, saveUrl) {
            return {
                resources,
                saveUrl,
                saving: false,
                saved: false,
                error: null,
                dragging: null,
                get placed() {
                    return this.resources.filter(r => r.x !== null && r.y !== null);
                },
                get unplaced() {
                    return this.resources.filter(r => r.x === null || r.y === null);
                },
                init() {},
                startDrag(event, resource, isUnplaced = false) {
                    if (isUnplaced) {
                        resource.x = 50;
                        resource.y = 50;
                    }
                    this.dragging = resource;

                    const move = (moveEvent) => this.onDrag(moveEvent);
                    const up = () => {
                        this.dragging = null;
                        window.removeEventListener('pointermove', move);
                        window.removeEventListener('pointerup', up);
                    };

                    window.addEventListener('pointermove', move);
                    window.addEventListener('pointerup', up);
                },
                onDrag(event) {
                    if (!this.dragging) return;
                    const canvas = this.$refs.canvas.getBoundingClientRect();
                    const x = ((event.clientX - canvas.left) / canvas.width) * 100;
                    const y = ((event.clientY - canvas.top) / canvas.height) * 100;
                    this.dragging.x = Math.min(100, Math.max(0, x));
                    this.dragging.y = Math.min(100, Math.max(0, y));
                },
                async save() {
                    this.saving = true;
                    this.saved = false;
                    this.error = null;

                    const positions = this.placed.map(r => ({ resource_id: r.id, x: r.x, y: r.y }));
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    try {
                        const response = await fetch(this.saveUrl, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ positions }),
                        });

                        if (!response.ok) {
                            const data = await response.json().catch(() => ({}));
                            this.error = data?.message || 'Kunde inte spara golvplanen.';
                            return;
                        }

                        this.saved = true;
                    } catch (_) {
                        this.error = 'Nätverksfel vid sparning. Försök igen.';
                    } finally {
                        this.saving = false;
                    }
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
