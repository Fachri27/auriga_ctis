@extends('layouts.main')

@section('content')

@include('front.components.peta')
@include('front.components.card', ['limit' => 3, 'offset' => 0])

{{-- ===================== HERO STATS ===================== --}}
<div class="bg-white border-b border-gray-100 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        <div class="text-center mb-8">
            <p class="text-xs font-semibold tracking-[0.25em] uppercase text-gray-400 mb-1">Data Real-Time</p>
            <h2 class="text-2xl font-bold text-gray-800">Kasus dalam Angka</h2>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="text-center bg-gray-50 rounded-2xl p-5 border border-gray-100">
                <p class="text-3xl sm:text-4xl font-bold text-gray-900">{{ $totalCases ?? '—' }}</p>
                <p class="text-sm text-gray-500 mt-1">Total Kasus Terdaftar</p>
                <p class="text-xs text-gray-400 mt-1">Sejak sistem diluncurkan</p>
            </div>
            <div class="text-center bg-yellow-50 rounded-2xl p-5 border border-yellow-100">
                <p class="text-3xl sm:text-4xl font-bold text-yellow-600">{{ $activeCases ?? '—' }}</p>
                <p class="text-sm text-gray-500 mt-1">Kasus Aktif</p>
                <p class="text-xs text-gray-400 mt-1">Sedang dalam proses hukum</p>
            </div>
            <div class="text-center bg-green-50 rounded-2xl p-5 border border-green-100">
                <p class="text-3xl sm:text-4xl font-bold text-green-600">{{ $completedCases ?? '—' }}</p>
                <p class="text-sm text-gray-500 mt-1">Kasus Selesai / Divonis</p>
                <p class="text-xs text-gray-400 mt-1">Proses hukum sudah final</p>
            </div>
            <div class="text-center bg-blue-50 rounded-2xl p-5 border border-blue-100">
                <p class="text-3xl sm:text-4xl font-bold text-blue-600">{{ $provinceCovered ?? '—' }}</p>
                <p class="text-sm text-gray-500 mt-1">Provinsi Terdampak</p>
                <p class="text-xs text-gray-400 mt-1">Di seluruh Indonesia</p>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-5">
            🕐 Data terakhir diperbarui: <strong>{{ now()->format('d M Y, H:i') }} WIB</strong>
            &nbsp;·&nbsp; Sumber: KPK, Kejaksaan Agung, Kepolisian RI
        </p>
    </div>
</div>

{{-- ===================== ALUR PROSES HUKUM (EDUKASI) ===================== --}}
<div class="bg-[#002E36] py-16 sm:py-20 text-white overflow-hidden relative">
    <div class="absolute -top-20 -right-20 w-64 h-64 bg-teal-400/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-64 h-64 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative max-w-6xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-10">
            <p class="text-xs font-semibold tracking-[0.25em] uppercase text-teal-400 mb-2">Panduan untuk Masyarakat</p>
            <h2 class="text-2xl sm:text-3xl font-bold">Bagaimana Alur Proses Hukum?</h2>
            <p class="mt-3 text-white/60 text-sm max-w-xl mx-auto">
                Memahami tahapan ini membantu Anda mengerti mengapa sebuah kasus bisa memakan waktu lama.
            </p>
        </div>

        {{-- Steps: scrollable on mobile --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-0 overflow-x-auto pb-4 sm:pb-0">

            @php
            $steps = [
                ['icon' => '📩', 'title' => 'Laporan Masuk',   'desc' => 'Masyarakat atau lembaga melaporkan dugaan tindak pidana kepada penegak hukum.'],
                ['icon' => '✅', 'title' => 'Verifikasi',       'desc' => 'Laporan diperiksa kebenarannya. Jika valid, dilanjutkan ke tahap penyelidikan.'],
                ['icon' => '🔍', 'title' => 'Penyelidikan',     'desc' => 'Penyidik mengumpulkan bukti awal untuk menentukan ada tidaknya tindak pidana.'],
                ['icon' => '⚖️', 'title' => 'Persidangan',      'desc' => 'Terdakwa diadili di pengadilan. Hakim memeriksa fakta dan bukti-bukti.'],
                ['icon' => '🔨', 'title' => 'Putusan',          'desc' => 'Hakim menjatuhkan vonis. Jika bersalah, terpidana menjalani hukuman.'],
            ];
            @endphp

            @foreach($steps as $i => $s)
            <div class="flex sm:flex-col items-start sm:items-center sm:flex-1 gap-4 sm:gap-0 w-full sm:w-auto flex-shrink-0">
                {{-- Dot + connector --}}
                <div class="flex sm:flex-col items-center sm:w-full flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-white/10 border border-white/20 flex items-center justify-center text-xl flex-shrink-0">
                        {{ $s['icon'] }}
                    </div>
                    @if(!$loop->last)
                    {{-- Mobile: vertical line --}}
                    <div class="w-0.5 h-8 bg-white/20 sm:hidden ml-5 mt-1"></div>
                    {{-- Desktop: horizontal line --}}
                    <div class="hidden sm:block flex-1 h-0.5 bg-white/20 w-full mt-[-24px]"></div>
                    @endif
                </div>
                {{-- Text --}}
                <div class="sm:text-center sm:mt-3 sm:px-2 pb-4 sm:pb-0">
                    <p class="text-xs font-bold uppercase tracking-wider text-teal-300 mb-1">
                        {{ $loop->iteration }}. {{ $s['title'] }}
                    </p>
                    <p class="text-xs text-white/60 leading-relaxed max-w-[140px] sm:mx-auto">
                        {{ $s['desc'] }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-10 text-center">
            <a href="#" class="inline-flex items-center gap-2 text-sm text-teal-300 hover:text-white transition-colors font-medium">
                Pelajari lebih lanjut tentang proses hukum →
            </a>
        </div>
    </div>
</div>

{{-- ===================== STATISTIK + PENJELASAN ===================== --}}
<div class="relative w-full bg-gradient-to-br from-[#002E36] via-[#003C45] to-[#002A31] py-20 sm:py-28 text-white overflow-hidden">
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-teal-400/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 text-center">
        <div class="text-sm tracking-[0.3em] text-white/60 uppercase mb-6">
            {{ __('messages.public_transparency') }}
        </div>
        <h2 class="text-3xl sm:text-4xl md:text-5xl font-semibold leading-tight">
            {{ __('messages.transparency') }} <span class="text-teal-300">{{ __('messages.case_statistics') }}</span>
        </h2>
        <p class="mt-6 text-base sm:text-lg text-white/80 max-w-3xl mx-auto leading-relaxed">
            {{ __('messages.explore') }}
        </p>
        <div class="mt-8 flex justify-center">
            <div class="w-24 h-[2px] bg-gradient-to-r from-transparent via-teal-400 to-transparent"></div>
        </div>
    </div>
</div>

<div class="bg-white py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- Category Chart --}}
            <div class="bg-gray-50 rounded-2xl shadow-sm p-5 sm:p-6">
                <h3 class="text-base font-semibold mb-1">{{ __('messages.case_category') }}</h3>
                <p class="text-xs text-gray-400 mb-5" id="categoryInsight">Memuat data...</p>
                <div id="categoryChart" style="height: 380px;"></div>
            </div>

            {{-- Status Chart --}}
            <div class="bg-gray-50 rounded-2xl shadow-sm p-5 sm:p-6">
                <h3 class="text-base font-semibold mb-1">{{ __('messages.case_status') }}</h3>
                <p class="text-xs text-gray-400 mb-5" id="statusInsight">Memuat data...</p>
                <div id="statusChart" style="height: 380px;"></div>
            </div>

        </div>

        {{-- Monthly Chart --}}
        <div class="bg-gray-50 rounded-2xl shadow-sm p-5 sm:p-6 mt-8">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-1">
                <h3 class="text-base font-semibold">{{ __('messages.case_trend') }}</h3>
            </div>
            <p class="text-xs text-gray-400 mb-5" id="monthlyInsight">Memuat data...</p>
            <div id="monthlyChart" style="height: 380px;"></div>
        </div>

        {{-- Transparansi & Unduh --}}
        {{-- <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 flex items-start gap-3">
                <span class="text-2xl">📋</span>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Sumber Data</p>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">Data bersumber dari KPK, Kejaksaan Agung, dan Kepolisian RI yang diverifikasi tim kami.</p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 flex items-start gap-3">
                <span class="text-2xl">🔄</span>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Pembaruan Rutin</p>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">Data diperbarui secara berkala mengikuti perkembangan kasus dari lembaga resmi.</p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 flex items-start gap-3">
                <span class="text-2xl">📥</span>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Unduh Data</p>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">Tersedia untuk jurnalis dan peneliti.</p>
                    <a href="#" class="inline-block mt-2 text-xs text-blue-600 hover:underline font-medium">Unduh CSV →</a>
                </div>
            </div>
        </div> --}}

    </div>
</div>

{{-- ===================== FAQ AWAM ===================== --}}
<div class="bg-gray-50 py-16 border-t border-gray-100">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-10">
            <p class="text-xs font-semibold tracking-[0.25em] uppercase text-gray-400 mb-2">Pertanyaan Umum</p>
            <h2 class="text-2xl font-bold text-gray-800">Yang Sering Ditanyakan Masyarakat</h2>
        </div>

        <div class="space-y-3" id="faq-list">

            @php
            $faqs = [
                ['q' => '❓ Apa itu CTIS dan siapa yang mengelolanya?',
                 'a' => 'CTIS (Case Tracking & Information System) adalah platform publik untuk memantau perkembangan kasus hukum di Indonesia. Dikelola secara independen dengan data yang bersumber dari lembaga penegak hukum resmi.'],
                ['q' => '🔍 Bagaimana cara mencari kasus di daerah saya?',
                 'a' => 'Gunakan fitur filter di peta atau kolom pencarian — masukkan nama provinsi, kota, atau kata kunci. Semua kasus yang sudah dipublikasikan akan muncul di hasil pencarian.'],
                ['q' => '⏳ Mengapa proses hukum bisa memakan waktu bertahun-tahun?',
                 'a' => 'Setiap tahap hukum memiliki prosedur ketat. Kasus korupsi sering melibatkan dokumen ribuan lembar, banyak saksi, dan pihak-pihak yang menggunakan hak banding hingga kasasi. Ini yang membuat prosesnya panjang.'],
                ['q' => '📢 Bagaimana cara melaporkan dugaan korupsi?',
                 'a' => 'Anda bisa melapor melalui KPK (kpk.go.id), Kejaksaan, atau Kepolisian setempat. Identitas pelapor dilindungi oleh Undang-Undang Perlindungan Saksi dan Korban. Anda juga bisa menggunakan fitur laporan di platform ini.'],
                ['q' => '📊 Apakah data di sini akurat dan terpercaya?',
                 'a' => 'Data bersumber dari dokumen resmi lembaga penegak hukum dan putusan pengadilan yang telah berkekuatan hukum tetap. Kami memverifikasi setiap informasi sebelum dipublikasikan dan memperbarui secara berkala.'],
            ];
            @endphp

            @foreach($faqs as $faq)
            <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm">
                <button onclick="toggleFaqHome(this)"
                    class="w-full flex items-center justify-between px-5 py-4 text-left font-semibold text-gray-800 text-sm hover:bg-gray-50 transition-colors">
                    <span>{{ $faq['q'] }}</span>
                    <svg class="faq-icon w-4 h-4 text-gray-400 transition-transform duration-200 flex-shrink-0 ml-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="faq-body hidden px-5 pb-4 text-sm text-gray-600 leading-relaxed border-t border-gray-50">
                    <p class="pt-3">{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach

        </div>
    </div>
</div>

{{-- ===================== CTA SECTION ===================== --}}


@endsection

@push('scripts')
<script src="https://code.highcharts.com/highcharts.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const categories = @json($casesByCategory);
    const statuses   = @json($status);
    const months     = @json($casesPerMonth);

    // ========================
    // INSIGHT: Category
    // ========================
    if (categories.length) {
        const top = categories.reduce((a, b) => a.count > b.count ? a : b);
        const total = categories.reduce((sum, i) => sum + i.count, 0);
        const pct = Math.round((top.count / total) * 100);
        document.getElementById('categoryInsight').textContent =
            `"${top.category_name}" mendominasi dengan ${top.count} kasus (${pct}% dari total ${total} kasus terdaftar).`;
    }

    // ========================
    // INSIGHT: Status
    // ========================
    if (statuses.length) {
        const total = statuses.reduce((sum, i) => sum + i.count, 0);
        const active = statuses.filter(s =>
            ['investigation', 'penyelidikan', 'penyidikan', 'prosecution', 'trial'].includes(s.status_key)
        ).reduce((sum, i) => sum + i.count, 0);
        const activePct = Math.round((active / total) * 100);
        document.getElementById('statusInsight').textContent =
            `${activePct}% dari ${total} kasus sedang dalam proses hukum aktif.`;
    }

    // ========================
    // INSIGHT: Monthly
    // ========================
    if (months.length) {
        const latest = months[months.length - 1];
        const prev   = months.length > 1 ? months[months.length - 2] : null;
        let insight  = `Pada ${latest.month}, terdapat ${latest.count} kasus baru.`;
        if (prev) {
            const diff = latest.count - prev.count;
            if (diff > 0) insight += ` Naik ${diff} kasus dibanding bulan sebelumnya.`;
            else if (diff < 0) insight += ` Turun ${Math.abs(diff)} kasus dibanding bulan sebelumnya.`;
            else insight += ' Sama dengan bulan sebelumnya.';
        }
        document.getElementById('monthlyInsight').textContent = insight;
    }

    // ========================
    // CATEGORY CHART
    // ========================
    Highcharts.chart('categoryChart', {
        chart: { type: 'column', style: { fontFamily: 'inherit' } },
        title: { text: null },
        xAxis: {
            categories: categories.map(i => i.category_name),
            labels: { style: { fontSize: '11px' } }
        },
        yAxis: {
            min: 0,
            title: { text: 'Jumlah Kasus' },
            allowDecimals: false
        },
        tooltip: {
            formatter: function() {
                return `<b>${this.x}</b><br/>Jumlah kasus: <b>${this.y}</b>`;
            }
        },
        legend: { enabled: false },
        series: [{
            name: 'Kasus',
            data: categories.map(i => i.count),
            color: '#0d9488'
        }],
        credits: { enabled: false },
        plotOptions: {
            column: { borderRadius: 4 }
        }
    });

    // ========================
    // STATUS CHART
    // ========================
    Highcharts.chart('statusChart', {
        chart: { type: 'pie', style: { fontFamily: 'inherit' } },
        title: { text: null },
        tooltip: {
            formatter: function() {
                return `<b>${this.point.name}</b><br/>
                    ${this.y} kasus (${Math.round(this.percentage)}%)`;
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b><br>{point.percentage:.0f}%',
                    style: { fontSize: '11px', fontWeight: 'normal' }
                }
            }
        },
        series: [{
            name: 'Kasus',
            data: statuses.map(i => ({ name: i.status_name, y: i.count }))
        }],
        credits: { enabled: false }
    });

    // ========================
    // MONTHLY CHART
    // ========================
    Highcharts.chart('monthlyChart', {
        chart: { type: 'area', style: { fontFamily: 'inherit' } },
        title: { text: null },
        xAxis: {
            categories: months.map(i => i.month),
            labels: { style: { fontSize: '11px' } }
        },
        yAxis: {
            title: { text: 'Jumlah Kasus' },
            allowDecimals: false
        },
        tooltip: {
            formatter: function() {
                return `<b>${this.x}</b><br/>Kasus baru: <b>${this.y}</b>`;
            }
        },
        series: [{
            name: 'Kasus Baru',
            data: months.map(i => i.count),
            color: '#0d9488',
            fillColor: {
                linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                stops: [
                    [0, 'rgba(13,148,136,0.25)'],
                    [1, 'rgba(13,148,136,0)']
                ]
            },
            lineWidth: 2,
            marker: { radius: 4 }
        }],
        credits: { enabled: false }
    });

});

// ========================
// FAQ Accordion
// ========================
function toggleFaqHome(btn) {
    const body = btn.nextElementSibling;
    const icon = btn.querySelector('.faq-icon');
    const isOpen = !body.classList.contains('hidden');

    document.querySelectorAll('.faq-body').forEach(b => b.classList.add('hidden'));
    document.querySelectorAll('.faq-icon').forEach(i => i.classList.remove('rotate-180'));

    if (!isOpen) {
        body.classList.remove('hidden');
        icon.classList.add('rotate-180');
    }
}
</script>
@endpush