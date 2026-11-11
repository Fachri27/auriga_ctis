{{-- Antrean dibaca berurutan: panggilan berikutnya dikirim setelah yang sebelumnya selesai.
     (wire:poll tidak dipakai karena memanggil ulang tanpa menunggu, sehingga panggilan menumpuk dalam satu request.) --}}
<div class="max-w-7xl mx-auto px-6 py-6 space-y-4"
    x-data="{
        reading: false,
        async readQueue() {
            if (this.reading) return;
            this.reading = true;
            try {
                while (await $wire.processQueue()) {
                    // Dijeda karena Google membatasi unduhan: tunggu sampai waktu lanjut.
                    if ($wire.resumeIn > 0) await new Promise((resolve) => setTimeout(resolve, $wire.resumeIn * 1000 + 500));
                }
            } finally { this.reading = false; }
        },
    }"
    x-on:case-import-read.window="readQueue()"
    x-on:case-import-invalid.window="$nextTick(() => document.querySelector('[data-row-invalid]')?.scrollIntoView({ behavior: 'smooth', block: 'center' }))">

    {{-- Page header --}}
    <div class="flex items-center justify-between gap-4 border-b border-[color:var(--hairline)] pb-3">
        <div>
            <div class="cms-eyebrow">CASES / IMPOR</div>
            <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Impor Kasus dari Dokumen</h1>
        </div>
        <a href="{{ route('case.index') }}" class="cms-btn cms-btn-ghost">Kembali ke daftar</a>
    </div>

    @if (session('success'))
        <div class="cms-pill cms-pill-ok"><span class="dot"></span>{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-3 text-sm border rounded-lg" style="color:var(--danger);border-color:var(--danger)">{{ session('error') }}</div>
    @endif

    {{-- Sumber dokumen --}}
    <div class="cms-panel cms-rise" style="animation-delay:.04s">
        <div class="cms-panel-head">
            <div>
                <div class="cms-panel-title">Sumber dokumen</div>
                <div class="cms-panel-sub">Satu dokumen = satu kasus. Format: judul, label (Pengungkap, Terlapor, Penyidik, Lokasi, Tahun), lalu bagian Kronologi, Perkembangan, Dugaan Permasalahan, Lesson Learning, Status, Sumber.</div>
            </div>
        </div>
        <div class="cms-panel-body grid grid-cols-1 lg:grid-cols-2 gap-6" style="padding:16px 20px">
            <form wire:submit="loadFolder" class="space-y-2">
                <label class="block text-xs font-medium text-[color:var(--muted)]">Link folder Google Drive</label>
                <div class="flex gap-2">
                    <input type="text" wire:model="folderUrl" placeholder="https://drive.google.com/drive/folders/…" class="cms-input w-full" @disabled(! $driveConfigured)>
                    <button type="submit" class="cms-btn cms-btn-primary whitespace-nowrap disabled:opacity-60" wire:loading.attr="disabled" wire:target="loadFolder" @disabled(! $driveConfigured)>
                        <span wire:loading.remove wire:target="loadFolder">Baca folder</span>
                        <span wire:loading wire:target="loadFolder">Membaca…</span>
                    </button>
                </div>
                <div class="flex items-center gap-2 text-xs text-[color:var(--muted)]">
                    <span>Dibaca bertahap, per</span>
                    <select wire:model="perBatch" class="cms-input py-1">
                        @foreach (\App\Livewire\Cases\CaseImport::BATCH_SIZES as $size)
                            <option value="{{ $size }}">{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>dokumen. Dokumen yang sudah diimpor dilewati.</span>
                </div>
                @error('folderUrl') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror
                @unless ($driveConfigured)
                    <p class="text-xs text-[color:var(--muted)]">Google Drive belum dikonfigurasi: isi <code>GOOGLE_DRIVE_API_KEY</code> di <code>.env</code> (folder dibagikan "Siapa saja yang memiliki link"), atau pasang file service account.</p>
                @endunless
            </form>

            <div class="space-y-2">
                <label class="block text-xs font-medium text-[color:var(--muted)]">Atau upload file .docx (maks. {{ (int) ini_get('max_file_uploads') ?: 20 }} file sekali upload)</label>
                <input type="file" wire:model="uploads" multiple accept=".docx" class="text-sm">
                <div wire:loading wire:target="uploads" class="text-xs text-[color:var(--muted)]">Membaca file…</div>
                <p class="text-xs text-[color:var(--muted)]">Tidak memakai Google Drive API, jadi tidak terkena pembatasan Google. Dari Drive: pilih file → Download (Google Docs otomatis jadi .docx).</p>
                @error('uploads.*') <p class="text-sm text-[color:var(--danger)]">{{ $message }}</p> @enderror
            </div>
        </div>

        @if ($total && ($remaining || $alreadyImported))
            <div class="cms-panel-body border-t border-[color:var(--hairline)] text-xs text-[color:var(--muted)]" style="padding:10px 20px">
                Tahap ini: {{ $total }} dokumen.
                @if ($remaining) {{ $remaining }} dokumen lagi menunggu tahap berikutnya (impor tahap ini dulu, lalu klik "Baca folder" lagi). @endif
                @if ($alreadyImported) {{ $alreadyImported }} dokumen sudah pernah diimpor dan dilewati. @endif
            </div>
        @endif

        @if ($queue)
            <div class="cms-panel-body border-t border-[color:var(--hairline)]" style="padding:12px 20px">
                @php $done = $total - count($queue); @endphp
                <div class="flex items-center justify-between text-xs text-[color:var(--muted)] mb-1.5">
                    @if ($resumeIn > 0)
                        <span class="text-[color:var(--danger)]">Google sementara membatasi unduhan. Dilanjutkan otomatis dalam ±{{ (int) ceil($resumeIn / 60) }} menit; biarkan halaman ini tetap terbuka.</span>
                    @else
                        <span>Membaca dokumen dari Drive… biarkan halaman ini tetap terbuka.</span>
                    @endif
                    <span class="num">{{ $done }} / {{ $total }}</span>
                </div>
                <div class="h-2 rounded-full bg-[color:var(--paper-2)] overflow-hidden">
                    <div class="h-full rounded-full bg-[color:var(--leaf-deep)] transition-all" style="width: {{ $total ? round($done / $total * 100) : 0 }}%"></div>
                </div>
            </div>
        @endif
    </div>

    {{-- Pratinjau --}}
    @if ($rows)
        <div class="cms-panel cms-rise" style="animation-delay:.08s">
            <div class="cms-panel-head flex-wrap gap-3">
                <div>
                    <div class="cms-panel-title">Pratinjau ({{ count($rows) }} dokumen)</div>
                    <div class="cms-panel-sub">Periksa kategori dan tanggal. Semua kasus diimpor berstatus Terbuka sebagai draft; status dan publikasi diatur dari halaman detail setelah dicek.</div>
                </div>
                <div class="flex items-center gap-2">
                    @if ($this->failedCount && ! $queue)
                        <button type="button" wire:click="retryFailed" class="cms-btn cms-btn-danger whitespace-nowrap">Coba lagi yang gagal ({{ $this->failedCount }})</button>
                    @endif
                    <select wire:model="defaultCategoryId" class="cms-input">
                        <option value="">Kategori default…</option>
                        @foreach ($this->categories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="applyDefaultCategory" class="cms-btn cms-btn-ghost whitespace-nowrap">Terapkan ke yang kosong</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="cms-table">
                    <thead>
                        <tr>
                            <th class="w-8">
                                <input type="checkbox" class="h-4 w-4" title="Pilih semua"
                                    @checked($this->selectedCount > 0) wire:click="selectAll($event.target.checked)">
                            </th>
                            <th>Dokumen</th>
                            <th>Lokasi (titik peta)</th>
                            <th>Tanggal kejadian</th>
                            <th>Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $row)
                            @php $rowInvalid = $errors->has("rows.$i.category_id") || $errors->has("rows.$i.event_date"); @endphp
                            <tr wire:key="import-row-{{ $row['ref'] }}" @if ($rowInvalid) data-row-invalid @endif
                                @class(['opacity-60' => $row['error'] || ($row['existing'] ?? false)])
                                @style(['background: color-mix(in srgb, var(--danger) 8%, transparent)' => $rowInvalid])>
                                <td class="align-top">
                                    <input type="checkbox" class="h-4 w-4" wire:model.live="rows.{{ $i }}.selected" @disabled($row['error'] || ($row['existing'] ?? false))>
                                </td>
                                <td class="align-top min-w-[280px]">
                                    <div class="font-medium text-[color:var(--ink)]">{{ $row['title'] }}</div>
                                    <div class="text-xs text-[color:var(--muted)]">{{ $row['name'] }}</div>
                                    @if ($row['error'])
                                        <div class="mt-1"><x-internal.badge variant="danger">Gagal dibaca</x-internal.badge> <span class="text-xs">{{ $row['error'] }}</span></div>
                                    @else
                                        <div class="mt-1 text-[11px] text-[color:var(--muted)]">{{ count($row['sections']) }} bagian: {{ implode(', ', $row['sections']) }}</div>
                                        @if ($row['existing'])
                                            <div class="mt-1"><x-internal.badge variant="info">Sudah diimpor ({{ $row['existing'] }})</x-internal.badge></div>
                                        @endif
                                        @foreach ($row['warnings'] as $warning)
                                            <div class="mt-1 text-xs text-[color:var(--danger)]">⚠ {{ $warning }}</div>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="align-top min-w-[220px]">
                                    @unless ($row['error'])
                                        @if ($row['location'])
                                            <div class="text-sm">{{ $row['location'] }}</div>
                                            <x-internal.badge :variant="in_array($row['precision'], ['desa', 'kecamatan', 'manual']) ? 'success' : 'warning'" size="sm">{{ $row['precision'] === 'manual' ? 'titik manual' : $row['precision'] }}</x-internal.badge>
                                        @else
                                            <span class="text-xs text-[color:var(--muted)]">Belum ada titik</span>
                                        @endif
                                        @if ($row['lokasi'])
                                            <div class="mt-1 text-[11px] text-[color:var(--muted)]">Dokumen: {{ $row['lokasi'] }}</div>
                                        @endif
                                        <button type="button" class="cms-btn cms-btn-ghost mt-1.5 text-xs"
                                            x-on:click="$dispatch('pick-point', @js(['index' => $i, 'title' => $row['title'], 'lokasi' => $row['lokasi'], 'lat' => $row['lat'] ?? null, 'lng' => $row['lng'] ?? null, 'precision' => $row['precision']]))">
                                            Atur titik
                                        </button>
                                    @endunless
                                </td>
                                <td class="align-top">
                                    @unless ($row['error'])
                                        <input type="date" wire:model="rows.{{ $i }}.event_date" class="cms-input">
                                        @error("rows.$i.event_date") <p class="text-xs text-[color:var(--danger)]">{{ $message }}</p> @enderror
                                    @endunless
                                </td>
                                <td class="align-top">
                                    @unless ($row['error'])
                                        <select wire:model="rows.{{ $i }}.category_id" class="cms-input">
                                            <option value="">Pilih…</option>
                                            @foreach ($this->categories as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @if (($row['category_reason'] ?? null) && (int) $row['category_id'] === (int) $row['category_suggested'])
                                            <p class="mt-1 text-[11px] text-[color:var(--muted)]">Otomatis dari isi dokumen: {{ $row['category_reason'] }}</p>
                                        @endif
                                        @error("rows.$i.category_id") <p class="text-xs text-[color:var(--danger)]">{{ $message }}</p> @enderror
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="cms-panel-body flex flex-wrap items-center justify-between gap-3 border-t border-[color:var(--hairline)]" style="padding:16px 20px">
                <label class="flex items-center gap-2 text-sm text-[color:var(--ink)]">
                    <input type="checkbox" wire:model="translate" class="h-4 w-4" @disabled(! $deeplConfigured)>
                    Terjemahkan otomatis ke English (DeepL) setelah impor
                    @unless ($deeplConfigured)
                        <span class="text-xs text-[color:var(--muted)]">— DeepL belum dikonfigurasi</span>
                    @endunless
                </label>

                <div class="flex items-center gap-3">
                    @error('rows') <span class="text-sm text-[color:var(--danger)] max-w-xl">{{ $message }}</span> @enderror
                    @if ($queue)
                        <span class="text-xs text-[color:var(--muted)]">{{ count($queue) }} dokumen masih menunggu dibaca; yang sudah terbaca bisa diimpor sekarang.</span>
                    @endif
                    <button type="button" wire:click="import" wire:loading.attr="disabled" wire:target="import"
                        @disabled($this->selectedCount === 0)
                        class="cms-btn cms-btn-leaf disabled:opacity-60 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="import">Impor {{ $this->selectedCount }} kasus</span>
                        <span wire:loading wire:target="import">Mengimpor…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Pemilih titik peta (satu peta dipakai bersama untuk semua baris pratinjau) --}}
    <div x-data="importPointPicker()" x-on:pick-point.window="open($event.detail)" wire:ignore>
        <div x-show="visible" x-cloak x-on:keydown.escape.window="close()"
            class="fixed inset-0 z-[2000] flex items-center justify-center p-4" style="background:rgba(11,30,7,0.45)">
            <div class="cms-panel w-full max-w-3xl" x-on:click.outside="close()">
                <div class="cms-panel-head">
                    <div>
                        <div class="cms-panel-title">Atur titik peta</div>
                        <div class="cms-panel-sub" x-text="title"></div>
                    </div>
                </div>
                <div class="cms-panel-body space-y-3" style="padding:16px 20px">
                    <p class="text-xs text-[color:var(--muted)]" x-show="lokasi">Lokasi di dokumen: <strong class="text-[color:var(--ink)]" x-text="lokasi"></strong></p>
                    <div x-ref="map" class="w-full rounded border border-[color:var(--hairline)]" style="height:420px"></div>
                    <div class="flex gap-2">
                        <input type="text" x-model="coordInput" x-on:keydown.enter.prevent="applyCoords()"
                            placeholder="Tempel koordinat, mis. -0.2205, 101.8731" class="cms-input flex-1">
                        <button type="button" x-on:click="applyCoords()" class="cms-btn cms-btn-ghost">Pakai koordinat</button>
                    </div>
                    <p class="text-xs text-[color:var(--muted)]">
                        Klik peta atau geser penanda untuk memindahkan titik. Pilih tampilan <em>Satelit</em> di pojok kanan atas peta untuk melihat lokasi sebenarnya.
                        <span x-show="lat !== null">Titik: <strong class="font-mono-c text-[color:var(--ink)]" x-text="lat + ', ' + lng"></strong></span>
                    </p>
                </div>
                <div class="cms-panel-body flex justify-between gap-2 border-t border-[color:var(--hairline)]" style="padding:12px 20px">
                    <button type="button" x-on:click="clearPoint()" class="cms-btn cms-btn-danger" x-show="lat !== null">Hapus titik</button>
                    <div class="flex gap-2 ml-auto">
                        <button type="button" x-on:click="close()" class="cms-btn cms-btn-ghost">Batal</button>
                        <button type="button" x-on:click="save()" x-bind:disabled="lat === null" class="cms-btn cms-btn-leaf disabled:opacity-60">Simpan titik</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Pemilih titik untuk pratinjau impor kasus (Leaflet sudah dimuat oleh layout internal).
    function importPointPicker() {
        const ZOOM = { desa: 13, kecamatan: 11, kabupaten: 9, provinsi: 7, manual: 14 };

        return {
            visible: false, index: null, title: '', lokasi: '', lat: null, lng: null, coordInput: '',
            map: null, marker: null,

            open(detail) {
                this.index = detail.index;
                this.title = detail.title;
                this.lokasi = detail.lokasi || '';
                this.coordInput = '';
                this.visible = true;

                this.$nextTick(() => {
                    this.ensureMap();
                    this.map.invalidateSize();
                    if (detail.lat !== null && detail.lng !== null) {
                        this.place(detail.lat, detail.lng);
                        this.map.setView([this.lat, this.lng], ZOOM[detail.precision] ?? 12);
                    } else {
                        this.removeMarker();
                        this.map.setView([-2.5489, 118.0149], 5);
                    }
                });
            },

            ensureMap() {
                if (this.map) return;

                const peta = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' });
                const satelit = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19, attribution: 'Esri' });

                this.map = L.map(this.$refs.map, { layers: [peta] });
                L.control.layers({ 'Peta': peta, 'Satelit': satelit }).addTo(this.map);
                this.map.on('click', (e) => this.place(e.latlng.lat, e.latlng.lng));
            },

            place(lat, lng) {
                this.lat = Number(Number(lat).toFixed(7));
                this.lng = Number(Number(lng).toFixed(7));

                if (!this.marker) {
                    this.marker = L.marker([this.lat, this.lng], { draggable: true }).addTo(this.map);
                    this.marker.on('dragend', (e) => {
                        const p = e.target.getLatLng();
                        this.lat = Number(p.lat.toFixed(7));
                        this.lng = Number(p.lng.toFixed(7));
                    });
                } else {
                    this.marker.setLatLng([this.lat, this.lng]);
                }
            },

            removeMarker() {
                if (this.marker) this.map.removeLayer(this.marker);
                this.marker = null;
                this.lat = null;
                this.lng = null;
            },

            // "-0.2205, 101.8731" atau "-0,2205; 101,8731"
            applyCoords() {
                const m = this.coordInput.match(/(-?\d+(?:[.,]\d+)?)\s*[,;\s]\s*(-?\d+(?:[.,]\d+)?)/);
                if (!m) return;
                this.place(parseFloat(m[1].replace(',', '.')), parseFloat(m[2].replace(',', '.')));
                this.map.setView([this.lat, this.lng], 14);
            },

            save() {
                if (this.lat === null) return;
                this.$wire.setPoint(this.index, this.lat, this.lng);
                this.close();
            },

            clearPoint() {
                this.$wire.setPoint(this.index, null, null);
                this.close();
            },

            close() {
                this.visible = false;
            },
        };
    }
</script>
@endpush
