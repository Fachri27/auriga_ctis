<div>
    <div class="max-w-7xl mx-auto py-12 mt-20 poppins-regular" x-data="{ locale: @entangle('locale') }">

        <h1 class="text-3xl font-semibold mb-6">
            <span x-show="locale === 'id'">Buat Laporan</span>
            <span x-show="locale === 'en'">Submit Report</span>
        </h1>

        @if(session('success'))
            <div class="p-4 mb-5 text-green-700 bg-green-100 rounded">
                {{ session('success') }}
            </div>
        @endif

        {{-- TWO COLUMNS RESPONSIVE --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 p-6 rounded-xl mb-6">
                <h2 class="text-xl font-semibold mb-4">Identitas Pelapor</h2>
                <!-- ========================= -->
                <!-- IDENTITAS LENGKAP -->
                <!-- ========================= -->
                <div class="bg-gray-50 p-6 rounded-xl mb-6">
                    <div class="grid grid-cols-2 gap-4">

                        <div>
                            <label>Nama Lengkap</label>
                            <input type="text" wire:model="nama_lengkap" class="w-full border rounded p-2 mt-1">
                        </div>

                        <div>
                            <label>NIK</label>
                            <input type="text" wire:model="nik" class="w-full border rounded p-2 mt-1">
                        </div>

                        <div>
                            <label>Jenis Kelamin</label>
                            <select wire:model="jenis_kelamin" class="w-full border rounded p-2 mt-1">
                                <option value="">Pilih...</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>

                        <div>
                            <label>Tanggal Lahir</label>
                            <input type="date" wire:model="tanggal_lahir" class="w-full border rounded p-2 mt-1">
                        </div>

                        <div class="col-span-2">
                            <label>Alamat</label>
                            <input type="text" wire:model="alamat" class="w-full border rounded p-2 mt-1">
                        </div>

                        <div>
                            <label>No HP</label>
                            <input type="text" wire:model="no_hp" class="w-full border rounded p-2 mt-1">
                        </div>

                        <div>
                            <label>Email</label>
                            <input type="email" wire:model="email" class="w-full border rounded p-2 mt-1">
                        </div>

                        <div>
                            <label>Pekerjaan</label>
                            <input type="text" wire:model="pekerjaan" class="w-full border rounded p-2 mt-1">
                        </div>

                        <div>
                            <label>Status Perkawinan</label>
                            <input type="text" wire:model="status_perkawinan" class="w-full border rounded p-2 mt-1">
                        </div>

                    </div>

                </div>
            </div>
            <div class="bg-gray-50 p-6 rounded-xl mb-6">
                <h2 class="text-xl font-semibold mb-4">Deskripsi Laporan</h2>
                <!-- Category -->
                <div class="mb-6">
                    <label class="text-sm">Category</label>
                    <select wire:model="category_id" class="w-full border px-3 py-2">
                        <option value="">Select category…</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name ?? $cat->slug }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>


                <!-- ========================= -->
                <!-- EVIDENCE -->
                <!-- ========================= -->
                <div class="bg-white p-6 rounded-xl mb-6">
                    <h2 class="text-xl font-semibold mb-4">Bukti Foto / Video</h2>

                    <input type="file" wire:model="evidence_files" multiple>

                    @if ($evidence_files)
                        <div class="grid grid-cols-4 gap-3 mt-3">
                            @foreach ($evidence_files as $file)
                                <div class="p-2 rounded bg-white border text-xs text-center">
                                    {{ $file->getClientOriginalName() }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
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
                    {{ $locale === 'id' ? 'Lokasi Kejadian' : 'Location' }}
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
            <div x-data="mapComponent()" x-init="initMap()">

                <label class="block font-medium mb-1">
                    {{ $locale === 'id' ? 'Lokasi Kejadian' : 'Location' }}
                </label>

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

        <!-- ========================= -->
        <!-- DESKRIPSI -->
        <!-- ========================= -->
        <div class="bg-gray-50 p-6 rounded-xl mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <span x-show="locale === 'id'">Deskripsi Laporan</span>
                <span x-show="locale === 'en'">Report Description</span>
            </h2>

            {{-- <textarea wire:model="description" class="w-full border rounded p-3 h-32"></textarea> --}}
            @include('front.components.tinymce-id')

            @error('description')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        <button wire:click="save" class="px-6 py-3 bg-black text-white rounded-xl">
            Kirim Laporan
        </button>

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

        }
    }
</script>