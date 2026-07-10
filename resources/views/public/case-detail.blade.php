@extends('layouts.main')

@section('content')

<style>
.case-summary img,
.case-summary video,
.case-summary iframe {
    max-width: 100%;
    height: auto;
}
.case-summary {
    word-break: break-word;
    overflow-wrap: break-word;
}
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>

    <div class="bg-gray-50 mt-20">

        @php
            $trans = $case->translations->firstWhere('locale', app()->getLocale()) ?? $case->translations->first();

            $categoryTrans =
                $case->category?->translations->firstWhere('locale', app()->getLocale()) ??
                $case->category?->translations->first();

            $noTransMsg = '<em class="text-gray-400">Konten tidak tersedia dalam bahasa ini</em>';

            // Status steps untuk progress bar
            $statusSteps = [
                'open' => ['label' => 'Terbuka', 'step' => 1],
                'verified' => ['label' => 'Terverifikasi', 'step' => 2],
                'published' => ['label' => 'Dipublikasikan', 'step' => 3],
                'penyelidikan' => ['label' => 'Penyelidikan', 'step' => 4],
                'investigation' => ['label' => 'Investigasi', 'step' => 4],
                'penyidikan' => ['label' => 'Penyidikan', 'step' => 5],
                'prosecution' => ['label' => 'Penuntutan', 'step' => 6],
                'trial' => ['label' => 'Persidangan', 'step' => 7],
                'vonis' => ['label' => 'Vonis', 'step' => 8],
                'Berkekuatan hukum tetap' => ['label' => 'Berkekuatan Hukum', 'step' => 9],
                'executed' => ['label' => 'Putusan Dijalankan', 'step' => 10],
                'completed' => ['label' => 'Selesai', 'step' => 11],
                'closed' => ['label' => 'Ditutup', 'step' => 12],
                'rejected' => ['label' => 'Ditolak', 'step' => 12],
                'converted' => ['label' => 'Dikonversi', 'step' => 12],
            ];

            $currentKey = $case->status?->key ?? '';
            $currentStep = $statusSteps[$currentKey]['step'] ?? 0;

            $visibleSteps = [
                1 => [
                    'label' => 'Terbuka',
                    'tooltip' => 'Laporan atau kasus baru masuk dan telah didaftarkan ke sistem.',
                ],
                3 => [
                    'label' => 'Dipublikasikan',
                    'tooltip' => 'Informasi kasus telah dipublikasikan dan dapat diakses oleh publik.',
                ],
                4 => [
                    'label' => 'Penyelidikan',
                    'tooltip' =>
                        'Penegak hukum sedang mengumpulkan bukti awal untuk menentukan apakah ada tindak pidana.',
                ],
                5 => [
                    'label' => 'Penyidikan',
                    'tooltip' => 'Bukti sudah cukup. Penyidik resmi mengusut tersangka dan mengumpulkan alat bukti.',
                ],
                6 => [
                    'label' => 'Penuntutan',
                    'tooltip' => 'Jaksa menyusun surat dakwaan dan mempersiapkan kasus untuk dibawa ke pengadilan.',
                ],
                7 => [
                    'label' => 'Persidangan',
                    'tooltip' => 'Kasus sedang disidangkan di pengadilan. Hakim memeriksa bukti dan keterangan saksi.',
                ],
                8 => [
                    'label' => 'Vonis',
                    'tooltip' => 'Hakim telah menjatuhkan putusan bersalah atau tidak bersalah kepada terdakwa.',
                ],
                9 => [
                    'label' => 'Hukum Tetap',
                    'tooltip' => 'Putusan telah berkekuatan hukum tetap (inkracht) — tidak bisa digugat lagi.',
                ],
                10 => [
                    'label' => 'Dijalankan',
                    'tooltip' => 'Terpidana sedang menjalani hukuman sesuai putusan pengadilan.',
                ],
                11 => ['label' => 'Selesai', 'tooltip' => 'Seluruh proses hukum telah selesai.'],
            ];

            // Penjelasan status dalam bahasa sederhana
            $statusExplainer = [
                'open' => [
                    'icon' => '📂',
                    'color' => 'blue',
                    'text' => 'Kasus ini baru saja masuk dan sedang menunggu proses lebih lanjut dari penegak hukum.',
                ],
                'verified' => [
                    'icon' => '✅',
                    'color' => 'blue',
                    'text' => 'Laporan kasus ini sudah diverifikasi kebenarannya oleh tim kami.',
                ],
                'published' => [
                    'icon' => '📢',
                    'color' => 'blue',
                    'text' => 'Kasus ini sudah dipublikasikan agar masyarakat dapat mengikuti perkembangannya.',
                ],
                'penyelidikan' => [
                    'icon' => '🔍',
                    'color' => 'yellow',
                    'text' => 'Penegak hukum sedang mengumpulkan bukti awal. Belum ada tersangka resmi saat ini.',
                ],
                'investigation' => [
                    'icon' => '🔍',
                    'color' => 'yellow',
                    'text' => 'Penegak hukum sedang mengumpulkan bukti awal. Belum ada tersangka resmi saat ini.',
                ],
                'penyidikan' => [
                    'icon' => '🔎',
                    'color' => 'yellow',
                    'text' =>
                        'Sudah ada tersangka. Penyidik resmi sedang mengumpulkan alat bukti untuk pelimpahan ke jaksa.',
                ],
                'prosecution' => [
                    'icon' => '⚖️',
                    'color' => 'orange',
                    'text' => 'Jaksa sedang menyusun surat dakwaan. Kasus ini akan segera masuk ke pengadilan.',
                ],
                'trial' => [
                    'icon' => '🏛️',
                    'color' => 'orange',
                    'text' =>
                        'Sidang sedang berlangsung di pengadilan. Hakim, jaksa, dan pengacara memeriksa fakta-fakta kasus.',
                ],
                'vonis' => [
                    'icon' => '🔨',
                    'color' => 'purple',
                    'text' => 'Hakim telah menjatuhkan putusan. Terdakwa dinyatakan bersalah atau bebas.',
                ],
                'Berkekuatan hukum tetap' => [
                    'icon' => '📌',
                    'color' => 'purple',
                    'text' =>
                        'Putusan sudah final dan tidak bisa digugat lagi. Proses banding atau kasasi telah selesai.',
                ],
                'executed' => [
                    'icon' => '🏢',
                    'color' => 'red',
                    'text' => 'Terpidana sedang menjalani hukuman — baik penjara, denda, maupun hukuman lainnya.',
                ],
                'completed' => [
                    'icon' => '✔️',
                    'color' => 'green',
                    'text' => 'Seluruh proses hukum pada kasus ini telah selesai.',
                ],
                'closed' => ['icon' => '🔒', 'color' => 'gray', 'text' => 'Kasus ini telah ditutup.'],
                'rejected' => [
                    'icon' => '❌',
                    'color' => 'red',
                    'text' => 'Kasus ini ditolak karena tidak memenuhi syarat atau tidak cukup bukti.',
                ],
                'converted' => [
                    'icon' => '🔄',
                    'color' => 'gray',
                    'text' => 'Status kasus ini telah dikonversi ke proses atau jalur hukum lain.',
                ],
            ];

            $explainer = $statusExplainer[$currentKey] ?? null;
            $explainerColor = [
                'blue' => 'bg-blue-50 border-blue-200 text-blue-800',
                'yellow' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
                'orange' => 'bg-orange-50 border-orange-200 text-orange-800',
                'purple' => 'bg-purple-50 border-purple-200 text-purple-800',
                'red' => 'bg-red-50 border-red-200 text-red-800',
                'green' => 'bg-green-50 border-green-200 text-green-800',
                'gray' => 'bg-gray-50 border-gray-200 text-gray-700',
            ];

            $shareUrl = urlencode(request()->fullUrl());
            $shareTitle = urlencode(strip_tags($trans?->title ?? 'Detail Kasus'));

            // Remove any <img> tags from the summary when showing the hero/lead
            $summaryLead = null;
            if (!empty($trans?->summary)) {
                $summaryLead = preg_replace('/<img[^>]*>/i', '', $trans->summary);
            }
        @endphp

        {{-- ===================== HERO ===================== --}}
        {{-- ===================== HERO ===================== --}}
        <section class="bg-white border-b">
            <div class="max-w-7xl mx-auto px-6 py-10">

                {{-- Baris atas: Kategori + Status sejajar --}}
                <div class="flex items-center gap-2 mb-4 flex-wrap">
                    @if ($categoryTrans?->name)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            {{ $categoryTrans->name }}
                        </span>
                    @endif
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                        {{ $case->status?->key === 'investigation' ||
                        $case->status?->key === 'penyelidikan' ||
                        $case->status?->key === 'penyidikan'
                            ? 'bg-yellow-100 text-yellow-800'
                            : 'bg-green-100 text-green-800' }}">
                        {{ $case->status->name ?? 'Status' }}
                    </span>
                </div>

                {{-- Judul --}}
                <h1 class="text-3xl md:text-4xl font-bold leading-tight max-w-4xl">
                    {!! $trans?->title ?? $noTransMsg !!}
                </h1>

                {{-- Ringkasan singkat --}}
                <p class="mt-4 text-gray-600 max-w-3xl leading-relaxed">
                    {!! strip_tags($summaryLead ?? $trans?->summary ?? '') !!}
                </p>

                {{-- Meta info inti: hanya 3 hal paling penting untuk konteks cepat --}}
                <div class="mt-6 flex flex-wrap gap-6 text-sm text-gray-700">
                    <div>
                        <span class="block text-gray-500 text-xs">Nomor Kasus</span>
                        <strong class="text-gray-900">{{ $case->case_number }}</strong>
                    </div>
                    <div>
                        <span class="block text-gray-500 text-xs">Tanggal Kejadian</span>
                        <strong class="text-gray-900">{{ $case->event_date ?? '-' }}</strong>
                    </div>
                    <div>
                        <span class="block text-gray-500 text-xs">Lokasi</span>
                        <strong class="text-gray-900">{{ $location['province'] ?? '-' }}{{ !empty($location['district']) ? ', ' . $location['district'] : '' }}</strong>
                    </div>
                </div>

                {{-- Metadata sekunder: kecil, tidak bersaing dengan info inti --}}
                <p class="mt-3 text-xs text-gray-400">
                    Dipublikasikan {{ optional($case->published_at)->format('d M Y') ?? '-' }}
                    &middot;
                    Diperbarui {{ optional($case->updated_at)->format('d M Y, H:i') }} WIB
                </p>

                {{-- ===== STATUS EXPLAINER (gabungan badge + narasi + durasi) ===== --}}
                <!--@if ($explainer)
                    @php
                        $daysSinceUpdate = $case->updated_at ? $case->updated_at->diffInDays(now()) : null;
                        $durationText = null;
                        if (!is_null($daysSinceUpdate)) {
                            if ($daysSinceUpdate < 1) {
                                $durationText = 'Diperbarui hari ini.';
                            } elseif ($daysSinceUpdate < 30) {
                                $durationText = "Sudah {$daysSinceUpdate} hari berada di tahap ini.";
                            } else {
                                $months = intdiv($daysSinceUpdate, 30);
                                $durationText = "Sudah sekitar {$months} bulan berada di tahap ini.";
                            }
                        }
                    @endphp
                    <div
                        class="mt-6 flex items-start gap-3 px-4 py-3 rounded-xl border {{ $explainerColor[$explainer['color']] ?? 'bg-gray-50 border-gray-200 text-gray-700' }} max-w-3xl">
                        <span class="text-xl mt-0.5">{{ $explainer['icon'] }}</span>
                        <div>
                            <p class="text-sm font-semibold mb-0.5">
                                Apa artinya status "{{ $case->status->name ?? '' }}"?
                            </p>
                            <p class="text-sm leading-relaxed">{{ $explainer['text'] }}</p>
                            @if ($durationText)
                                <p class="text-xs mt-1.5 opacity-75 font-medium">⏱️ {{ $durationText }}</p>
                            @endif
                        </div>
                    </div>
                @endif-->

                {{-- ===== SHARE BUTTONS ===== --}}
                <div class="mt-6 flex items-center gap-3 flex-wrap">
                    <span class="text-xs text-gray-400 font-medium uppercase tracking-wide">Bagikan:</span>

                    <a href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 hover:bg-green-100 transition-colors border border-green-200">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                            <path
                                d="M12 0C5.373 0 0 5.373 0 12c0 2.127.558 4.126 1.535 5.858L.057 23.5l5.797-1.522A11.942 11.942 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.894a9.878 9.878 0 01-5.031-1.376l-.361-.214-3.741.981.999-3.648-.235-.374A9.865 9.865 0 012.106 12C2.106 6.58 6.58 2.106 12 2.106c5.421 0 9.894 4.474 9.894 9.894 0 5.421-4.473 9.894-9.894 9.894z" />
                        </svg>
                        WhatsApp
                    </a>

                    <a href="https://twitter.com/intent/tweet?text={{ $shareTitle }}&url={{ $shareUrl }}"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 hover:bg-sky-100 transition-colors border border-sky-200">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.737-8.835L1.254 2.25H8.08l4.259 5.63L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z" />
                        </svg>
                        Twitter/X
                    </a>

                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors border border-blue-200">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                        Facebook
                    </a>

                    <button onclick="copyLink()" id="copyBtn"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors border border-gray-200 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span id="copyLabel">Salin Link</span>
                    </button>
                </div>

                {{-- ===== IKUTI KASUS INI ===== --}}
                @if (! in_array($case->status?->key, ['completed', 'closed']))
                    <div class="mt-8 pt-6 border-t border-gray-100 max-w-xl">
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-widest">Ikuti Perkembangan Kasus Ini</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Dapatkan notifikasi email setiap ada perkembangan terbaru pada kasus ini.
                        </p>
                        <div class="mt-3">
                            @livewire('case-subscribe-form', ['caseId' => $case->id])
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- ===================== PROGRESS BAR ===================== --}}
        <section class="bg-white border-b">
            <div class="max-w-7xl mx-auto px-6 py-6">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-widest">Tahapan Kasus</h2>
                    <span class="text-xs text-gray-400 italic">Hover tiap tahap untuk penjelasan</span>
                </div>

                {{-- Ringkasan progres singkat: posisi saat ini dari total tahap --}}
                @php
                    $totalSteps = 11; // step 1 - 11 sesuai $visibleSteps
                    $currentLabel = $visibleSteps[$currentStep]['label'] ?? null;
                @endphp
                @if ($currentLabel)
                    <p class="text-xs text-gray-500 mb-5">
                        Kasus ini berada pada tahap <strong class="text-gray-700">{{ $currentLabel }}</strong>
                        (tahap {{ $currentStep }} dari {{ $totalSteps }}).
                    </p>
                @endif

                {{-- Mobile --}}
                <div class="flex md:hidden flex-wrap gap-2">
                    @foreach ($visibleSteps as $step => $info)
                        @php
                            $done = $currentStep >= $step;
                            $active = $currentStep === $step;
                        @endphp
                        <span
                            class="px-3 py-1 rounded-full text-xs font-semibold
                    {{ $active ? 'bg-green-600 text-white' : ($done ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400') }}">
                            {{ $info['label'] }}
                        </span>
                    @endforeach
                </div>

                {{-- Desktop with tooltips --}}
                <div class="hidden md:flex items-start relative">
                    <div class="absolute top-4 left-0 right-0 h-0.5 bg-gray-200 z-0"></div>
                    @foreach ($visibleSteps as $step => $info)
                        @php
                            $done = $currentStep >= $step;
                            $active = $currentStep === $step;
                        @endphp
                        <div class="flex-1 flex flex-col items-center relative z-10 group/step">
                            {{-- Dot --}}
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition-all cursor-default
                        {{ $active ? 'bg-green-600 border-green-600 shadow-lg shadow-green-200 ring-4 ring-green-100' : ($done ? 'bg-green-500 border-green-500' : 'bg-white border-gray-300') }}">
                                @if ($done)
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="3"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                @endif
                            </div>
                            {{-- Label --}}
                            <p
                                class="mt-2 text-center text-[10px] leading-tight font-medium max-w-[70px]
                        {{ $active ? 'text-green-700 font-bold' : ($done ? 'text-green-600' : 'text-gray-400') }}">
                                {{ $info['label'] }}
                            </p>
                            {{-- Tooltip --}}
                            <div
                                class="absolute bottom-full mb-3 left-1/2 -translate-x-1/2 w-48 bg-gray-900 text-white text-xs rounded-lg px-3 py-2 leading-snug
                        opacity-0 group-hover/step:opacity-100 pointer-events-none transition-opacity duration-200 z-20 shadow-xl text-center">
                                {{ $info['tooltip'] }}
                                <div
                                    class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ===================== MAP ===================== --}}
        <section class="max-w-7xl mx-auto px-6 py-10">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div id="map" class="h-[420px] w-full"></div>
                <div class="px-6 py-4 text-sm text-gray-600">
                    📍 {{ $location['province'] ?? 'Lokasi tidak diketahui' }} —
                    {{ $location['district'] ?? '' }}
                    {{ $location['village'] ? '— ' . $location['village'] : '' }}
                </div>
            </div>
        </section>

        {{-- ===================== INFO GRID ===================== --}}
        <section class="max-w-6xl mx-auto px-6">
            @php
                $info = [
                    [
                        'Kategori',
                        $categories
                            ->map(function ($cat) {
                                $t =
                                    $cat->translations->firstWhere('locale', app()->getLocale()) ??
                                    $cat->translations->first();
                                return $t?->name ?? '-';
                            })
                            ->join(', '),
                    ],
                    ['Provinsi', $location['province'] ?? '-'],
                    ['Kab/Kota', $location['district'] ?? '-'],
                    ['Desa/Kelurahan', $location['village'] ?? '-'],
                ];
            @endphp
            <div class="grid md:grid-cols-4 gap-4">
                @foreach ($info as [$label, $value])
                    <div class="bg-white rounded-xl p-5 shadow-sm">
                        <p class="text-xs text-gray-400 uppercase tracking-wide">{{ $label }}</p>
                        <p class="mt-2 font-semibold text-gray-800">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ===================== MAIN CONTENT ===================== --}}
        <section class="max-w-7xl mx-auto px-6 py-12">
            {{-- Tab Switcher --}}
            <div class="max-w-4xl mx-auto">

                {{-- Tab Buttons --}}
                <div class="overflow-x-auto -mx-6 px-6 mb-6 scrollbar-hide">
                    <div class="flex gap-1 bg-gray-100 rounded-xl p-1 min-w-max md:min-w-0">
                        <button onclick="switchTab('kronologi')" id="tab-kronologi"
                            class="tab-btn whitespace-nowrap flex-1 py-2 px-3 rounded-lg text-sm font-semibold transition-all duration-200 bg-white text-gray-800 shadow-sm">
                            Kronologi
                        </button>
                        <button onclick="switchTab('perkembangan')" id="tab-perkembangan"
                            class="tab-btn whitespace-nowrap flex-1 py-2 px-3 rounded-lg text-sm font-semibold transition-all duration-200 text-gray-500 hover:text-gray-700">
                            Perkembangan
                        </button>
                        <button onclick="switchTab('permasalahan')" id="tab-permasalahan"
                            class="tab-btn whitespace-nowrap flex-1 py-2 px-3 rounded-lg text-sm font-semibold transition-all duration-200 text-gray-500 hover:text-gray-700">
                            Permasalahan
                        </button>
                        <button onclick="switchTab('pembelajaran')" id="tab-pembelajaran"
                            class="tab-btn whitespace-nowrap flex-1 py-2 px-3 rounded-lg text-sm font-semibold transition-all duration-200 text-gray-500 hover:text-gray-700">
                            Lesson Learn
                        </button>
                        <button onclick="switchTab('status')" id="tab-status"
                            class="tab-btn whitespace-nowrap flex-1 py-2 px-3 rounded-lg text-sm font-semibold transition-all duration-200 text-gray-500 hover:text-gray-700">
                            Status
                        </button>
                    </div>
                </div>

                {{-- Panel: Kronologi --}}
                <div id="panel-kronologi" class="tab-panel">
                    @if ($trans?->description)
                        <div class="prose prose-gray max-w-none">{!! $trans->description !!}</div>
                    @else
                        <p class="text-gray-400 italic">Kronologi belum tersedia.</p>
                    @endif
                </div>

                {{-- Panel: Perkembangan --}}
                <div id="panel-perkembangan" class="tab-panel hidden">
                    @php
                        $perkembangan = $trans->perkembangan ?? null;
                        $perkembanganArr = null;
                        if (is_string($perkembangan)) {
                            $json = json_decode($perkembangan, true);
                            if (is_array($json)) {
                                $perkembanganArr = $json;
                            }
                        }
                    @endphp
                    @if ($perkembanganArr)
                        <div class="space-y-6 border-l-2 border-green-600 pl-6">
                            @forelse ($perkembanganArr as $entry)
                                <div class="relative">
                                    <span class="absolute -left-[11px] top-1 w-4 h-4 bg-green-600 rounded-full"></span>
                                    <div class="ml-5">
                                        <h3 class="font-semibold text-gray-800">
                                            {{ $entry['title'] ?? 'Perkembangan Kasus' }}</h3>
                                        <p class="text-sm text-gray-600 mt-1">{{ $entry['notes'] ?? '' }}</p>
                                        @if (!empty($entry['created_at']))
                                            <p class="text-xs text-gray-400 mt-2">
                                                {{ \Carbon\Carbon::parse($entry['created_at'])->format('d M Y H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 italic">Belum ada perkembangan.</p>
                            @endforelse
                        </div>
                    @elseif(!empty($perkembangan))
                        <div class="prose prose-gray max-w-none">{!! $perkembangan !!}</div>
                    @else
                        <p class="text-gray-500 italic">Belum ada perkembangan.</p>
                    @endif
                </div>

                {{-- Panel: permasalahan --}}
                <div id="panel-permasalahan" class="tab-panel hidden">
                    @if ($trans?->dugaan_permasalahan)
                        <div class="prose-content text-gray-700 leading-relaxed">
                            {!! $trans->dugaan_permasalahan !!}
                        </div>
                    @else
                        <p class="text-gray-400 italic">Dugaan permasalahan belum tersedia.</p>
                    @endif
                </div>

                {{-- Panel: Pembelajaran --}}
                <div id="panel-pembelajaran" class="tab-panel hidden">
                    @if ($trans?->pembelajaran)
                        <div class="prose-content text-gray-700 leading-relaxed">
                            {!! $trans->pembelajaran !!}
                        </div>
                    @else
                        <p class="text-gray-400 italic">Pembelajaran belum tersedia.</p>
                    @endif
                </div>

                {{-- Panel: Status narasi --}}
                <div id="panel-status" class="tab-panel hidden">
                    @if ($case->status_narasi)
                        <div class="prose-content text-gray-700 leading-relaxed">
                            {!! $case->status_narasi !!}
                        </div>
                    @else
                        <p class="text-gray-400 italic">Pembelajaran belum tersedia.</p>
                    @endif
                </div>
            </div>
        </section>

        {{-- ===================== ARTIKEL TERKAIT ===================== --}}
        <section class="max-w-7xl mx-auto px-6 pb-12">
            <h2 class="text-2xl font-bold mb-6">Artikel Terkait</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                @foreach ($artikels as $c)
                    <div
                        class="group bg-white border border-gray-200 border-t-4 border-t-gray-900 rounded-sm flex flex-col hover:shadow-[4px_4px_0_#111] hover:-translate-x-0.5 hover:-translate-y-0.5 transition-all duration-200">

                        {{-- Image --}}
                        <div class="relative w-full aspect-video overflow-hidden">
                            <img src="{{ asset('storage/' . $c->image) }}" alt="{{ strip_tags($c->title) }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>

                            {{-- Badge Artikel --}}
                            <span
                                class="absolute top-3 left-3 px-2 py-0.5 text-xs font-bold tracking-widest uppercase bg-gray-900 text-white">
                                📰 Artikel
                            </span>

                            {{-- Category Badge --}}
                            @if ($c->category_name)
                                <span
                                    class="absolute top-3 right-3 px-2 py-0.5 text-xs font-bold tracking-widest uppercase border border-white text-white bg-black/30 backdrop-blur-sm">
                                    {{ $c->category_name }}
                                </span>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="p-5 flex flex-col flex-1">
                            {{-- Title --}}
                            <h3 class="text-lg font-black text-gray-900 leading-tight mb-3 tracking-tight line-clamp-2">
                                {!! Str::limit(strip_tags($c->title), 80) !!}
                            </h3>

                            {{-- Excerpt --}}
                            <p class="text-sm text-gray-500 leading-relaxed italic flex-1 mb-4 line-clamp-3">
                                {!! Str::limit(strip_tags($c->excerpt), 120) !!}
                            </p>

                            {{-- Footer --}}
                            <div class="pt-3 border-t border-gray-100">
                                @if ($c->type === 'internal')
                                    <a href="{{ route('public.artikel.detail', ['slug' => $c->slug]) }}"
                                        class="text-xs font-bold uppercase tracking-widest text-[#032A36] hover:text-[#264c16] transition-colors after:content-['_→']">
                                        Baca Selengkapnya
                                    </a>
                                @else
                                    <a href="{{ $c->link }}" target="_blank" rel="noopener"
                                        class="text-xs font-bold uppercase tracking-widest text-[#032A36] hover:text-[#264c16] transition-colors after:content-['_→']">
                                        Baca Selengkapnya
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- ===================== KASUS TERKAIT ===================== --}}
        @if (isset($relatedCases) && $relatedCases->count())
            <section class="max-w-7xl mx-auto px-6 pb-16">
                <div class="border-t pt-10">
                    <h2 class="text-2xl font-bold mb-6">Kasus Terkait</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach ($relatedCases as $related)
                            @php
                                $relatedTrans =
                                    $related->translations->firstWhere('locale', app()->getLocale()) ??
                                    $related->translations->first();
                            @endphp
                            <a href="{{ route('public.case.detail', ['slug' => $related->slug]) }}"
                                class="group bg-white rounded-xl shadow-sm p-5 hover:shadow-md hover:-translate-y-1 transition-all duration-200 border border-gray-100">
                                <div class="flex items-center justify-between mb-3">
                                    <span
                                        class="text-[10px] font-semibold uppercase tracking-widest px-2.5 py-1 rounded-full
                            {{ $related->status?->key === 'investigation' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $related->status->name ?? '-' }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $related->case_number }}</span>
                                </div>
                                <h3
                                    class="font-bold text-gray-800 text-sm leading-snug group-hover:text-green-700 transition-colors line-clamp-2">
                                    {{ $relatedTrans?->title ?? 'Tanpa judul' }}
                                </h3>
                                <p class="mt-2 text-xs text-gray-500 line-clamp-2">
                                    {{ Str::limit(strip_tags($relatedTrans?->summary ?? ''), 100) }}
                                </p>
                                <p class="mt-3 text-[10px] text-gray-400">📍 {{ $related->location['province'] ?? '-' }}
                                </p>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

    </div>

    <style>
        /* Custom prose styling for TinyMCE content */
        .prose-content p {
            margin-bottom: 1rem;
        }

        .prose-content p:last-child {
            margin-bottom: 0;
        }

        .prose-content h1,
        .prose-content h2,
        .prose-content h3,
        .prose-content h4 {
            font-weight: 700;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }

        .prose-content h1 {
            font-size: 1.875rem;
        }

        .prose-content h2 {
            font-size: 1.5rem;
        }

        .prose-content h3 {
            font-size: 1.25rem;
        }

        .prose-content ul,
        .prose-content ol {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }

        .prose-content ul {
            list-style-type: disc;
        }

        .prose-content ol {
            list-style-type: decimal;
        }

        .prose-content li {
            margin-bottom: 0.5rem;
        }

        .prose-content a {
            color: #2563eb;
            text-decoration: underline;
        }

        .prose-content strong {
            font-weight: 700;
        }

        .prose-content em {
            font-style: italic;
        }
    </style>

@endsection

@push('scripts')
    <script>
        // ===== Map =====
        document.addEventListener('DOMContentLoaded', function() {
            const lat = {{ $case->latitude ?? 'null' }};
            const lng = {{ $case->longitude ?? 'null' }};
            if (!lat || !lng) return;
            const map = L.map('map', {
                scrollWheelZoom: false
            }).setView([lat, lng], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            L.marker([lat, lng]).addTo(map);
        });

        // ===== Tab Switcher =====
        function switchTab(name) {
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('bg-white', 'text-gray-800', 'shadow-sm');
                b.classList.add('text-gray-500');
            });
            document.getElementById('panel-' + name).classList.remove('hidden');
            const activeBtn = document.getElementById('tab-' + name);
            activeBtn.classList.add('bg-white', 'text-gray-800', 'shadow-sm');
            activeBtn.classList.remove('text-gray-500');
        }

        // ===== FAQ Accordion =====
        function toggleFaq(btn) {
            const body = btn.nextElementSibling;
            const icon = btn.querySelector('.faq-icon');
            const isOpen = !body.classList.contains('hidden');
            // Close all
            document.querySelectorAll('.faq-body').forEach(b => b.classList.add('hidden'));
            document.querySelectorAll('.faq-icon').forEach(i => i.classList.remove('rotate-180'));
            // Open clicked if it was closed
            if (!isOpen) {
                body.classList.remove('hidden');
                icon.classList.add('rotate-180');
            }
        }

        // ===== Copy Link =====
        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                const btn = document.getElementById('copyBtn');
                const label = document.getElementById('copyLabel');
                label.textContent = 'Tersalin!';
                btn.classList.add('bg-green-50', 'text-green-700', 'border-green-200');
                btn.classList.remove('bg-gray-50', 'text-gray-700', 'border-gray-200');
                setTimeout(() => {
                    label.textContent = 'Salin Link';
                    btn.classList.remove('bg-green-50', 'text-green-700', 'border-green-200');
                    btn.classList.add('bg-gray-50', 'text-gray-700', 'border-gray-200');
                }, 2000);
            });
        }
    </script>
@endpush
