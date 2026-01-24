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
<div class="w-full bg-[#00323C] py-20 px-4 md:mb-10 mb-5 poppins-regular">
    {{-- <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">

        <!-- IMAGE -->
        <div class="w-full h-[250px] sm:h-[320px] lg:h-[360px] bg-gray-500 overflow-hidden">
            <img src="" alt="image" class="w-full h-full object-cover">
        </div>

        <!-- TEXT CONTENT -->
        <div class="text-white space-y-6">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-semibold leading-tight">
                {{ $case->title ?? "Case Title" }}
            </h1>

            <p class="text-base sm:text-lg leading-relaxed tracking-wide md:text-left text-justify">
                {{$case->description ?? "Case Description"}}
            </p>

            <div class="uppercase tracking-wider font-semibold cursor-pointer hover:underline">
                view ->
            </div>

        </div>
    </div> --}}

    {{-- chart case --}}
    {{-- <div class="max-w-7xl mx-auto bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Cases per Category</h3>
            <div style="position: relative; height: 300px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div> --}}
    <div class="max-w-7xl mx-auto bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-6">
            {{-- <h3 class="text-lg font-semibold text-gray-900 mb-4">Cases per Category</h3> --}}
            <div id="container" style="height: 450px;"></div>
        </div>
    </div>
</div>
{{-- @include('front.components.dokumentasi') --}}
{{-- @include('front.components.footer') --}}

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
        const colorMap = {
            Fraud: '#dc2626',
            Abuse: '#2563eb',
            Corruption: '#7c3aed',
            Environment: '#16a34a',
        };
        // console.log(categories);
        // const categoryCtx = document.getElementById('container').getContext('2d');
        Highcharts.chart('container', {
            chart: {
                type: 'bar',
                animation: true,
                scrollablePlotArea: {
            minHeight: 600,   // ⬅️ makin besar → bisa scroll
            scrollPositionY: 0
        }
            },
            title: {
                text: 'Cases per Sector'
            },
            xAxis: {
                categories: categories.map(item => item.category_name),
                title: {
                    text: 'Category'
                },
                // crosshair: true,
                min: 0
                // max: 4,
                // scrollbar: {
                //     enabled: true
                // },
                // ticklength: 0
            },
            yAxis: {
                min: 0,
                // max: 1200,
                title: {
                    text: 'Number of Cases'
                },
                allowDecimals: false
            },
            legend: {
                enabled: false
            },
            tooltip: {
                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                    '<td style="padding:0"><b>{point.y}</b></td></tr>',
                footerFormat: '</table>',
                shared: true,
                useHTML: true
            },
            responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        legend: {
                            layout: 'horizontal',
                            align: 'center',
                            verticalAlign: 'bottom'
                        }
                    }
                }]
            },
            series: [{
                name: 'Cases',
                data: categories.map(item => ({
                    name: item.category_name,
                    y: item.count,
                    color: colorMap[item.category_name] || '#0f766e'
                }))
            }],
            credits: {
                enabled: false
            }
        });
    });
</script>

@endpush