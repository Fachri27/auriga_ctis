<div class="max-w-5xl mx-auto px-6 py-6 space-y-4" @if ($polling) wire:poll.5s @endif>

    {{-- Page header --}}
    <div class="flex items-center justify-between gap-4 border-b border-[color:var(--hairline)] pb-3">
        <div>
            <div class="cms-eyebrow">CMS / TERJEMAHAN</div>
            <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Auto Translate ID → EN</h1>
        </div>
        <button type="button"
            wire:click="translateAll"
            wire:confirm="Terjemahkan semua konten yang versi English-nya masih kosong? Proses berjalan di background dan memakai kuota DeepL."
            wire:loading.attr="disabled"
            @disabled(! $configured || collect($rows)->sum('pending') === 0)
            class="cms-btn cms-btn-leaf disabled:opacity-60 disabled:cursor-not-allowed">
            Terjemahkan semua ({{ collect($rows)->sum('pending') }})
        </button>
    </div>

    {{-- DeepL status --}}
    <div class="cms-panel cms-rise" style="animation-delay:.04s">
        <div class="cms-panel-body flex flex-wrap items-center justify-between gap-3" style="padding:16px 20px">
            @if (! $configured)
                <div class="text-sm text-[color:var(--ink)]">
                    <x-internal.badge variant="danger">Belum dikonfigurasi</x-internal.badge>
                    <span class="ml-2">Isi <code>DEEPL_API_KEY</code> di file <code>.env</code> untuk mengaktifkan terjemahan.</span>
                </div>
            @elseif ($usage)
                @php $percent = $usage['character_limit'] > 0 ? min(100, round($usage['character_count'] / $usage['character_limit'] * 100)) : 0; @endphp
                <div class="flex-1 min-w-[240px]">
                    <div class="flex items-center justify-between text-xs text-[color:var(--muted)] mb-1.5">
                        <span>Kuota DeepL periode ini</span>
                        <span class="num">{{ number_format($usage['character_count'], 0, ',', '.') }} / {{ number_format($usage['character_limit'], 0, ',', '.') }} karakter</span>
                    </div>
                    <div class="h-2 rounded-full bg-[color:var(--paper-2)] overflow-hidden">
                        <div class="h-full rounded-full" style="width: {{ $percent }}%; background: {{ $percent >= 90 ? 'var(--danger)' : 'var(--leaf-deep)' }}"></div>
                    </div>
                </div>
            @else
                <div class="text-sm text-[color:var(--muted)]">
                    <x-internal.badge variant="success">Terhubung</x-internal.badge>
                    <span class="ml-2">Info kuota DeepL tidak tersedia saat ini.</span>
                </div>
            @endif

            @if ($polling)
                <x-internal.badge variant="info">Sedang diproses di background…</x-internal.badge>
            @endif
        </div>
    </div>

    {{-- Content types --}}
    <div class="cms-panel cms-rise" style="animation-delay:.08s">
        <div class="cms-panel-head">
            <div>
                <div class="cms-panel-title">Konten</div>
                <div class="cms-panel-sub">Hanya field English yang masih kosong yang diterjemahkan; isi yang sudah ada tidak ditimpa.</div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="cms-table">
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Belum lengkap (EN)</th>
                        <th class="text-right">± Karakter</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr wire:key="translation-{{ $row['type'] }}">
                            <td>{{ $row['label'] }}</td>
                            <td class="num text-right">{{ $row['total'] }}</td>
                            <td class="num text-right">
                                @if ($row['pending'] > 0)
                                    <x-internal.badge variant="warning">{{ $row['pending'] }}</x-internal.badge>
                                @else
                                    <x-internal.badge variant="success">Lengkap</x-internal.badge>
                                @endif
                            </td>
                            <td class="num text-right">{{ number_format($row['characters'], 0, ',', '.') }}</td>
                            <td class="text-right">
                                <button type="button"
                                    wire:click="translate('{{ $row['type'] }}')"
                                    wire:loading.attr="disabled"
                                    @disabled(! $configured || $row['pending'] === 0)
                                    class="cms-btn cms-btn-ghost disabled:opacity-50 disabled:cursor-not-allowed">
                                    Terjemahkan
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-xs text-[color:var(--muted)]">
        Terjemahan berjalan lewat queue. Pastikan queue worker aktif (<code>php artisan queue:work</code>).
        Untuk menerjemahkan satu item sambil memeriksa hasilnya, gunakan tombol <em>Terjemahkan ID → EN</em> di form edit.
    </p>
</div>
