<style>
    #map {
        width: 100%;
        height: 400px;
        background: #00323C;
    }

    /* RESPONSIVE MOBILE */
    @media (max-width: 640px) {
        #map {
            height: 250px;
            /* lebih pendek biar muat mobile */
        }
    }
</style>
<div class="w-full bg-[#00323C] py-10 mt-25 px-4 text-white poppins-regular" x-data="filterUI()">

    <div class="max-w-7xl mx-auto mb-8 sm:mb-10">
        <div id="map" class="bg-[#032A36] relative z-1"></div>
    </div>

    <div class="max-w-4xl mx-auto">

        <!-- MOBILE TITLE -->
        <h2 class="text-center text-xl font-semibold mb-6 md:hidden tracking-wide">
            Cari Data Indonesia
        </h2>

        <!-- FILTER WRAPPER -->
        <!-- FILTER FORM -->
        <div class="">

            <!-- Keyword -->
            {{-- <div class="flex flex-col">
                <label class="text-xs tracking-wider mb-1 opacity-70">KEYWORD</label>
                <div class="flex items-center bg-white/10 border border-white/30 rounded-lg px-3 h-[46px]">
                    <svg class="w-4 h-4 opacity-70 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" />
                    </svg>
                    <input type="text" class="w-full bg-transparent outline-none text-sm placeholder:text-white/60"
                        placeholder="Cari kata kunci...">
                </div>
            </div> --}}

            <!-- Sector -->
            {{-- <div class="flex flex-col">
                <label class="text-xs tracking-wider mb-1 opacity-70">SECTOR</label>
                <select wire:model="sector"
                    class="w-full bg-white/10 border border-white/30 rounded-lg px-3 h-[46px] outline-none text-sm">
                    <option value="">All sectors</option>
                    @foreach(
                    \Illuminate\Support\Facades\DB::table('case_geometries')->distinct()->pluck('category') as $s)
                    <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </div> --}}

            <!-- Status -->
            {{-- <div class="flex flex-col">
                <label class="text-xs tracking-wider mb-1 opacity-70">STATUS</label>
                <select class="w-full bg-white/10 border border-white/30 rounded-lg px-3 h-[46px] outline-none text-sm"
                    wire:modal='status'>
                    <option>Aktif / Tidak Aktif</option>
                </select>
            </div> --}}

            <!-- Location -->
            {{-- <div class="flex flex-col">
                <label class="text-xs tracking-wider mb-1 opacity-70">LOCATION</label>
                <input type="text"
                    class="w-full bg-white/10 border border-white/30 rounded-lg px-3 h-[46px] outline-none text-sm"
                    placeholder="Masukkan lokasi...">
            </div> --}}

            @livewire('peta.map-filter')

            <!-- Button -->
            {{-- <div class="flex flex-col justify-end">
                <button
                    class="w-full h-[46px] rounded-lg border border-white/30 bg-white/10 hover:bg-white hover:text-[#00323C] transition flex items-center justify-center gap-2"
                    wire:click='applyFilter'>
                    <span>Cari</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" />
                    </svg>
                </button>
            </div> --}}

        </div>

    </div>

</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {

    var map = L.map('map', {
        zoomControl: false,
        center: [-25.2702, 134.2798],
    zoom: 3,
    gestureHandling: true,
    gestureHandlingOptions: {
        text: {
            touch: "Hey bro, use two fingers to move the map",
            scroll: "Hey bro, use ctrl + scroll to zoom the map",
            scrollMac: "Hey bro, use \u2318 + scroll to zoom the map"
        }
    },
        attributionControl: false,
        scrollWheelZoom: true,
        doubleClickZoom: true,
        touchZoom: true,
        boxZoom: true,
        keyboard: false
    });

    // legend for status colors
    var legend = L.control({ position: 'topright' });
    legend.onAdd = function () {
        var div = L.DomUtil.create('div', 'leaflet-legend p-2 bg-white/90 rounded text-sm');
        div.style.minWidth = '160px';
        div.innerHTML = '<div style="font-weight:600;margin-bottom:6px; color:black;">Status</div>' +
            '<div style="color:black;"><span style="display:inline-block;width:12px;height:12px;background:#1f78b4;margin-right:6px;border-radius:2px;"></span>Open</div>' +
            '<div style="color:black;"><span style="display:inline-block;width:12px;height:12px;background:#ff7f0e;margin-right:6px;border-radius:2px;"></span>Investigation</div>' +
            '<div style="color:black;"><span style="display:inline-block;width:12px;height:12px;background:#2ca02c;margin-right:6px;border-radius:2px; color:black;"></span>Published</div>';
        return div;
    };
    legend.addTo(map);

    // enable dragging dulu supaya fitBounds bekerja
    map.dragging.enable();

    L.tileLayer('', {}).addTo(map); // background kosong

    // contoh marker data
    // var incidents = [
    //     { lat: -6.2, lng: 106.8, title: "Jakarta" },
    //     { lat: -7.8, lng: 110.4, title: "Yogyakarta" },
    //     { lat: 3.6, lng: 98.6, title: "Medan" }
    // ];

    // ambil data nya dari case_geometries

    // marker cluster layer (shows counts when multiple incidents are nearby)
    var markers = L.markerClusterGroup({
        // keep clustering until a reasonable zoom level
        disableClusteringAtZoom: 14,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false
    });
    map.addLayer(markers);

    fetch("/id.geojson")
        .then(res => res.json())
        .then(data => {

            var geoLayer = L.geoJSON(data, {
                style: {
                    color: "#ffffff",
                    weight: 1,
                    fillColor: "#BFC9D2",
                    fillOpacity: 1
                }
            }).addTo(map);

            let allFeatures = [];

            // ===== PC =====
            if (window.innerWidth > 768) {
                map.setView([-2.5, 118], 5);
            }

            // ===== MOBILE =====
            else {
                // beri jeda agar leaflet siap
                setTimeout(() => {
                    map.fitBounds(geoLayer.getBounds(), {
                        padding: [30, 30]
                    });
                }, 100);
            }

            // helper: load geometries with optional filters
            function loadGeometries(filters = {}) {
                const qs = new URLSearchParams(filters).toString();
                // console.log(qs);
                const url = '/case-geometries' + (qs ? ('?' + qs) : '');
                

                fetch(url)
                    .then(res => res.json())
                    .then(geomData => {
                        if (!(geomData && Array.isArray(geomData.features))) return;

                        // clear previous markers
                        markers.clearLayers();

                        // simple HTML escape helper
                        window.escapeHtml = window.escapeHtml || function (unsafe) {
                            return String(unsafe)
                                .replaceAll('&', '&amp;')
                                .replaceAll('<', '&lt;')
                                .replaceAll('>', '&gt;')
                                .replaceAll('"', '&quot;')
                                .replaceAll("'", '&#039;');
                        };

                        const bounds = L.latLngBounds();

                        const statusColors = {
                            open: '#1f78b4',
                            investigation: '#ff7f0e',
                            prosecution: '#d62728',
                            trial: '#9467bd',
                            executed: '#8c564b',
                            closed: '#7f7f7f',
                            rejected: '#e377c2',
                            published: '#2ca02c',
                            completed: '#17becf'
                        };

                        function colorFromString(str) {
                            if (!str) return '#2c3e50';
                            let hash = 0;
                            for (let i = 0; i < str.length; i++) {
                                hash = str.charCodeAt(i) + ((hash << 5) - hash);
                            }
                            const h = Math.abs(hash) % 360;
                            return `hsl(${h} 70% 45%)`;
                        }

                        const detailBase = '/{{ app()->getLocale() }}/detail-case/';
                        geomData.features.forEach(f => {
                            if (!f.geometry || !f.geometry.type) return;

                            const caseNumber = f.properties.case_number || f.properties.case_id || null;
                            const title = f.properties.title || ('Case ' + (caseNumber || f.properties.case_id));

                            const popupBase = `
                                <div style="min-width:200px">
                                    <div style="font-weight:600;margin-bottom:6px">${escapeHtml(title)}</div>
                                    ${f.properties.category ? `<div style="font-size:12px;color:#666;margin-bottom:6px">Category: ${escapeHtml(f.properties.category)}</div>` : ''}
                                    ${f.properties.status_key ? `<div style="font-size:12px;color:#666;margin-bottom:6px">Status: ${escapeHtml(f.properties.status_key)}</div>` : ''}
                                    ${f.properties.event_date ? `<div style="font-size:12px;color:#666;margin-bottom:6px">Tanggal: ${escapeHtml(f.properties.event_date)}</div>` : ''}
                                    ${f.properties.case_description ? `<div style="font-size:13px;color:#333;margin-bottom:8px">${escapeHtml(f.properties.case_description)}</div>` : ''}
                                    ${caseNumber ? `<div class="text-black"><a href="${detailBase}${encodeURIComponent(caseNumber)}" class="text-xs inline-block px-3 py-1 bg-green-600 text-black rounded">Lihat Detail</a></div>` : ''}
                                </div>
                            `;

                            if (f.geometry.type === 'Point') {
                                const coords = f.geometry.coordinates; // [lon, lat]
                                const lat = coords[1];
                                const lon = coords[0];

                                const color = f.properties.status_key && statusColors[f.properties.status_key]
                                    ? statusColors[f.properties.status_key]
                                    : colorFromString(f.properties.category || f.properties.title);

                                const circle = L.circleMarker([lat, lon], {
                                    radius: 8,
                                    color: '#ffffff',
                                    weight: 1,
                                    fillColor: color,
                                    fillOpacity: 1
                                }).bindPopup(popupBase);

                                markers.addLayer(circle);
                                bounds.extend([lat, lon]);
                            }

                            if (f.geometry.type === 'Polygon' || f.geometry.type === 'MultiPolygon') {
                                try {
                                    const g = f.geometry;
                                    const layer = L.geoJSON(g);
                                    const b = layer.getBounds();
                                    const center = b.getCenter();
                                    const color = f.properties.status_key && statusColors[f.properties.status_key]
                                        ? statusColors[f.properties.status_key]
                                        : colorFromString(f.properties.category || f.properties.title);
                                    const circle = L.circleMarker([center.lat, center.lng], {
                                        radius: 8,
                                        color: '#ffffff',
                                        weight: 1,
                                        fillColor: color,
                                        fillOpacity: 1
                                    }).bindPopup(popupBase);

                                    markers.addLayer(circle);
                                    bounds.extend(b);
                                } catch (e) { /* ignore */ }
                            }
                        });

                        // if we have markers, fit bounds (only on mobile to keep behavior)
                        if (bounds.isValid()) {
                            if (window.innerWidth <= 768) {
                                setTimeout(() => map.fitBounds(bounds, { padding: [30, 30] }), 100);
                            }
                        }
                    })
                    .catch(err => console.error('Failed to load case geometries', err));
            }

            // expose loadGeometries globally and apply any queued filters
            window.loadGeometries = loadGeometries;
            if (window.pendingLeafletFilter) {
                try { window.loadGeometries(window.pendingLeafletFilter); } catch (e) {}
                window.pendingLeafletFilter = null;
            }
            if (window.pendingLeafletReset) {
                try { window.loadGeometries(); } catch (e) {}
                window.pendingLeafletReset = null;
            }

            // initial load (no filters)
            loadGeometries();

            // listen for filter events from Livewire MapFilter

            
           window.addEventListener('apply-leaflet-filter', function (e) {
                // Livewire => e.detail adalah ARRAY
                const detail = Array.isArray(e.detail)
                    ? (e.detail[0] || {})
                    : (e.detail || {});

                console.log('FILTER PAYLOAD FROM EVENT:', detail);

                const payload = {
                    sector: detail.sector ?? '',
                    status: detail.status ?? '',
                    search: detail.search ?? '',
                    lat: detail.lat ?? '',
                    lng: detail.lng ?? '',
                    radius: detail.radius ?? ''
                };

                if (typeof window.loadGeometries === 'function') {
                    window.loadGeometries(payload);
                } else if (typeof loadGeometries === 'function') {
                    loadGeometries(payload);
                } else {
                    window.pendingLeafletFilter = payload;
                    console.info('loadGeometries not yet available; queueing filter');
                }
            });




            window.addEventListener('reset-leaflet-filter', function (e) {
                if (typeof window.loadGeometries === 'function') {
                    window.loadGeometries();
                } else if (typeof loadGeometries === 'function') {
                    loadGeometries();
                } else {
                    // queue reset request
                    window.pendingLeafletReset = true;
                    console.info('loadGeometries not yet available; queueing reset');
                }
            });

            // locate me button: get browser geolocation and set mapfilter via livewire
            // document.getElementById('locateMe')?.addEventListener('click', function () {
            //     if (!navigator.geolocation) {
            //         alert('Geolocation not supported');
            //         return;
            //     }
            //     navigator.geolocation.getCurrentPosition(function (pos) {
            //         const lat = pos.coords.latitude;
            //         const lng = pos.coords.longitude;
            //         // tell Livewire component to set lat/lng
            //         if (window.livewire) {
            //             window.livewire.emit('setLocation', lat, lng);
            //         }
            //         // also trigger load immediately
            //         const radiusEl = document.querySelector('input[wire\\:model\\.defer="radius"]');
            //         const radiusVal = radiusEl ? radiusEl.value : '';
            //         loadGeometries({ lat: lat, lng: lng, radius: radiusVal });
            //     }, function (err) {
            //         alert('Unable to get location: ' + err.message);
            //     });
            // });

            // matikan dragging setelah semuanya kelar
            setTimeout(() => {
                map.dragging.disable();
            }, 400);

        })
        .catch(err => console.error("ERROR:", err));

});
</script>