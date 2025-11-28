<style>
    #map {
        width: 100%;
        height: 400px;
        background: #00323C; 
    }

    /* RESPONSIVE MOBILE */
    @media (max-width: 640px) {
        #map {
            height: 250px; /* lebih pendek biar muat mobile */
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
                <select class="w-full bg-white/10 border border-white/30 rounded-lg px-3 h-[46px] outline-none text-sm">
                    <option>Pilih Sector</option>
                </select>
            </div>

            <!-- Status -->
            <div class="flex flex-col">
                <label class="text-xs tracking-wider mb-1 opacity-70">STATUS</label>
                <select class="w-full bg-white/10 border border-white/30 rounded-lg px-3 h-[46px] outline-none text-sm">
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
        attributionControl: false,
        scrollWheelZoom: false,
        doubleClickZoom: false,
        touchZoom: false,
        boxZoom: false,
        keyboard: false
    });

    // enable dragging dulu supaya fitBounds bekerja
    map.dragging.enable();

    L.tileLayer('', {}).addTo(map); // background kosong

    // contoh marker data
    var incidents = [
        { lat: -6.2, lng: 106.8, title: "Jakarta" },
        { lat: -7.8, lng: 110.4, title: "Yogyakarta" },
        { lat: 3.6, lng: 98.6, title: "Medan" }
    ];

    // marker layer
    var markers = L.layerGroup().addTo(map);

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

            // Tambah marker
            // incidents.forEach(item => {
            //     L.marker([item.lat, item.lng])
            //         .bindPopup(item.title)
            //         .addTo(markers);
            // });

            // matikan dragging setelah semuanya kelar
            setTimeout(() => {
                map.dragging.disable();
            }, 400);

        })
        .catch(err => console.error("ERROR:", err));

});
</script>