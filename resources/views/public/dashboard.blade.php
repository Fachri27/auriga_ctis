@extends('layouts.main')

@section('content')
<div class="bg-gray-50 mt-20 poppins-regular">

    {{-- HEADER --}}
    <section class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-6 py-8">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900">Public Case Dashboard</h1>
            <p class="mt-2 text-gray-600">Track and view public case information</p>
        </div>
    </section>

    {{-- SUMMARY CARDS --}}
    <section class="max-w-7xl mx-auto px-6 py-8">
        <div class="grid md:grid-cols-3 gap-6">
            {{-- Total Cases Card --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide font-medium">Total Cases</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalCases }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Active Cases Card --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide font-medium">Active Cases</p>
                        <p class="mt-2 text-3xl font-bold text-green-600">{{ $activeCases }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Closed Cases Card --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide font-medium">Closed Cases</p>
                        <p class="mt-2 text-3xl font-bold text-gray-600">{{ $closedCases }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FILTER LABEL --}}
    @php
    $filterLabel = 'All Cases';
    if (!empty($filter)) {
    if ($filter === 'active') $filterLabel = 'Under Investigation';
    elseif ($filter === 'published') $filterLabel = 'Published Cases';
    elseif ($filter === 'closed') $filterLabel = 'Closed Cases';
    }
    @endphp
    <section class="max-w-7xl mx-auto px-6 py-2">
        <div class="text-sm text-gray-700">Showing: <span class="font-semibold">{{ $filterLabel }}</span></div>
    </section>

    {{-- MAP SECTION --}}
    <section class="max-w-7xl mx-auto px-6 pb-12">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Case Locations</h2>
                <p class="text-sm text-gray-600 mt-1">Interactive map showing all public case locations</p>
            </div>
            <div id="map" class="w-full" style="height: 70vh; min-height: 500px;"></div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    // Initialize map with default center for Indonesia
    var map = L.map('map', {
        center: [-2.5, 118],
        zoom: 5,
        scrollWheelZoom: true
    });

    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Initialize marker cluster group
    var markers = L.markerClusterGroup({
        disableClusteringAtZoom: 14,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false
    });

    // Get cases data from Blade
    var cases = @json($cases);

    // Safety check: handle empty dataset
    if (!cases || cases.length === 0) {
        console.log('No cases to display on map');
        return;
    }

    // Add markers for each case
    cases.forEach(function(caseItem) {
        // Safety check: skip if coordinates are missing or null
        if (!caseItem.latitude || !caseItem.longitude) {
            return;
        }

        var lat = parseFloat(caseItem.latitude);
        var lng = parseFloat(caseItem.longitude);

        // Validate coordinates
        if (isNaN(lat) || isNaN(lng)) {
            return;
        }

        // Create marker
        var marker = L.marker([lat, lng]);

        // Build popup content safely
        var popupContent = '<div class="p-2">';
        
        // Case number
        if (caseItem.case_number) {
            popupContent += '<div class="font-semibold text-gray-900 mb-2">' + escapeHtml(caseItem.case_number) + '</div>';
        }

        // Category name (safely access nested property)
        if (caseItem.category && caseItem.category.name) {
            popupContent += '<div class="text-sm mb-1"><span class="text-gray-500">Category:</span> <span class="font-medium">' + escapeHtml(caseItem.category.name) + '</span></div>';
        }

        // Status name (safely access nested property)
        if (caseItem.status && caseItem.status.name) {
            popupContent += '<div class="text-sm mb-1"><span class="text-gray-500">Status:</span> <span class="font-medium">' + escapeHtml(caseItem.status.name) + '</span></div>';
        }

        // Event date
        if (caseItem.event_date) {
            popupContent += '<div class="text-sm mb-2"><span class="text-gray-500">Event Date:</span> <span class="font-medium">' + escapeHtml(caseItem.event_date) + '</span></div>';
        }

        // View Detail link (dummy href as requested)
        popupContent += '<a href="#" class="inline-block mt-2 px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition">View Detail</a>';
        popupContent += '</div>';

        marker.bindPopup(popupContent);
        markers.addLayer(marker);
    });

    // Add marker cluster group to map
    map.addLayer(markers);

    // Helper function to escape HTML (prevent XSS)
    function escapeHtml(text) {
        var map = {
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
@endpush






