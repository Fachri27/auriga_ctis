<div class="p-4">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold">Pending Review</h2>
    </div>

    <div class="bg-white shadow rounded-md overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-2">Case Number</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Event Date</th>
                    <th class="px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cases as $c)
                <tr class="border-t hover:bg-slate-50">
                    <td class="px-4 py-2">{{ $c->case_number }}</td>
                    <td class="px-4 py-2">{{ optional($c->translations->firstWhere('locale', app()->getLocale()))->title
                        ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $c->status->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ optional($c->event_date)->format('Y-m-d') ?? '-' }}</td>
                    <td class="px-4 py-2">
                        <a href="{{ route('case.detail', $c->id) }}" class="text-blue-600 hover:underline">Open</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td class="px-4 py-6 text-center" colspan="5">No cases pending review.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4">
            {{ $cases->links() }}
        </div>
    </div>
</div>