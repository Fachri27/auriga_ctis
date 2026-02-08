@php
$breadcrumbs = [
['label' => 'Dashboard', 'url' => route('dashboard')]
];
$pageTitle = 'Dashboard Overview';
$pageSubtitle = 'Case Tracking Information System - Internal Dashboard';
@endphp

<x-internal-layout>

    {{-- ================= SUMMARY CARDS ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 max-w-7xl mx-auto mt-10">
        {{-- Total Cases Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Cases</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($totalCases) }}</p>
                        <p class="mt-1 text-xs text-gray-500">All cases in system</p>
                    </div>
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- New Reports Today Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">New Reports Today</p>
                        <p class="mt-2 text-3xl font-bold text-green-600">{{ number_format($newReportsToday) }}</p>
                        <p class="mt-1 text-xs text-gray-500">Created today</p>
                    </div>
                    <div class="w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Published Cases Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Published</p>
                        <p class="mt-2 text-3xl font-bold text-purple-600">{{ number_format($publishedCases) }}</p>
                        <p class="mt-1 text-xs text-gray-500">Public cases</p>
                    </div>
                    <div class="w-14 h-14 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Unpublished Cases Card --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Unpublished</p>
                        <p class="mt-2 text-3xl font-bold text-orange-600">{{ number_format($unpublishedCases) }}</p>
                        <p class="mt-1 text-xs text-gray-500">Private cases</p>
                    </div>
                    <div class="w-14 h-14 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= CHARTS SECTION ================= --}}
    {{-- <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8"> --}}
        {{-- Bar Chart: Cases per Category --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 max-w-7xl mx-auto">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Cases per Category</h3>
                <div style="position: relative; height: 300px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Pie Chart: Case Status Distribution --}}
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 max-w-7xl mx-auto">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Case Status Distribution</h3>
                <div style="position: relative; height: 300px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        {{--
    </div> --}}

    {{-- Line Chart: Reports Over Time --}}
    {{-- <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8"> --}}
        <div class="p-6 max-w-7xl mx-auto">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Reports Over Time (Last 30 Days)</h3>
            <div style="position: relative; height: 300px;">
                <canvas id="timeChart"></canvas>
            </div>
        </div>
        {{--
    </div> --}}

    {{-- ================= MAP SECTION ================= --}}
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8 max-w-7xl mx-auto">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Case Locations</h3>
            <p class="text-sm text-gray-500 mt-1">All cases with location data (published and unpublished)</p>
        </div>
        <div id="caseMap" class="w-full" style="height: 500px;"></div>
    </div>

    {{-- ================= LATEST CASES TABLE ================= --}}
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 max-w-7xl mx-auto mb-20">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Latest Cases</h3>
            <p class="text-sm text-gray-500 mt-1">Most recently created cases</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Case
                            Number</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Category</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event
                            Date</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Public?</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($latestCases as $case)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $case->case_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $case->category?->translation('id')?->name ?? $case->category?->slug ?? 'Uncategorized'
                            }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-internal.badge
                                variant="{{ $case->status?->key === 'new' || $case->status?->key === 'unverified' ? 'new' : ($case->status?->key === 'investigation' || $case->status?->key === 'in_progress' ? 'investigation' : ($case->is_public ? 'published' : 'default')) }}">
                                {{ $case->status?->name ?? 'Unknown' }}
                            </x-internal.badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $case->event_date ? \Carbon\Carbon::parse($case->event_date)->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($case->is_public)
                            <span class="text-green-600 font-medium">Yes</span>
                            @else
                            <span class="text-gray-500">No</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end items-center gap-2">
                                <a href="{{ route('case.detail', $case->id) }}"
                                    class="text-blue-600 hover:text-blue-900">
                                    View
                                </a>
                                @can('case.update', $case)
                                @if(!$case->is_public)
                                <a href="{{ route('case.detail', $case->id) }}"
                                    class="text-purple-600 hover:text-purple-900">
                                    Publish
                                </a>
                                @else
                                <a href="{{ route('case.detail', $case->id) }}"
                                    class="text-orange-600 hover:text-orange-900">
                                    Unpublish
                                </a>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                            No cases found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{--
    </div> --}}

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ================= BAR CHART: Cases per Category =================
            const categoryData = @json($casesByCategory);
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: categoryData.map(item => item.category_name),
                    datasets: [{
                        label: 'Number of Cases',
                        data: categoryData.map(item => item.count),
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

            // ================= PIE CHART: Case Status Distribution =================
            const statusData = @json($casesByStatus);
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: statusData.map(item => item.status_name),
                    datasets: [{
                        data: statusData.map(item => item.count),
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.6)',  // red
                            'rgba(234, 179, 8, 0.6)',  // yellow
                            'rgba(34, 197, 94, 0.6)',  // green
                            'rgba(59, 130, 246, 0.6)', // blue
                            'rgba(168, 85, 247, 0.6)', // purple
                            'rgba(251, 146, 60, 0.6)', // orange
                            'rgba(107, 114, 128, 0.6)' // gray
                        ],
                        borderColor: [
                            'rgba(239, 68, 68, 1)',
                            'rgba(234, 179, 8, 1)',
                            'rgba(34, 197, 94, 1)',
                            'rgba(59, 130, 246, 1)',
                            'rgba(168, 85, 247, 1)',
                            'rgba(251, 146, 60, 1)',
                            'rgba(107, 114, 128, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // ================= LINE CHART: Reports Over Time =================
            const timeData = @json($reportsOverTime);
            // Create date range for last 30 days
            const dates = [];
            const counts = [];
            const today = new Date();
            for (let i = 29; i >= 0; i--) {
                const date = new Date(today);
                date.setDate(date.getDate() - i);
                const dateStr = date.toISOString().split('T')[0];
                dates.push(dateStr);
                const dataPoint = timeData.find(d => d.date === dateStr);
                counts.push(dataPoint ? dataPoint.count : 0);
            }

            const timeCtx = document.getElementById('timeChart').getContext('2d');
            new Chart(timeCtx, {
                type: 'line',
                data: {
                    labels: dates.map(d => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
                    datasets: [{
                        label: 'Cases Created',
                        data: counts,
                        borderColor: 'rgba(59, 130, 246, 1)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
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

            // ================= LEAFLET MAP =================
            // Initialize map centered on Indonesia
            const map = L.map('caseMap', {
                center: [-2.5, 118],
                zoom: 5,
                scrollWheelZoom: true
            });

            // Add OpenStreetMap tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            // Get cases data from Blade
            const casesWithLocation = @json($casesWithLocation);

            // Helper function to get marker icon color
            function getMarkerIcon(color) {
                return L.divIcon({
                    className: 'custom-marker',
                    html: `<div style="background-color: ${color}; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });
            }

            // Add markers for each case
            casesWithLocation.forEach(function(caseItem) {
                if (!caseItem.latitude || !caseItem.longitude) {
                    return;
                }

                const lat = parseFloat(caseItem.latitude);
                const lng = parseFloat(caseItem.longitude);

                if (isNaN(lat) || isNaN(lng)) {
                    return;
                }

                // Determine color
                const colors = {
                    'red': '#ef4444',
                    'yellow': '#eab308',
                    'green': '#22c55e',
                    'gray': '#6b7280'
                };
                const markerColor = colors[caseItem.color] || colors.gray;

                // Create marker
                const marker = L.marker([lat, lng], {
                    icon: getMarkerIcon(markerColor)
                });

                // Build popup content
                let popupContent = '<div class="p-3" style="min-width: 200px;">';
                popupContent += '<div class="font-semibold text-gray-900 mb-2">' + escapeHtml(caseItem.case_number) + '</div>';
                popupContent += '<div class="text-sm mb-1"><span class="text-gray-500">Status:</span> <span class="font-medium">' + escapeHtml(caseItem.status_name) + '</span></div>';
                popupContent += '<div class="text-sm mb-2"><span class="text-gray-500">Category:</span> <span class="font-medium">' + escapeHtml(caseItem.category_name) + '</span></div>';
                popupContent += '<a href="/cms/cases/' + caseItem.id + '/detail" class="inline-block mt-2 px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition">Review Case</a>';
                popupContent += '</div>';

                marker.bindPopup(popupContent);
                marker.addTo(map);
            });

            // Helper function to escape HTML (prevent XSS)
            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
            }
        });
    </script>

    <style>
        .custom-marker {
            background: transparent !important;
            border: none !important;
        }
    </style>
    @endpush
</x-internal-layout>