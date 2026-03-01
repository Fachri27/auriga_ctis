@extends('layouts.main')

@section('content')
{{-- <div class="w-full bg-[#032A36] py-10 px-5 text-white" x-data="{}">

    <!-- MAP (Mobile & Desktop Responsive) -->
    <div class="flex justify-center mb-10">
        <img src="/img/map-indonesia.png" alt="Map" class="w-full max-w-4xl opacity-90">
    </div>

    <!-- FILTER SECTION -->
    <div class="max-w-4xl mx-auto">

        <!-- MOBILE TITLE (optional) -->
        <h2 class="text-center text-lg font-semibold mb-6 md:hidden tracking-wide">
            Cari Data Indonesia
        </h2>

        <!-- INPUTS GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

            <!-- Keyword -->
            <div>
                <label class="text-xs uppercase tracking-widest opacity-70 mb-1 block">Keyword</label>
                <input type="text"
                    class="w-full bg-white/10 border border-white/30 px-3 py-3 rounded-md focus:border-white/60 outline-none text-sm placeholder-white/50"
                    placeholder="Cari kata kunci...">
            </div>

            <!-- Sector -->
            <div>
                <label class="text-xs uppercase tracking-widest opacity-70 mb-1 block">Sector</label>
                <input type="text"
                    class="w-full bg-white/10 border border-white/30 px-3 py-3 rounded-md focus:border-white/60 outline-none text-sm placeholder-white/50"
                    placeholder="Contoh: Energi">
            </div>

            <!-- Status -->
            <div>
                <label class="text-xs uppercase tracking-widest opacity-70 mb-1 block">Status</label>
                <input type="text"
                    class="w-full bg-white/10 border border-white/30 px-3 py-3 rounded-md focus:border-white/60 outline-none text-sm placeholder-white/50"
                    placeholder="Aktif / Tidak aktif">
            </div>

            <!-- Location -->
            <div>
                <label class="text-xs uppercase tracking-widest opacity-70 mb-1 block">Location</label>
                <input type="text"
                    class="w-full bg-white/10 border border-white/30 px-3 py-3 rounded-md focus:border-white/60 outline-none text-sm placeholder-white/50"
                    placeholder="Nama kota / provinsi">
            </div>

            <!-- SEARCH BUTTON -->
            <div class="flex items-end">
                <button
                    class="w-full py-3 rounded-md border border-white/40 bg-white/10 hover:bg-white hover:text-[#00323C] transition font-semibold tracking-wide text-sm flex items-center justify-center gap-2">
                    <span>Cari</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                        viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 
                            3.85a1 1 0 0 0 
                            1.415-1.414l-3.85-3.85zm-5.242 
                            1.656a5 5 0 1 1 
                            0-10 5 5 0 0 1 0 10z" />
                    </svg>
                </button>
            </div>

        </div>
    </div>


</div> --}}
{{-- @dd(
auth()->user()->name,
auth()->user()->getAllPermissions()->pluck('name'),
auth()->user()->can('case.change-status'),
auth()->user()->can('case.update')
) --}}

@include('front.components.peta')
@include('front.components.card', ['limit' => 3, 'offset' => 0])
<div class="relative w-full bg-gradient-to-br from-[#002E36] via-[#003C45] to-[#002A31] py-28 text-white overflow-hidden">

    <!-- Soft Glow Effects -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-teal-400/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-emerald-400/10 rounded-full blur-3xl"></div>

    <div class="relative max-w-6xl mx-auto px-6 text-center">

        <!-- Small Label -->
        <div class="text-sm tracking-[0.3em] text-white/60 uppercase mb-6">
            {{ __('messages.public_transparency') }}
        </div>

        <!-- Title -->
        <h2 class="text-4xl md:text-5xl font-semibold leading-tight">
            {{ __('messages.transparency') }} <span class="text-teal-300">{{ __('messages.case_statistics') }}</span>
        </h2>

        <!-- Description -->
        <p class="mt-8 text-lg text-white/80 max-w-3xl mx-auto leading-relaxed">
            {{ __('messages.explore') }}
        </p>

        <!-- Decorative Divider -->
        <div class="mt-10 flex justify-center">
            <div class="w-24 h-[2px] bg-gradient-to-r from-transparent via-teal-400 to-transparent"></div>
        </div>

    </div>
</div>



<div class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-4">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

            <div class="bg-gray-50 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-6">
                    {{ __('messages.case_category') }}
                </h3>
                <div id="categoryChart" style="height: 400px;"></div>
            </div>

            <div class="bg-gray-50 rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-6">
                    {{ __('messages.case_status') }}
                </h3>
                <div id="statusChart" style="height: 400px;"></div>
            </div>

        </div>

        <div class="bg-gray-50 rounded-xl shadow-sm p-6 mt-10">
            <h3 class="text-lg font-semibold mb-6">
                {{ __('messages.case_trend') }}
            </h3>
            <div id="monthlyChart" style="height: 400px;"></div>
        </div>

    </div>
</div>

{{-- button form report --}}
<div class="bg-[#00323C] py-20 text-center border-b border-t border-white">
    <div class="max-w-3xl mx-auto px-4">
        <h2 class="text-3xl font-bold mb-6 text-white">
            {{ __('messages.submit_report_text') }}
        </h2>
        <p class="text-white/80 mb-8">
            {{ __('messages.help') }}
        </p>

        <a href="{{ route('report.form', app()->getLocale()) }}"
            class="bg-white text-[#00323C] px-8 py-3 rounded-lg font-semibold hover:bg-gray-200 transition">
            {{ __('messages.submit_report') }}
        </a>
    </div>
</div>


@endsection
@push('scripts')
<!-- Chart.js -->
{{-- <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script> --}}
<script src="https://code.highcharts.com/highcharts.js"></script>

{{-- <script>
    document.addEventListener('DOMContentLoaded', function () {
            // Data from backend
            const categories = @json($casesByCategory);
            // console.log(categories);
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            let delayed;

            // Create Chart
            new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: categories.map(item => item.category_name),
                    datasets: [{
                        label: 'Number of Cases',
                        data: categories.map(item => item.count),
                        backgroundColor: 'rgba(59, 130, 246, 0.6)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {  
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    animation: {
                        onComplete: () => {
                            delayed = true;
                        },
                        delay: (context) => {
                            let delay = 0;
                            if (context.type === 'data' && context.mode === 'default' && !delayed) {
                            delay = context.dataIndex * 300 + context.datasetIndex * 100;
                            }
                            return delay;
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        });         
</script> --}}

<script>
    document.addEventListener('DOMContentLoaded', function () {

    const categories = @json($casesByCategory);
    const statuses = @json($status);
    const months = @json($casesPerMonth);

    // ======================
    // CATEGORY CHART
    // ======================
    Highcharts.chart('categoryChart', {
        chart: { type: 'column' },
        title: { text: null },
        xAxis: {
            categories: categories.map(item => item.category_name)
        },
        yAxis: {
            min: 0,
            title: { text: 'Total Cases' },
            allowDecimals: false
        },
        legend: { enabled: false },
        series: [{
            name: 'Cases',
            data: categories.map(item => item.count)
        }],
        credits: { enabled: false }
    });

    // ======================
    // STATUS CHART
    // ======================
    Highcharts.chart('statusChart', {
        chart: { type: 'pie' },
        title: { text: null },
        series: [{
            name: 'Cases',
            data: statuses.map(item => ({
                name: item.status_name,
                y: item.count
            }))
        }],
        credits: { enabled: false }
    });

    // ======================
    // MONTHLY DEVELOPMENT
    // ======================
    Highcharts.chart('monthlyChart', {
        chart: { type: 'line' },
        title: { text: null },
        xAxis: {
            categories: months.map(item => item.month)
        },
        yAxis: {
            title: { text: 'Number of Cases' },
            allowDecimals: false
        },
        series: [{
            name: 'Cases',
            data: months.map(item => item.count)
        }],
        credits: { enabled: false }
    });

});
</script>




@endpush