<div>
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
    <div class="w-full bg-[#00323C] py-10 mt-25 px-4 text-white" x-data="filterUI()">

        <!-- MAP -->
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
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 max-w-6xl mx-auto mt-6">

                <!-- Keyword -->
                <div class="flex flex-col">
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
                </div>

                <!-- Sector -->
                <div class="flex flex-col">
                    <label class="text-xs tracking-wider mb-1 opacity-70">SECTOR</label>
                    <select
                        class="w-full bg-white/10 border border-white/30 rounded-lg px-3 h-[46px] outline-none text-sm">
                        <option>Pilih Sector</option>
                        for
                        <option value=""></option>
                    </select>
                </div>

                <!-- Status -->
                <div class="flex flex-col">
                    <label class="text-xs tracking-wider mb-1 opacity-70">STATUS</label>
                    <select
                        class="w-full bg-white/10 border border-white/30 rounded-lg px-3 h-[46px] outline-none text-sm">
                        <option>Aktif / Tidak Aktif</option>
                    </select>
                </div>

                <!-- Location -->
                <div class="flex flex-col">
                    <label class="text-xs tracking-wider mb-1 opacity-70">LOCATION</label>
                    <input type="text"
                        class="w-full bg-white/10 border border-white/30 rounded-lg px-3 h-[46px] outline-none text-sm"
                        placeholder="Masukkan lokasi...">
                </div>

                <!-- Button -->
                <div class="flex flex-col justify-end">
                    <button
                        class="w-full h-[46px] rounded-lg border border-white/30 bg-white/10 hover:bg-white hover:text-[#00323C] transition flex items-center justify-center gap-2">
                        <span>Cari</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" />
                        </svg>
                    </button>
                </div>

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

            // Tambah marker dari case_geometries
            fetch('/case-geometries')
                .then(res => res.json())
                .then(geomData => {
                    if (geomData && Array.isArray(geomData.features)) {

                        // simple HTML escape helper
                        window.escapeHtml = window.escapeHtml || function (unsafe) {
                            return String(unsafe)
                                .replaceAll('&', '&amp;')
                                .replaceAll('<', '&lt;')
                                .replaceAll('>', '&gt;')
                                .replaceAll('"', '&quot;')
                                .replaceAll("'", '&#039;');
                        };

                        geomData.features.forEach(f => {
                            if (!f.geometry || !f.geometry.type) return;

                            // POINT
                            if (f.geometry.type === 'Point') {
                                const coords = f.geometry.coordinates; // [lon, lat]
                                const lat = coords[1];
                                const lon = coords[0];
                                const case_id = f.properties.case_id || null;
                                const title = f.properties.title || ('Case ' + f.properties.case_id);
                                const category = f.properties.category || null;
                                // const geom = f.properties.geom || null;

                                const popupHtml = `
                                    <div style="min-width:200px">
                                        <div style="font-weight:600;margin-bottom:6px">No Case : ${escapeHtml(case_id)}</div>
                                        <div style="font-weight:600;margin-bottom:6px">Title : ${escapeHtml(title)}</div>
                                        <div style="font-weight:600;margin-bottom:6px">Category : ${escapeHtml(category)}</div>
                                        ${f.properties.event_date ? `<div style="font-size:12px;color:#666;margin-bottom:6px">Tanggal: ${escapeHtml(f.properties.event_date)}</div>` : ''}
                                        ${f.properties.case_description ? `<div style="font-size:13px;color:#333">${escapeHtml(f.properties.case_description)}</div>` : ''}
                                    </div>
                                `;

                                L.marker([lat, lon])
                                    .bindPopup(popupHtml)
                                    .addTo(markers);
                            }

                            // POLYGON / MULTIPOLYGON -> add centroid marker
                            if (f.geometry.type === 'Polygon' || f.geometry.type === 'MultiPolygon') {
                                try {
                                    const g = f.geometry;
                                    const bounds = L.geoJSON(g).getBounds();
                                    const center = bounds.getCenter();
                                    const title = f.properties.title || ('Case ' + f.properties.case_id);
                                    const popupHtml = `
                                        <div style="min-width:200px">
                                            <div style="font-weight:600;margin-bottom:6px">${escapeHtml(title)}</div>
                                            <div style="font-weight:600;margin-bottom:6px">${escapeHtml(category)}</div>
                                            ${f.properties.event_date ? `<div style="font-size:12px;color:#666;margin-bottom:6px">Tanggal: ${escapeHtml(f.properties.event_date)}</div>` : ''}
                                            ${f.properties.case_description ? `<div style="font-size:13px;color:#333">${escapeHtml(f.properties.case_description)}</div>` : ''}
                                        </div>
                                    `;
                                    L.marker([center.lat, center.lng])
                                        .bindPopup(popupHtml)
                                        .addTo(markers);
                                } catch (e) {
                                    // ignore
                                }
                            }

                        });
                    }
                })
                .catch(err => console.error('Failed to load case geometries', err));

            // matikan dragging setelah semuanya kelar
            setTimeout(() => {
                map.dragging.disable();
            }, 400);

        })
        .catch(err => console.error("ERROR:", err));

    });
    </script>
</div>