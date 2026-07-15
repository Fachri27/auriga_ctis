@extends('layouts.main')

@section('content')
<div id="verify-case-page" class="mt-16 bg-[#F5F7F1]">
    {{-- Hero band --}}
    <section class="bg-[#0B1E07] console-grid text-white py-16 md:py-20">
        <div class="max-w-7xl mx-auto px-4">
            <p class="font-data uppercase tracking-[0.2em] text-xs text-[#9BDB4D] mb-3">VERIFIKASI KASUS</p>
            <h1 class="font-display text-3xl md:text-4xl font-semibold max-w-2xl mb-4">
                Hasil Verifikasi Kasus Publik
            </h1>
            <p class="text-white/80 max-w-2xl text-sm md:text-base">
                Data berikut adalah hasil pencocokan nomor kasus terhadap database kami. Pastikan detail yang ditampilkan sesuai dengan laporan yang Anda miliki.
            </p>
        </div>
    </section>

    {{-- Dossier card --}}
    <section class="bg-white py-12 md:py-16">
        <div class="max-w-4xl mx-auto px-4">
            <div class="bg-white border border-[#E2E6DA] rounded-sm p-6 md:p-8">
                <div class="mb-6">
                    <p class="text-[10px] font-data uppercase tracking-widest text-[#6b7268] mb-1">Nomor Kasus</p>
                    <p class="font-data text-lg md:text-xl text-[#0B1E07]">{{ $case->case_number }}</p>
                </div>

                <h2 class="font-display text-xl md:text-2xl font-semibold text-[#0B1E07] mb-6">
                    {{ optional($case->translations->firstWhere('locale', app()->getLocale()))->title ??
                    optional($case->translations->first())->title ?? '-' }}
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-t border-[#E2E6DA] pt-6 mb-6">
                    <div>
                        <p class="text-[10px] font-data uppercase tracking-widest text-[#6b7268] mb-1">Status</p>
                        <p class="text-sm md:text-base font-medium text-[#0B1E07]">
                            {{ $case->status?->name ?? ($case->status?->key ?? '-') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] font-data uppercase tracking-widest text-[#6b7268] mb-1">Tanggal Laporan</p>
                        <p class="text-sm md:text-base font-medium text-[#0B1E07]">
                            {{ optional($case->event_date)->toDateString() ??
                            optional($case->published_at)->toDateString() ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="border-t border-[#E2E6DA] pt-6 flex items-center justify-between">
                    <a href="{{ route('public.verify.case', $case->case_number) }}"
                       class="inline-flex items-center gap-2 text-sm font-semibold text-[#2F6C14] hover:text-[#9BDB4D] transition-colors group">
                        Lihat Detail Kasus
                        <span class="text-[#9BDB4D] group-hover:translate-x-0.5 transition-transform">→</span>
                    </a>
                </div>
            </div>

            {{-- Transparency banner --}}
            <div class="mt-6 bg-[#F5F7F1] border border-[#E2E6DA] rounded-sm p-5">
                <p class="text-sm text-[#6b7268]">
                    Informasi ini bersumber dari data publik yang telah melalui proses verifikasi. Jika Anda menemukan ketidaksesuaian, silakan hubungi tim kami.
                </p>
            </div>
        </div>
    </section>
</div>
@endsection
