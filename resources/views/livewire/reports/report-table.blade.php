<div class="max-w-7xl mx-auto px-6 py-6 space-y-4 cms-rise" style="animation-delay:.04s">

    {{-- HEADER --}}
    <div class="border-b border-[color:var(--hairline)] pb-3 flex items-center justify-between">
        <div>
            <div class="cms-eyebrow">REPORTS</div>
            <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Reports</h1>
            <div class="cms-panel-sub">Incoming reports awaiting verification and processing</div>
        </div>
    </div>

    {{-- REPORT LIST --}}
    @if($reports->count())
    <div class="space-y-3">
        @foreach ($reports as $r)
        <div wire:key="report-{{ $r->id }}"
            class="cms-panel flex flex-col md:flex-row md:justify-between md:items-center gap-4 cms-rise"
            style="animation-delay:.04s">

            <div class="cms-panel-body" style="padding:16px 20px; flex:1; width:100%">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    {{-- LEFT --}}
                    <div class="space-y-2">
                        {{-- Report Code --}}
                        <div class="flex items-center gap-3">
                            <span class="font-semibold text-[color:var(--ink)] font-mono-c text-base">
                                #{{ $r->report_code }}
                            </span>

                            {{-- STATUS --}}
                            @if($r->status)
                            @php
                                $variant = match($r->status->key) {
                                    'open' => 'warn',
                                    'verified' => 'info',
                                    'converted' => 'ok',
                                    'rejected' => 'danger',
                                    default => 'default',
                                };
                            @endphp
                            <x-internal.badge variant="{{ $variant }}">{{ ucfirst($r->status->name) }}</x-internal.badge>
                            @endif
                        </div>

                        {{-- DESCRIPTION --}}
                        <p class="text-sm text-[color:var(--ink-2)] max-w-2xl line-clamp-2">
                            {{ strip_tags(optional($r->translations->where('locale','id')->first())->description ?? 'No description provided by reporter.') }}
                        </p>

                        {{-- META --}}
                        <div class="text-xs text-[color:var(--muted)] flex gap-4">
                            <span>{{ $r->created_at->format('d M Y') }}</span>
                            <span class="font-mono-c">Report ID: {{ $r->id }}</span>
                        </div>
                    </div>

                    {{-- RIGHT ACTION --}}
                    <div class="flex gap-2 shrink-0">
                        @can('report.view')
                        <a href="{{ route('reports.detail', $r->id) }}"
                            class="cms-btn cms-btn-ghost">
                            View
                        </a>
                        @endcan

                        {{-- OPTIONAL QUICK ACTION --}}
                        {{--
                        <button class="cms-btn cms-btn-primary">
                            Verify
                        </button>
                        --}}
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @else
    {{-- EMPTY STATE --}}
    <div class="py-12 text-center text-sm text-[color:var(--muted)]">
        <svg class="w-10 h-10 mx-auto mb-3 text-[color:var(--hairline-2)]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
        <p class="font-medium text-[color:var(--ink)]">No reports available</p>
        <p class="mt-1">New reports will appear here once submitted by users.</p>
    </div>
    @endif

    <div class="mt-4">
        {{ $reports->links() }}
    </div>
</div>