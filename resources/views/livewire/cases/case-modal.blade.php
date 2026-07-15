<div>
    <div x-data="{ open: @entangle('show') }" x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center" x-on:close-case-modal.window="open = false">

        <!-- Overlay -->
        <div class="fixed inset-0 bg-black/40" @click="open = false"></div>

        <!-- Modal -->
        <div class="cms-panel relative w-full max-w-3xl mx-4 z-50 overflow-y-auto max-h-[90vh]">

            <!-- HEADER -->
            <div class="cms-panel-head">
                <div>
                    <div class="cms-eyebrow">CASE</div>
                    <h2 class="cms-panel-title">
                        {{ $caseId ? 'Edit Case' : 'Create New Case' }}
                    </h2>
                </div>
                <button class="cms-btn cms-btn-ghost" @click="open = false">Close</button>
            </div>

            <div class="cms-panel-body" style="padding:20px">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">

                <!-- Category -->
                <div>
                    <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Category</label>
                    <select wire:model="category_id" class="cms-input w-full">
                        <option value="">Select category…</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name ?? $cat->slug }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Status</label>
                    <select wire:model="status_id" class="cms-input w-full">
                        <option value="">Select status…</option>
                        @foreach($statuses as $st)
                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </select>
                    @error('status_id') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror
                </div>

                <!-- Event Date -->
                <div>
                    <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Event Date</label>
                    <input type="date" wire:model="event_date" class="cms-input w-full">
                    @error('event_date') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror
                </div>

                <!-- Public -->
                <div class="flex items-center gap-2 md:mt-6">
                    <input type="checkbox" wire:model="is_public" class="rounded border-[color:var(--hairline-2)]">
                    <label class="text-sm text-[color:var(--ink)]">Public Case</label>
                </div>
            </div>

            <!-- Location -->
            {{-- <h3 class="text-lg font-semibold mt-6 mb-2">Location</h3>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-sm">Latitude</label>
                    <input wire:model="latitude" class="w-full border px-3 py-2" placeholder="-6.1234">
                </div>
                <div>
                    <label class="text-sm">Longitude</label>
                    <input wire:model="longitude" class="w-full border px-3 py-2" placeholder="106.9876">
                </div>
            </div> --}}
            <!-- ========================= -->
            <!-- MAP PICKER -->
            <!-- ========================= -->
            <div class="py-4">

                <!-- PROVINCE -->
                {{-- <label class="block font-medium mb-1">Provinsi</label>
                <select wire:model.live="province_id" class="w-full border rounded-xl p-2 bg-gray-50 mb-4">
                    <option value="">Pilih provinsi...</option>
                    @foreach($provinces as $p)
                    <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                    @endforeach
                </select> --}}

                <!-- CITY -->
                {{-- <label class="block font-medium mb-1">Kota / Kabupaten</label>
                <select wire:model.live="city_id" class="w-full border rounded-xl p-2 bg-gray-50 mb-4">
                    <option value="">Pilih kota...</option>
                    @foreach($cities as $c)
                    <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                    @endforeach
                </select> --}}

                <!-- DISTRICT -->
                {{-- <label class="block font-medium mb-1">Kecamatan</label>
                <select wire:model.live="district_id" class="w-full border rounded-xl p-2 bg-gray-50 mb-6">
                    <option value="">Pilih kecamatan...</option>
                    @foreach($districts as $d)
                    <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                    @endforeach
                </select> --}}



                <!-- Lat/Lng -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Latitude</label>
                        <input type="text" wire:model="lat" class="cms-input w-full font-mono-c">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Longitude</label>
                        <input type="text" wire:model="lng" class="cms-input w-full font-mono-c">
                    </div>
                </div>

                <!-- MAP -->
                <div x-data="mapComponent()" x-init="initMap()">

                    <div wire:ignore id="map" class="w-full h-100 rounded-[10px] border border-[color:var(--hairline)]"></div>
                    <div x-data="{ open: false }" class="relative w-full mt-2">

                        <input type="text" wire:model.live.debounce.150ms="searchLocation" @focus="open = true"
                            @keydown.escape="open = false" placeholder="Cari desa / kelurahan..."
                            class="cms-input w-full" />

                        {{-- DROPDOWN --}}
                        @if(count($results) > 0)
                        <div x-show="open" x-transition x-cloak @click.away="open = false" class="absolute left-0 right-0 z-[9999] mt-1
                               bg-white border border-[color:var(--hairline)]
                               rounded-[10px] shadow-lg max-h-60 overflow-auto">
                            @foreach($results as $item)
                            <div wire:click="select(
                                                '{{ $item['id'] }}',
                                                '{{ $item['text'] }}',
                                                '{{ $item['lat'] }}',
                                                '{{ $item['long'] }}'
                                            )" @click="open = false"
                                class="px-4 py-2 hover:bg-[color:var(--paper)] cursor-pointer text-sm text-[color:var(--ink)]">
                                {{ $item['text'] }}
                            </div>
                            @endforeach
                        </div>
                        @endif

                    </div>


                    <input type="hidden" x-model="lat" wire:model="lat">
                    <input type="hidden" x-model="lng" wire:model="lng">

                    <template x-if="lat && lng">
                        <p class="mt-2 text-sm text-[color:var(--muted)]">
                            Lokasi: <strong class="text-[color:var(--ink)] font-mono-c" x-text="lat + ', ' + lng"></strong>
                        </p>
                    </template>
                </div>
            </div>

            <!-- TRANSLATIONS -->
            <div class="cms-eyebrow mt-6 mb-2">CASE INFORMATION (ID)</div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Title (ID)</label>
                <input wire:model="title_id" class="cms-input w-full">
                @error('title_id') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Summary (ID)</label>
                <textarea wire:model="summary_id" class="cms-input w-full h-20"></textarea>
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Description (ID)</label>
                <textarea wire:model="desc_id" class="cms-input w-full h-28"></textarea>
            </div>

            <div class="cms-eyebrow mt-6 mb-2">CASE INFORMATION (EN)</div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Title (EN)</label>
                <input wire:model="title_en" class="cms-input w-full">
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Summary (EN)</label>
                <textarea wire:model="summary_en" class="cms-input w-full h-20"></textarea>
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-[color:var(--muted)] mb-1.5">Description (EN)</label>
                <textarea wire:model="desc_en" class="cms-input w-full h-28"></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-[color:var(--hairline)]">
                <button @click="open = false" class="cms-btn cms-btn-ghost">Cancel</button>

                <button wire:click="save" class="cms-btn cms-btn-leaf">
                    {{ $caseId ? 'Update Case' : 'Create Case' }}
                </button>
            </div>

            </div>

        </div>

    </div>
</div>
<script>
    function mapComponent() {
        return {
            map: null,
            marker: null,
            regionLayer: null,
            lat: null,
            lng: null,

            initMap() {
                this.map = L.map('map').setView([-2.5489, 118.0149], 5);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(this.map);

                // 🔥 WAJIB: listen DOM event dari Livewire
                window.addEventListener('location-updated', e => {
                    // this.setMarker(e.detail.lat, e.detail.lng);
                    this.setMarker(e.detail.lat, e.detail.lng);
                    this.map.setView([e.detail.lat, e.detail.lng], 14);
                    this.setPolygon(e.detail.geometry);
                });

                window.addEventListener('location-reset', () => {
                    if (this.marker) {
                        this.map.removeLayer(this.marker);
                        this.marker = null;
                    }

                    if (this.regionLayer) {
                        this.map.removeLayer(this.regionLayer);
                        this.regionLayer = null;
                    }

                    this.map.setView([-2.5489, 118.0149], 5);
                });

            },

            setMarker(lat, lng) {
                if (this.marker) this.marker.remove();
                this.marker = L.marker([lat, lng]).addTo(this.map);
            },

            setPolygon(geometry) {
                console.log('GEOMETRY:', geometry);
                if (!geometry) {
                    if (this.marker) {
                        this.map.setView(
                            [this.marker.getLatLng().lat, this.marker.getLatLng().lng],
                            14
                        );
                    }
                    return;
                }

                if (this.regionLayer) {
                    this.map.removeLayer(this.regionLayer);
                }

                const feature = {
                    type: "Feature",
                    properties: {},
                    geometry: geometry
                };

                this.regionLayer = L.geoJSON(feature, {
                    style: {
                        color: '#00323C',
                        weight: 2,
                        fillColor: '#00323C',
                        fillOpacity: 0.3
                    }
                }).addTo(this.map);

                this.map.fitBounds(this.regionLayer.getBounds());
            }

        }
    }
</script>