<div class="max-w-7xl mx-auto px-6 py-6 space-y-4 cms-rise">
    <div class="flex items-center justify-between border-b border-[color:var(--hairline)] pb-3">
        <div>
            <div class="cms-eyebrow">VERIFICATION</div>
            <h1 class="text-xl font-semibold text-[color:var(--ink)] tracking-tight mt-0.5">Rejected Cases</h1>
        </div>
    </div>

    <div class="cms-panel">
        <div class="overflow-x-auto">
            <table class="cms-table w-full text-sm">
                <thead>
                    <tr>
                        <th>Case Number</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Event Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cases as $c)
                    <tr>
                        <td class="num">{{ $c->case_number }}</td>
                        <td class="text-sm text-[color:var(--muted)]">{{ optional($c->translations->firstWhere('locale', app()->getLocale()))->title ?? '-' }}</td>
                        <td class="text-sm text-[color:var(--muted)]">{{ $c->status->name ?? '-' }}</td>
                        <td class="text-sm text-[color:var(--muted)]">{{ optional($c->event_date)->format('Y-m-d') ?? '-' }}</td>
                        <td>
                            <a href="{{ route('case.detail', $c->id) }}" class="link text-sm font-medium">Open</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td class="py-12 text-center text-sm text-[color:var(--muted)]" colspan="5">No rejected cases found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 border-t border-[color:var(--hairline)]">
            {{ $cases->links() }}
        </div>
    </div>
</div>