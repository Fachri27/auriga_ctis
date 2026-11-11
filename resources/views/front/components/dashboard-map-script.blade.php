{{-- Bagian bersama peta dashboard (mode Kasus & Perkara): basemap, bingkai awal seluruh Indonesia, scroll halaman
     tidak tertahan peta (zoom pakai Ctrl/⌘ + scroll, geser pakai dua jari di layar sentuh, lewat plugin
     leaflet-gesture-handling di layouts.main), tombol tampilan awal, layar penuh, citra satelit, dan kotak cari lokasi.
     Gayanya ada di public/partials/dashboard-map-style. --}}
@php
    $mapI18n = [
        'touch' => __('Gunakan dua jari untuk menggeser peta'),
        'scroll' => __('Tekan Ctrl sambil scroll untuk memperbesar peta'),
        'scrollMac' => __('Tekan ⌘ sambil scroll untuk memperbesar peta'),
        'zoomIn' => __('Perbesar'),
        'zoomOut' => __('Perkecil'),
        'reset' => __('Kembali ke tampilan awal'),
        'fullscreen' => __('Layar penuh'),
        'exitFullscreen' => __('Keluar dari layar penuh'),
        'satellite' => __('Citra satelit'),
        'noMatch' => __('Tidak ditemukan'),
    ];
@endphp
<script>
    // Tanpa Leaflet (CDN gagal dimuat) peta tidak dibuat; halaman tetap jalan.
    window.gdDashMap = typeof L === 'undefined' ? null : (() => {
        const T = @json($mapI18n);
        // Sedikit lebih lebar dari daratan Indonesia (Sabang–Merauke, Miangas–Rote).
        const INDONESIA = L.latLngBounds([-11.2, 94.6], [6.4, 141.2]);
        const ESRI = 'https://server.arcgisonline.com/ArcGIS/rest/services/';
        const isMac = /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent);
        // Layar penuh tetap aktif saat konten dashboard ditukar tanpa reload (mis. klik klasifikasi di layar penuh).
        let fullscreen = false;

        const ICONS = {
            reset: '<path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
            maximize: '<path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/>',
            minimize: '<path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/>',
            satellite: '<path d="M13 7 9 3 5 7l4 4"/><path d="m17 11 4 4-4 4-4-4"/><path d="m8 12 4 4 6-6-4-4Z"/><path d="m16 8 3-3"/><path d="M9 21a6 6 0 0 0-6-6"/>',
            search: '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        };
        const icon = (name, size = 16) => `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICONS[name]}</svg>`;
        const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);
        const tiles = (path, options = {}) => L.tileLayer(`${ESRI}${path}/MapServer/tile/{z}/{y}/{x}`, { maxZoom: 16, ...options });

        /**
         * Buat peta dashboard di `el`. `card` = elemen pembungkus peta + sidebar yang dibuat layar penuh;
         * `onReset` = aksi tombol tampilan awal (bawaan: seluruh Indonesia); `satellite` = tampilkan tombol citra satelit.
         * Mengembalikan { map, fitIndonesia, destroy }.
         */
        function create(el, { maxZoom = 16, card = null, onReset = null, satellite = false } = {}) {
            const map = L.map(el, {
                zoomControl: false,
                zoomSnap: 0.25,
                maxZoom,
                maxBounds: INDONESIA.pad(0.5),
                maxBoundsViscosity: 0.8,
                gestureHandling: true,
                gestureHandlingOptions: {
                    text: { touch: T.touch, scroll: isMac ? T.scrollMac : T.scroll, scrollMac: T.scrollMac },
                    duration: 1500,
                },
            });
            el.classList.add('gd-dash-map');

            const base = L.layerGroup([
                tiles('Canvas/World_Light_Gray_Base', { attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ' }),
                tiles('Canvas/World_Light_Gray_Reference'),
            ]).addTo(map);
            const imagery = satellite ? L.layerGroup([
                tiles('World_Imagery', { attribution: 'Tiles &copy; Esri &mdash; Esri, Maxar, Earthstar Geographics' }),
                tiles('Reference/World_Boundaries_and_Places'),
            ]) : null;

            // Ruang kiri untuk tombol zoom & alat agar titik di Aceh tidak tertutup.
            const fitIndonesia = (animate = false) => map.fitBounds(INDONESIA, { paddingTopLeft: [48, 8], paddingBottomRight: [8, 8], animate });
            // Batas perkecil: sedikit di bawah zoom yang memuat seluruh Indonesia, agar peta tidak "hilang" di tengah laut.
            const updateMinZoom = () => map.setMinZoom(Math.max(2, Math.floor(map.getBoundsZoom(INDONESIA)) - 1));

            L.control.zoom({ position: 'topleft', zoomInTitle: T.zoomIn, zoomOutTitle: T.zoomOut }).addTo(map);

            let fsButton = null;
            const Tools = L.Control.extend({
                options: { position: 'topleft' },
                onAdd() {
                    const bar = L.DomUtil.create('div', 'leaflet-bar gd-map-tools');
                    L.DomEvent.disableClickPropagation(bar);
                    const button = (name, title, onClick) => {
                        const b = L.DomUtil.create('button', '', bar);
                        b.type = 'button';
                        b.title = title;
                        b.setAttribute('aria-label', title);
                        b.innerHTML = icon(name);
                        L.DomEvent.on(b, 'click', onClick);
                        return b;
                    };
                    button('reset', T.reset, () => (onReset ? onReset() : fitIndonesia(true)));
                    if (card) {
                        fsButton = button('maximize', T.fullscreen, () => setFullscreen(!fullscreen));
                        fsButton.setAttribute('aria-pressed', 'false');
                    }
                    if (imagery) {
                        const b = button('satellite', T.satellite, () => {
                            const on = !map.hasLayer(imagery);
                            map.removeLayer(on ? base : imagery);
                            map.addLayer(on ? imagery : base);
                            b.setAttribute('aria-pressed', String(on));
                        });
                        b.setAttribute('aria-pressed', 'false');
                    }
                    return bar;
                },
            });
            new Tools().addTo(map);

            function setFullscreen(on) {
                fullscreen = on;
                card.classList.toggle('is-fullscreen', on);
                document.documentElement.classList.toggle('gd-map-fullscreen', on);
                fsButton.innerHTML = icon(on ? 'minimize' : 'maximize');
                fsButton.title = on ? T.exitFullscreen : T.fullscreen;
                fsButton.setAttribute('aria-label', fsButton.title);
                fsButton.setAttribute('aria-pressed', String(on));
                document[on ? 'addEventListener' : 'removeEventListener']('keydown', onKey);
                // Layar penuh: tidak ada halaman untuk di-scroll, jadi scroll & geser langsung menggerakkan peta.
                if (map.gestureHandling) {
                    map.gestureHandling[on ? 'disable' : 'enable']();
                    if (!on && el.matches(':hover')) ['dragging', 'scrollWheelZoom', 'tap'].forEach((h) => map[h] && map[h].enable());
                }
                map.invalidateSize();
                updateMinZoom();
            }
            const onKey = (e) => { if (e.key === 'Escape') setFullscreen(false); };

            map.on('resize', updateMinZoom);
            fitIndonesia();
            updateMinZoom();
            if (card && fullscreen) setFullscreen(true);

            return {
                map,
                fitIndonesia,
                destroy() {
                    document.removeEventListener('keydown', onKey);
                    // Status layar penuh disimpan untuk peta berikutnya; kelas <html> dipasang lagi saat peta itu dibuat.
                    document.documentElement.classList.remove('gd-map-fullscreen');
                    map.remove();
                },
            };
        }

        /** Gulir `box` agar `el` terlihat, tanpa ikut menggulir halaman seperti scrollIntoView. */
        function scrollWithin(box, el) {
            const b = box.getBoundingClientRect();
            const r = el.getBoundingClientRect();
            if (r.top < b.top) box.scrollTop -= b.top - r.top;
            else if (r.bottom > b.bottom) box.scrollTop += r.bottom - b.bottom;
        }

        /** Perbesar peta ke penanda (keluarkan dari kelompok bila perlu), lalu buka popup-nya. */
        function reveal(map, group, marker, zoom) {
            map.once('moveend', () => (group ? group.zoomToShowLayer(marker, () => marker.openPopup()) : marker.openPopup()));
            map.setView(marker.getLatLng(), Math.max(map.getZoom(), zoom), { animate: true });
        }

        /** Kotak cari di peta. `items` = [{ label, sub, ... }]; `onPick(item)` dipanggil saat satu hasil dipilih. */
        function search(map, items, { placeholder, onPick, position = 'topright' }) {
            const norm = (s) => String(s).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
            const id = 'gd-map-search-' + L.Util.stamp(map);
            let results = [];
            let active = -1;

            const Control = L.Control.extend({
                options: { position },
                onAdd() {
                    const box = L.DomUtil.create('div', 'gd-map-search');
                    box.innerHTML = `
                        <label class="gd-map-search-field">
                            ${icon('search', 14)}
                            <input type="search" autocomplete="off" spellcheck="false" placeholder="${esc(placeholder)}" aria-label="${esc(placeholder)}"
                                role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="${id}">
                        </label>
                        <ul id="${id}" role="listbox" hidden></ul>`;
                    L.DomEvent.disableClickPropagation(box);
                    L.DomEvent.disableScrollPropagation(box);

                    const input = box.querySelector('input');
                    const list = box.querySelector('ul');

                    const close = () => {
                        list.hidden = true;
                        input.setAttribute('aria-expanded', 'false');
                        input.removeAttribute('aria-activedescendant');
                        active = -1;
                    };
                    const highlight = (i) => {
                        active = i;
                        list.querySelectorAll('[role="option"]').forEach((li, n) => li.setAttribute('aria-selected', String(n === i)));
                        if (i >= 0) {
                            input.setAttribute('aria-activedescendant', `${id}-${i}`);
                            scrollWithin(list, list.children[i]);
                        }
                    };
                    const pick = (i) => {
                        const item = results[i];
                        if (!item) return;
                        input.value = item.label;
                        close();
                        input.blur();
                        onPick(item);
                    };
                    const render = () => {
                        const q = norm(input.value.trim());
                        if (!q) return close();
                        // Yang diawali kata kunci lebih dulu, lalu yang memuatnya; urutan asli `items` dipertahankan.
                        const starts = [];
                        const contains = [];
                        items.forEach((item) => {
                            const label = norm(item.label);
                            if (label.startsWith(q) || label.includes(' ' + q)) starts.push(item);
                            else if (label.includes(q)) contains.push(item);
                        });
                        results = starts.concat(contains).slice(0, 8);
                        list.innerHTML = results.length
                            ? results.map((item, i) => `<li id="${id}-${i}" role="option" aria-selected="false" data-i="${i}"><span>${esc(item.label)}</span>${item.sub ? `<small>${esc(item.sub)}</small>` : ''}</li>`).join('')
                            : `<li class="gd-map-search-empty">${esc(T.noMatch)}</li>`;
                        list.hidden = false;
                        input.setAttribute('aria-expanded', 'true');
                        highlight(results.length ? 0 : -1);
                    };

                    input.addEventListener('input', render);
                    input.addEventListener('focus', () => input.value && render());
                    input.addEventListener('blur', () => setTimeout(close, 150));
                    input.addEventListener('keydown', (e) => {
                        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                            e.preventDefault();
                            if (list.hidden) return render();
                            if (results.length) highlight((active + (e.key === 'ArrowDown' ? 1 : -1) + results.length) % results.length);
                        } else if (e.key === 'Enter') {
                            e.preventDefault();
                            pick(active < 0 ? 0 : active);
                        } else if (e.key === 'Escape') {
                            e.stopPropagation(); // jangan sampai ikut menutup layar penuh
                            if (!list.hidden) close();
                            else input.value = '';
                        }
                    });
                    // mousedown agar pilihan terambil sebelum input kehilangan fokus.
                    list.addEventListener('mousedown', (e) => {
                        const li = e.target.closest('[data-i]');
                        if (!li) return;
                        e.preventDefault();
                        pick(Number(li.dataset.i));
                    });

                    return box;
                },
            });
            new Control().addTo(map);
        }

        return { create, reveal, search, scrollWithin, esc };
    })();
</script>
