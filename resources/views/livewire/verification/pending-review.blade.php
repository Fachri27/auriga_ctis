<div class="p-4">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold">Pending Review</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                <tr>
                    <th class="px-5 py-3.5 text-left font-semibold">Case Number</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Title</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Status</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Event Date</th>
                    <th class="px-5 py-3.5 text-left font-semibold">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($cases as $c)
                <tr class="hover:bg-gray-50/70 transition-colors">
                    <td class="px-5 py-4 font-semibold text-gray-900">{{ $c->case_number }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600">{{ optional($c->translations->firstWhere('locale', app()->getLocale()))->title ?? '-' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600">{{ $c->status->name ?? '-' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600">{{ optional($c->event_date)->format('Y-m-d') ?? '-' }}</td>
                    <td class="px-5 py-4 text-sm">
                        <a href="{{ route('case.detail', $c->id) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-900 transition-colors">Open</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td class="px-5 py-8 text-center text-sm text-gray-400" colspan="5">No cases pending review.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-5 py-4 border-t border-gray-100">
            {{ $cases->links() }}
        </div>
    </div>
</div>