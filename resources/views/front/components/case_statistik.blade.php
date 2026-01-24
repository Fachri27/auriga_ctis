<div class="w-full bg-gradient-to-br from-[#00323C] to-[#014a59] py-20 px-4 md:mb-10 mb-5">
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- SUMMARY -->
            <div class="lg:col-span-1 bg-white/10 backdrop-blur-lg rounded-2xl p-6 text-white shadow-xl">
                <h3 class="text-sm uppercase tracking-widest text-white/70">
                    Case Overview
                </h3>

                <div class="mt-6">
                    <p class="text-5xl font-bold">
                        {{ array_sum($caseCounts) }}
                    </p>
                    <p class="text-white/70 mt-1">
                        Total Cases Recorded
                    </p>
                </div>

                <div class="mt-8 text-sm text-white/80 leading-relaxed">
                    Distribution of cases across different categories.  
                    This visualization helps identify dominant trends.
                </div>
            </div>

            <!-- CHART -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    Cases per Category
                </h3>

                <div class="relative h-[320px]">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

        </div>
    </div>
</div>
@push('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('categoryChart');

    const labels = @json($casesByCategory);
    const dataValues = @json($caseCounts);

    const colors = [
        '#00323C',
        '#005B6A',
        '#008A9C',
        '#00B3C4',
        '#7ADFEA'
    ];

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cases',
                data: dataValues,
                backgroundColor: colors.slice(0, labels.length),
                borderRadius: 10,
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#00323C',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 12,
                    cornerRadius: 8
                }
            },
            scales: {
                x: {
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    grid: {
                        color: '#e5e7eb'
                    }
                }
            },
            animation: {
                duration: 1200,
                easing: 'easeOutQuart'
            }
        }
    });
});
</script>
@endpush
