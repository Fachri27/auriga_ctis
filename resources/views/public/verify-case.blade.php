@extends('layouts.main')

@section('content')
<div class="max-w-4xl mx-auto py-12 px-4">
    <div class="bg-white shadow rounded p-6">
        <h1 class="text-xl font-semibold mb-4">Verifikasi Case Publik</h1>

        <div class="mb-3 text-sm text-gray-600">Case number</div>
        <div class="mb-4 font-mono text-lg">{{ $case->case_number }}</div>

        <div class="mb-3 text-sm text-gray-600">Judul</div>
        <div class="mb-4 font-semibold">
            {{ optional($case->translations->firstWhere('locale', app()->getLocale()))->title ??
            optional($case->translations->first())->title ?? '-' }}
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm text-gray-700">
            <div>
                <div class="text-xs text-gray-500">Status</div>
                <div class="font-medium">{{ $case->status?->name ?? ($case->status?->key ?? '-') }}</div>
            </div>

            <div>
                <div class="text-xs text-gray-500">Tanggal Laporan</div>
                <div class="font-medium">{{ optional($case->event_date)->toDateString() ??
                    optional($case->published_at)->toDateString() ?? '-' }}</div>
            </div>
        </div>

    </div>
</div>
@endsection