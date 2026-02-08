<div>
    <div class="max-w-7xl mx-auto py-12 poppins-regular" x-data="{ lang: 'id' }">

        <h1 class="text-3xl font-semibold mb-6">
            <span x-show="lang === 'id'">Buat Kasus</span>
            <span x-show="lang === 'en'">Submit Cases</span>
        </h1>

        @if(session('success'))
        <div class="p-4 mb-5 text-green-700 bg-green-100 rounded">
            {{ session('success') }}
        </div>
        @endif

        {{-- Language --}}
        <div class="flex flex-col mb-10">
            <label class="font-medium mb-2">🌐 Bahasa</label>
            <div class="flex rounded-lg border bg-gray-100 p-1 w-80">
                <!-- Indonesia -->
                <button :class="lang === 'id' ? 'bg-blue-600 text-white' : 'text-gray-700'" @click="lang = 'id'"
                    class="flex-1 text-center py-2 rounded-lg transition-colors">
                    Indonesia
                </button>
                <!-- English -->
                <button :class="lang === 'en' ? 'bg-blue-600 text-white' : 'text-gray-700'" @click="lang = 'en'"
                    class="flex-1 text-center py-2 rounded-lg transition-colors">
                    English
                </button>
            </div>

            <!-- Optional: hidden input untuk form -->
            <input type="hidden" name="lang" :value="lang">
        </div>

        {{-- TWO COLUMNS RESPONSIVE --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
            <!-- Category -->
            <div>
                <label class="text-sm">Category</label>
                <select wire:model="category_id" class="w-full border p-2 mt-1 bg-gray-50">
                    <option value="">Select category…</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name ?? $cat->slug }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <!-- Status -->
            <div>
                <label class="text-sm">Status</label>
                <select wire:model="status_id" class="w-full border  p-2 mt-1 bg-gray-50">
                    <option value="">Select status…</option>
                    @foreach($statuses as $st)
                    <option value="{{ $st->id }}">{{ $st->name }}</option>
                    @endforeach
                </select>
                @error('status_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            
        </div>
        <!-- Event Date -->
        <div>
            <label class="text-sm">Event Date</label>
            <input type="date" wire:model="event_date" class="w-full border  p-2 mt-1 bg-gray-50">
            @error('event_date') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <!-- ========================= -->
        <!-- MAP PICKER -->
        <!-- ========================= -->
        <div class="max-w-7xl mx-auto py-10">

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
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="font-medium">Latitude</label>
                    <input type="text" wire:model="lat" class="w-full border  p-2 mt-1 bg-gray-50">
                </div>
                <div>
                    <label class="font-medium">Longitude</label>
                    <input type="text" wire:model="lng" class="w-full border  p-2 mt-1 bg-gray-50">
                </div>
            </div>

            <!-- MAP -->
            {{-- <div x-data="mapComponent()" x-init="initMap()">
                <div id="map" class="w-full h-72 rounded-lg border"></div>
            </div> --}}
            {{-- <div x-data="mapComponent()" x-init="initMap()">

                <label class="block font-medium mb-1">
                    {{ $lang === 'id' ? 'Lokasi Kejadian' : 'Location' }}
                </label>

                <div wire:ignore id="map" class="w-full h-64 rounded border"></div>
                <livewire:geo-server wire:key="geo-server-map-report" />

                <input type="hidden" x-model="lat" wire:model="lat">
                <input type="hidden" x-model="lng" wire:model="lng">

                <template x-if="lat && lng">
                    <p class="mt-2 text-sm text-gray-600">
                        Lokasi: <strong x-text="lat + ', ' + lng"></strong>
                    </p>
                </template>
            </div> --}}
            <div x-data="mapComponent(@entangle('lat').live, @entangle('lng').live)" x-init="initMap()">

                {{-- <div class="text-sm text-red-600">
                    DEBUG: lat={{ $lat }} | lng={{ $lng }}
                </div> --}}


                <div wire:ignore id="map" class="w-full h-100 rounded border"></div>
                <div x-data="{ open: false }" class="relative w-full">

                    <input type="text" wire:model.live.debounce.150ms="searchLocation" @focus="open = true"
                        @keydown.escape="open = false" placeholder="Cari desa / kelurahan..."
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-black focus:border-black" />

                    {{-- DROPDOWN --}}
                    @if(count($results) > 0)
                    <div x-show="open" x-transition x-cloak @click.away="open = false" class="absolute left-0 right-0 z-[9999] mt-1
                               bg-white border border-gray-300
                               rounded-lg shadow-lg max-h-60 overflow-auto">
                        @foreach($results as $item)
                        <div wire:click="select(
                                                '{{ $item['id'] }}',
                                                '{{ $item['text'] }}',
                                                '{{ $item['lat'] }}',
                                                '{{ $item['long'] }}'
                                            )" @click="open = false"
                            class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm">
                            {{ $item['text'] }}
                        </div>
                        @endforeach
                    </div>
                    @endif

                </div>


                <input type="hidden" x-model="lat" wire:model="lat">
                <input type="hidden" x-model="lng" wire:model="lng">

                <template x-if="lat && lng">
                    <p class="mt-2 text-sm text-gray-600">
                        Lokasi: <strong x-text="lat + ', ' + lng"></strong>
                    </p>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-10">
            <div>
                <label for="" class="font-medium">Korban</label>
                <input type="text" wire:model="korban" class="w-full border  p-2 mt-1 bg-gray-50">
            </div>

            <div>
                <label for="" class="font-medium">Pekerjaan</label>
                <input type="text" wire:model="pekerjaan" class="w-full border  p-2 mt-1 bg-gray-50">
            </div>

        </div>

        <div class="mb-10">
            <label>Jenis Kelamin</label>
            <select wire:model="jenis_kelamin" class="w-full border rounded p-2 mt-1">
                <option value="">Pilih...</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
                <option value="A">Campuran</option>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-10">
            <div>
                <label for="" class="font-medium">Jumlah Korban</label>
                <input type="number" wire:model="jumlah_korban" class="w-full border  p-2 mt-1 bg-gray-50">
            </div>

            <div>
                <label for="" class="font-medium">Konflik Dengan</label>
                <input type="text" wire:model="konflik" class="w-full border  p-2 mt-1 bg-gray-50">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div x-show="lang === 'id'">
                <label class="font-medium">Judul</label>
                <input type="text" wire:model="title_id" class="w-full border  p-2 mt-1 bg-gray-50">
            </div>
            <div x-show="lang === 'en'">
                <label class="font-medium">Title</label>
                <input type="text" wire:model="title_en" class="w-full border  p-2 mt-1 bg-gray-50">
            </div>
            <div x-show="lang === 'id'">
                <label class="font-medium">Summary (ID)</label>
                <input type="text" wire:model="summary_id" class="w-full border  p-2 mt-1 bg-gray-50">
            </div>
            <div x-show="lang === 'en'">
                <label class="font-medium">Summary (EN)</label>
                <input type="text" wire:model="summary_en" class="w-full border  p-2 mt-1 bg-gray-50">
            </div>
        </div>

        <!-- ========================= -->
        <!-- DESKRIPSI -->
        <!-- ========================= -->
        <div class="bg-gray-50 p-6 rounded-xl mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <span x-show="lang === 'id'">Deskripsi Kasus</span>
                <span x-show="lang === 'en'">Cases Description</span>
            </h2>

            {{-- <textarea wire:model="description" class="w-full border rounded p-3 h-32"></textarea> --}}
            <div class="bg-white border rounded-xl p-4">
                <h3 class="font-semibold mb-3">Content</h3>

                <div x-show="lang === 'id'">
                    {{-- editor_id --}}
                    @includeWhen(true, 'front.components.tinymce-content-id')
                </div>

                <div x-show="lang === 'en'">
                    {{-- editor_en --}}
                    @includeWhen(true, 'front.components.tinymce-content-en')
                </div>
            </div>

            @error('description')
            <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div class="bg-white p-6 rounded-xl mb-6">
            <h2 class="text-xl font-semibold mb-4">Bukti Foto / Video</h2>

            <input type="file" wire:model="bukti" multiple>

            @if ($bukti)
            <div class="grid grid-cols-4 gap-3 mt-3">
                @foreach ($bukti as $file)
                <div class="p-2 rounded bg-white border text-xs text-center">
                    {{ basename($file) }}
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <button wire:click="save" class="px-6 py-3 bg-black text-white rounded-xl">
            Kirim Laporan
        </button>

    </div>


</div>
<script>
    function mapComponent(lat, lng) {
    return {
        map: null,
        marker: null,
        lat,
        lng,

        initMap() {
            console.log('MAP INIT', this.lat, this.lng);

            this.map = L.map('map').setView(
                this.lat && this.lng
                    ? [this.lat, this.lng]
                    : [-2.5489, 118.0149],
                this.lat && this.lng ? 14 : 5
            );

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(this.map);

            if (this.lat && this.lng) {
                this.marker = L.marker([this.lat, this.lng]).addTo(this.map);
            }

            // 🔥 ini kuncinya
            this.$watch('lat', () => this.updateMarker());
            this.$watch('lng', () => this.updateMarker());
        },

        updateMarker() {
            if (!this.lat || !this.lng || !this.map) return;

            if (this.marker) {
                this.marker.setLatLng([this.lat, this.lng]);
            } else {
                this.marker = L.marker([this.lat, this.lng]).addTo(this.map);
            }

            this.map.setView([this.lat, this.lng], 14);
        }
    }
}
</script>