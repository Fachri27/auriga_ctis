<div class="max-w-4xl mx-auto py-16 px-6">

    <!-- HEADER -->
    <div class="text-center mb-10">
        <h2 class="text-2xl font-semibold text-gray-900">
            Status Laporan
        </h2>
        <p class="text-gray-600 text-sm mt-1">
            Distribusi penanganan laporan saat ini
        </p>
    </div>

    <!-- STATUS LIST -->
    <div class="bg-white rounded-2xl shadow-sm border p-8 space-y-6">

        @foreach($status as $stat)

            @php
                $percentage = $totalCases > 0 
                    ? round(($stat->case_count / $totalCases) * 100, 1)
                    : 0;
            @endphp

            <div>
                <div class="flex justify-between text-sm font-medium mb-2">
                    <span class="text-gray-800">
                        {{ $stat->name }}
                    </span>

                    <span class="text-gray-500">
                        {{ $stat->total }} laporan ({{ $percentage }}%)
                    </span>
                </div>

                <div class="w-full bg-gray-200 h-3 rounded-full overflow-hidden">
                    <div
                        class="bg-black h-3 rounded-full transition-all duration-500"
                        style="width: {{ $percentage }}%">
                    </div>
                </div>
            </div>

        @endforeach

    </div>

</div>