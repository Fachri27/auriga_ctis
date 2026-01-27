<div>
    <div class="mx-10 py-10 space-y-6">

        {{-- HEADER --}}
        <div>
            <h1 class="text-2xl font-bold">Reports</h1>
            <p class="text-sm text-gray-500">
                Incoming reports awaiting verification and processing
            </p>
        </div>

        {{-- REPORT LIST --}}
        @if($reports->count())
        <div class="space-y-4">
            @foreach ($reports as $r)
            <div
                class="bg-white border rounded-xl p-5 flex flex-col md:flex-row md:justify-between md:items-center gap-4 hover:shadow-sm transition">

                {{-- LEFT --}}
                <div class="space-y-2">
                    {{-- Report Code --}}
                    <div class="flex items-center gap-3">
                        <span class="font-semibold text-lg">
                            #{{ $r->report_code }}
                        </span>

                        {{-- STATUS --}}
                        @if($r->status)
                        <span class="px-3 py-1 text-xs rounded-full
                            {{ $r->status->key === 'open' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $r->status->key === 'verified' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $r->status->key === 'converted' ? 'bg-green-100 text-green-700' : '' }}
                        ">
                            {{ ucfirst($r->status->name) }}
                        </span>
                        @endif
                    </div>

                    {{-- DESCRIPTION --}}
                    <p class="text-gray-700 text-sm max-w-2xl line-clamp-2">
                        {!!
                            optional(
                                $r->translations->where('locale','id')->first()
                            )->description
                            ?? 'No description provided by reporter.'
                        !!}
                    </p>

                    {{-- META --}}
                    <div class="text-xs text-gray-500 flex gap-4">
                        <span>
                            📅 {{ $r->created_at->format('d M Y') }}
                        </span>

                        <span>
                            🆔 Report ID: {{ $r->id }}
                        </span>
                    </div>
                </div>

                {{-- RIGHT ACTION --}}
                <div class="flex gap-3 shrink-0">
                    <a href="{{ route('reports.detail', $r->id) }}"
                        class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">
                        View
                    </a>

                    {{-- OPTIONAL QUICK ACTION --}}
                    {{-- 
                    <button class="px-4 py-2 text-sm bg-black text-white rounded-lg">
                        Verify
                    </button>
                    --}}
                </div>

            </div>
            @endforeach
        </div>

        @else
        {{-- EMPTY STATE --}}
        <div class="bg-white border rounded-xl p-12 text-center text-gray-500">
            <div class="text-4xl mb-3">📭</div>
            <p class="font-semibold">No reports available</p>
            <p class="text-sm mt-1">
                New reports will appear here once submitted by users.
            </p>
        </div>
        @endif

    </div>
</div>
