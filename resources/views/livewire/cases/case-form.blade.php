@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
<style>.ksect + .ksect { margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--hairline); }</style>
@endpush

<div class="max-w-5xl mx-auto px-6 py-6 space-y-4" x-data="{ lang: 'id' }">

    {{-- ================= HEADER + LANGUAGE TOGGLE (inline) ================= --}}
    <div class="cms-panel-head border-b border-[color:var(--hairline)] pb-3 flex items-center justify-between gap-4">
        <div>
            <div class="cms-eyebrow">CASES</div>
            <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">
                <span x-show="lang === 'id'">Buat Kasus</span>
                <span x-show="lang === 'en'">Submit Cases</span>
            </h1>
        </div>
        <div class="flex items-center gap-2">
            <x-internal.translate-button />
            <div class="flex rounded-lg border border-[color:var(--hairline)] bg-[color:var(--paper-2)] p-1">
                <button :class="lang === 'id' ? 'cms-btn-primary' : 'cms-btn-ghost'" @click="lang = 'id'"
                    class="px-3 py-1.5 text-xs rounded-md transition-colors">Indonesia</button>
                <button :class="lang === 'en' ? 'cms-btn-primary' : 'cms-btn-ghost'" @click="lang = 'en'"
                    class="px-3 py-1.5 text-xs rounded-md transition-colors">English</button>
            </div>
        </div>
        <input type="hidden" name="lang" :value="lang">
    </div>

    {{-- ================= ALERTS ================= --}}
    @if (session('success'))
        <div class="cms-pill cms-pill-ok"><span class="dot"></span>{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="cms-pill cms-pill-danger"><span class="dot"></span>{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="cms-panel" style="border-color:var(--danger-soft)">
            <div class="cms-panel-body" style="padding:12px 16px">
                <div class="flex items-center gap-2 font-semibold mb-1 text-sm" style="color:var(--danger)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Form belum lengkap
                </div>
                <ul class="list-disc list-inside text-sm" style="color:var(--danger)">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- ================= DETAIL KASUS (Category / Status / Title / Event Date) ================= --}}
    <div class="cms-panel" style="overflow:visible">
        <div class="cms-panel-body" style="padding:16px 20px">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Category (TomSelect) -->
                <div x-data="{
                    ts: null,
                    init() {
                        this.ts = new TomSelect(this.$refs.select, {
                            plugins: ['remove_button'],
                            closeAfterSelect: false,
                            hideSelected: true,
                            onChange: (values) => {
                                const parsed = values.map(v => Number(v))
                                //PAKSA SET KE LIVEWIRE
                                @this.set('category_ids', parsed)
                            }
                        })
                        //SET VALUE SAAT COMPONENT LOAD
                        this.$nextTick(() => {
                            if (@this.get('category_ids')?.length) {
                                this.ts.setValue(@this.get('category_ids').map(v => String(v)))
                            }
                        })
                    }
                }" wire:ignore>
                    <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5 block">Category</label>
                    <select x-ref="select" multiple class="cms-input w-full">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name ?? $cat->slug }}</option>
                        @endforeach
                    </select>
                    @error('category_ids')
                        <p class="text-sm mt-1" style="color:var(--danger)">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5 block">Status</label>
                    <select wire:model="status_id" class="cms-input w-full">
                        <option value="">Select status…</option>
                        @foreach ($statuses as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </select>
                    @error('status_id')
                        <p class="text-sm mt-1" style="color:var(--danger)">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title ID -->
                <div class="md:col-span-2" x-show="lang === 'id'" x-data="{
                    title: @js(old('title_id', $title_id ?? '')),
                    slug: @js(old('slugId', $slugId ?? '')),
                    makeSlug(text) {
                        return text.toLowerCase()
                            .replace(/[^a-z0-9]+/g, '-')
                            .replace(/^-+|-+$/g, '');
                    }
                }" x-init="if (title && !slug) { slug = makeSlug(title) }">
                    <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5 block">Judul Kasus (ID)</label>
                    <input type="text" wire:model="title_id" x-model="title" @input="slug = makeSlug(title)" class="cms-input w-full">
                </div>

                <!-- Title EN -->
                <div class="md:col-span-2" x-show="lang === 'en'" x-data="{
                    slug: @js(old('slugEn', $slugEn ?? '')),
                    makeSlug(text) {
                        return text.toLowerCase()
                            .replace(/[^a-z0-9]+/g, '-')
                            .replace(/^-+|-+$/g, '');
                    }
                }" x-init="if (title && !slug) { slug = makeSlug(title) }">
                    <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5 block">Title Case (EN)</label>
                    <input type="text" wire:model="title_en" class="cms-input w-full">
                </div>

                <!-- Kualifikasi -->
                <div>
                    <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5 block">Kualifikasi Kasus</label>
                    {{-- live: panel Keterangan Tahapan mengikuti kualifikasi yang dipilih --}}
                    <select wire:model.live="kualifikasi" class="cms-input w-full">
                        <option value="">Pilih kualifikasi…</option>
                        @foreach (\App\Models\CaseModel::KUALIFIKASI as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('kualifikasi')
                        <p class="text-sm mt-1" style="color:var(--danger)">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Event Date -->
                <div>
                    <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5 block">Event Date</label>
                    <input type="date" wire:model="event_date" class="cms-input w-full">
                    @error('event_date')
                        <p class="text-sm mt-1" style="color:var(--danger)">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ================= LOCATION (map + pelapor/terlapor) ================= --}}
    <div class="cms-panel" style="overflow:visible">
        <div class="cms-panel-head" style="padding:14px 20px">
            <div>
                <div class="cms-eyebrow">LOCATION</div>
                <div class="cms-panel-title">Lokasi & Pelapor</div>
            </div>
        </div>
        <div class="cms-panel-body" style="padding:16px 20px">
            <!-- Pelapor / Terlapor -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5 block">Identitas Pelapor</label>
                    <input type="text" wire:model="pelapor" class="cms-input w-full">
                </div>
                <div>
                    <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5 block">Identitas Terlapor</label>
                    <input type="text" wire:model="terlapor" class="cms-input w-full">
                </div>
            </div>

            <!-- Map + search -->
            <div x-data="mapComponent(@entangle('lat').live, @entangle('lng').live)" x-init="initMap()">
                <div wire:ignore id="map" class="w-full h-100 rounded border border-[color:var(--hairline)]"></div>
                <div x-data="{ open: false }" class="relative w-full mt-3">
                    <input type="text" wire:model.live.debounce.150ms="searchLocation" @focus="open = true"
                        @keydown.escape="open = false" placeholder="Cari desa / kelurahan..." class="cms-input w-full" />
                    @if (count($results) > 0)
                        <div x-show="open" x-transition x-cloak @click.away="open = false"
                            class="absolute left-0 right-0 z-[9999] mt-1 bg-white border border-[color:var(--hairline)] rounded-lg shadow-lg max-h-60 overflow-auto">
                            @foreach ($results as $item)
                                <div wire:click="select('{{ $item['id'] }}','{{ $item['text'] }}','{{ $item['lat'] }}','{{ $item['long'] }}')"
                                    @click="open = false" class="px-4 py-2 hover:bg-[color:var(--paper-2)] cursor-pointer text-sm">
                                    {{ $item['text'] }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <input type="hidden" x-model="lat" wire:model="lat">
                <input type="hidden" x-model="lng" wire:model="lng">
                <template x-if="lat && lng">
                    <p class="mt-2 text-sm" style="color:var(--muted)">
                        Lokasi: <strong class="font-mono-c" x-text="lat + ', ' + lng"></strong>
                    </p>
                </template>
            </div>
        </div>
    </div>

    {{-- ================= KONTEN (7 editor rich-text dalam 1 panel, label inline + divider) ================= --}}
    <div class="cms-panel">
        <div class="cms-panel-body" style="padding:18px 20px">

            {{-- Instansi (ID only) --}}
            <section x-show="lang === 'id'" class="ksect">
                <div class="cms-eyebrow mb-2">INSTANSI</div>
                @include('front.components.instansi')
                @error('instansi')
                    <p class="text-sm mt-1" style="color:var(--danger)">{{ $message }}</p>
                @enderror
            </section>

            {{-- Narasi Status (ID only) --}}
            <section x-show="lang === 'id'" class="ksect">
                <div class="cms-eyebrow mb-2">NARASI STATUS</div>
                @include('front.components.status')
                @error('status')
                    <p class="text-sm mt-1" style="color:var(--danger)">{{ $message }}</p>
                @enderror
            </section>

            {{-- Deskripsi (ID + EN) --}}
            <section class="ksect">
                <div class="cms-eyebrow mb-2">
                    <span x-show="lang === 'id'">DESKRIPSI KASUS</span>
                    <span x-show="lang === 'en'">CASES DESCRIPTION</span>
                </div>
                <div x-show="lang === 'id'">@includeWhen(true, 'front.components.tinymce-content-id')</div>
                <div x-show="lang === 'en'">@includeWhen(true, 'front.components.tinymce-content-en')</div>
                @error('description')
                    <p class="text-sm mt-1" style="color:var(--danger)">{{ $message }}</p>
                @enderror
            </section>

            {{-- Pembelajaran (ID + EN) --}}
            <section class="ksect">
                <div class="cms-eyebrow mb-2">
                    <span x-show="lang === 'id'">PEMBELAJARAN</span>
                    <span x-show="lang === 'en'">LESSON LEARNING</span>
                </div>
                <div x-show="lang === 'id'">@includeWhen(true, 'front.components.tinymce-pembelajaran-id')</div>
                <div x-show="lang === 'en'">@includeWhen(true, 'front.components.tinymce-pembelajaran-en')</div>
                @error('pembelajaran_en')
                    <p class="text-sm mt-1" style="color:var(--danger)">{{ $message }}</p>
                @enderror
            </section>

            {{-- Dugaan Permasalahan (ID + EN) --}}
            <section class="ksect">
                <div class="cms-eyebrow mb-2">
                    <span x-show="lang === 'id'">DUGAAN PERMASALAHAN</span>
                    <span x-show="lang === 'en'">ALLEGED ISSUES</span>
                </div>
                <div x-show="lang === 'id'">@includeWhen(true, 'front.components.tinymce-dugaan-permasalahan-id')</div>
                <div x-show="lang === 'en'">@includeWhen(true, 'front.components.tinymce-dugaan-permasalahan-en')</div>
                @error('dugaan_permasalahan_en')
                    <p class="text-sm mt-1" style="color:var(--danger)">{{ $message }}</p>
                @enderror
            </section>

            {{-- Perkembangan Kasus (ID + EN) --}}
            <section class="ksect">
                <div class="cms-eyebrow mb-2">
                    <span x-show="lang === 'id'">PERKEMBANGAN KASUS</span>
                    <span x-show="lang === 'en'">CASE DEVELOPMENT</span>
                </div>
                <div x-show="lang === 'id'">@includeWhen(true, 'front.components.tinymce-perkembangan-id')</div>
                <div x-show="lang === 'en'">@includeWhen(true, 'front.components.tinymce-perkembangan-en')</div>
                @error('perkembangan_en')
                    <p class="text-sm mt-1" style="color:var(--danger)">{{ $message }}</p>
                @enderror
            </section>

            {{-- Sumber (ID only) --}}
            <section x-show="lang === 'id'" class="ksect">
                <div class="cms-eyebrow mb-2">SUMBER</div>
                @include('front.components.sumber')
                @error('sumber')
                    <p class="text-sm mt-1" style="color:var(--danger)">{{ $message }}</p>
                @enderror
            </section>

        </div>
    </div>

    {{-- ================= KETERANGAN TAHAPAN ================= --}}
    <div class="cms-panel">
        <div class="cms-panel-head" style="padding:14px 20px">
            <div class="cms-panel-title">
                <span x-show="lang === 'id'">Keterangan Tahapan</span>
                <span x-show="lang === 'en'">Stage Notes</span>
            </div>
        </div>
        <div class="cms-panel-body" style="padding:16px 20px">
            <p class="text-sm mb-4" style="color:var(--muted)">
                Tampil di bawah tiap tahapan pada halaman detail kasus. Tahapannya mengikuti kualifikasi kasus.
                Kosongkan untuk memakai keterangan standar (teks abu-abu di dalam isian).
            </p>
            <div class="space-y-4">
                @foreach ($stagesId as $step => $stage)
                    <div wire:key="tahapan-{{ $stage['key'] }}">
                        <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5 block">
                            <span x-show="lang === 'id'">{{ $stage['label'] }}</span>
                            <span x-show="lang === 'en'">{{ $stagesEn[$step]['label'] }}</span>
                        </label>
                        <textarea x-show="lang === 'id'" wire:model="tahapan_id.{{ $stage['key'] }}" rows="2" maxlength="500"
                            class="cms-input w-full" placeholder="{{ $stage['desc'] }}"></textarea>
                        <textarea x-show="lang === 'en'" wire:model="tahapan_en.{{ $stage['key'] }}" rows="2" maxlength="500"
                            class="cms-input w-full" placeholder="{{ $stagesEn[$step]['desc'] }}"></textarea>
                        @error('tahapan_id.' . $stage['key'])
                            <p class="text-sm mt-1" style="color:var(--danger)">{{ $message }}</p>
                        @enderror
                        @error('tahapan_en.' . $stage['key'])
                            <p class="text-sm mt-1" style="color:var(--danger)">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ================= FOTO SAMPUL ================= --}}
    @php
        // Pratinjau: foto baru yang sedang diunggah, atau foto sampul yang tersimpan (kecuali ditandai hapus).
        try {
            $coverPreview = $cover && ! $errors->has('cover') ? $cover->temporaryUrl() : null;
        } catch (\Throwable) {
            $coverPreview = null;
        }
        $coverPreview ??= $existingCover && ! $removeCover ? asset('storage/' . $existingCover) : null;
    @endphp
    <div class="cms-panel">
        <div class="cms-panel-head" style="padding:14px 20px">
            <div class="cms-panel-title">Foto Sampul</div>
        </div>
        <div class="cms-panel-body" style="padding:16px 20px">
            <p class="text-sm mb-3" style="color:var(--muted)">
                Tampil di bagian atas halaman detail kasus dan saat tautan kasus dibagikan (WhatsApp, media sosial).
            </p>
            <div class="rounded p-3 mb-4 text-xs leading-relaxed" style="background:#FFF7E6;border:1px solid #F5D9A8;color:#7A4B00">
                <strong>Pastikan foto aman dipublikasikan:</strong> tidak memperlihatkan wajah atau identitas pelapor, korban, maupun saksi,
                tidak memuat dokumen pribadi, dan lembaga berhak memakainya. Jangan memakai foto dari Bukti tanpa pemeriksaan.
                Lokasi GPS dan metadata lain di dalam foto dihapus otomatis saat disimpan.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <div class="relative w-full aspect-video rounded overflow-hidden border border-[color:var(--hairline)] bg-[color:var(--paper-2)]">
                        @if ($coverPreview)
                            <img src="{{ $coverPreview }}" alt="Pratinjau foto sampul" class="absolute inset-0 w-full h-full object-cover">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-xs" style="color:var(--muted)">Belum ada foto sampul</div>
                        @endif
                        <div wire:loading.flex wire:target="cover" class="absolute inset-0 items-center justify-center text-xs" style="background:rgba(255,255,255,.8)">Mengunggah…</div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <input type="file" wire:model="cover" accept="image/jpeg,image/png,image/webp" class="text-sm">
                        @if ($coverPreview)
                            <button type="button" wire:click="removeCoverImage" class="cms-btn cms-btn-ghost">Hapus foto</button>
                        @endif
                    </div>
                    <p class="mt-2 text-xs" style="color:var(--muted)">JPG, PNG, atau WebP · minimal 800 × 450 px · maks. 8 MB · rasio 16:9 paling pas.</p>
                    @error('cover')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5 block">
                            <span x-show="lang === 'id'">Keterangan foto (ID)</span>
                            <span x-show="lang === 'en'">Photo caption (EN)</span>
                        </label>
                        <input x-show="lang === 'id'" type="text" wire:model="cover_caption_id" maxlength="255" class="cms-input w-full"
                            placeholder="mis. Bukaan tambang di kawasan hutan lindung, Maret 2026">
                        <input x-show="lang === 'en'" type="text" wire:model="cover_caption_en" maxlength="255" class="cms-input w-full"
                            placeholder="e.g. Mining pit inside a protected forest, March 2026">
                        <p class="mt-1 text-xs" style="color:var(--muted)">Juga dipakai sebagai teks alternatif untuk pembaca layar.</p>
                        @error('cover_caption_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        @error('cover_caption_en') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-medium text-[color:var(--muted)] mb-1.5 block">Kredit foto</label>
                        <input type="text" wire:model="cover_credit" maxlength="150" class="cms-input w-full" placeholder="mis. Dok. Auriga Nusantara">
                        @error('cover_credit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= BUKTI ================= --}}
    <div class="cms-panel">
        <div class="cms-panel-head" style="padding:14px 20px">
            <div class="cms-panel-title">Bukti Foto / Video</div>
        </div>
        <div class="cms-panel-body" style="padding:16px 20px">
            <input type="file" wire:model="bukti" multiple class="text-sm">
            @if ($existingBukti)
                <div class="grid grid-cols-4 gap-3 mt-3">
                    @foreach ($existingBukti as $file)
                        <div class="p-2 rounded border border-[color:var(--hairline)] text-xs text-center font-mono-c" style="color:var(--muted)">
                            {{ basename($file) }}
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ================= SUBMIT ================= --}}
    <div>
        <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
            onclick="console.log('[CaseForm] save button clicked')"
            class="cms-btn cms-btn-leaf">
            <span wire:loading.remove wire:target="save">Kirim Laporan</span>
            <span wire:loading wire:target="save">Menyimpan...</span>
        </button>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('case-save-debug', (event) => {
            console.log('[CaseForm]', event.step, event.payload)
        })
    })

    let mapController = null;

    document.addEventListener('livewire:init', () => {
        Livewire.on('location-updated', (event) => {
            if (mapController) mapController.drawPolygon(event.geometry);
        });
        Livewire.on('location-reset', () => {
            if (mapController) mapController.clearPolygon();
        });
    });

    function mapComponent(lat, lng) {
        return {
            map: null,
            marker: null,
            polygon: null,
            lat,
            lng,

            initMap() {
                mapController = this;

                this.map = L.map('map').setView(
                    this.lat && this.lng ? [this.lat, this.lng] : [-2.5489, 118.0149],
                    this.lat && this.lng ? 14 : 5
                );

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(this.map);

                this.map.on('click', (e) => this.onMapClick(e));

                this.addMarker();

                this.$watch('lat', () => this.updateMarker());
                this.$watch('lng', () => this.updateMarker());
            },

            addMarker() {
                if (!this.lat || !this.lng || !this.map) return;
                this.marker = L.marker([this.lat, this.lng]).addTo(this.map);
            },

            onMapClick(e) {
                this.lat = e.latlng.lat;
                this.lng = e.latlng.lng;
                @this.set('lat', e.latlng.lat);
                @this.set('lng', e.latlng.lng);
            },

            updateMarker() {
                if (!this.lat || !this.lng || !this.map) return;

                if (this.marker) {
                    this.marker.setLatLng([this.lat, this.lng]);
                } else {
                    this.addMarker();
                }

                this.map.setView([this.lat, this.lng], 14);
            },

            drawPolygon(geometry) {
                this.clearPolygon();
                if (!geometry) return;

                try {
                    this.polygon = L.geoJSON(geometry, {
                        style: {
                            color: '#2F6C14',
                            fillColor: '#9BDB4D',
                            fillOpacity: 0.15,
                            weight: 2,
                        },
                    }).addTo(this.map);
                    this.map.fitBounds(this.polygon.getBounds(), { padding: [30, 30] });
                } catch (e) {
                    console.warn('Failed to draw polygon', e);
                }
            },

            clearPolygon() {
                if (this.polygon) {
                    this.map.removeLayer(this.polygon);
                    this.polygon = null;
                }
            }
        }
    }
</script>
@endpush