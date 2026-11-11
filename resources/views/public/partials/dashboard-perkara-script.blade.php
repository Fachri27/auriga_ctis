{{-- Peta & grafik mode Perkara. Dipanggil saat halaman dimuat dan setiap kali konten mode Perkara ditukar tanpa
     reload; datanya dibaca dari #perkara-map-data (public/partials/dashboard-perkara). Mengembalikan fungsi pembersih.
     Kontrol peta bersama (layar penuh, tampilan awal, cari) ada di front/components/dashboard-map-script. --}}
<script>
    window.initPerkaraDashboard = (root) => {
        if (window.pubInitCharts) window.pubInitCharts(root);
        const cleanup = () => { if (window.pubDisposeCharts) window.pubDisposeCharts(root); };

        const el = root.querySelector('#perkaraMap');
        const dataEl = root.querySelector('#perkara-map-data');
        if (!el || !dataEl || !window.gdDashMap) return cleanup;

        const DATA = JSON.parse(dataEl.textContent);
        const fmt = (n) => Number(n).toLocaleString(DATA.locale);
        const esc = gdDashMap.esc;
        const pct = (v, t) => { const n = v / t * 100; return n < 1 ? '<1%' : `${Math.round(n)}%`; };
        const order = Object.keys(DATA.colors);
        const colorOf = (label) => DATA.colors[label] ?? '#9ca3af';
        const rank = (label) => { const i = order.indexOf(label); return i < 0 ? order.length : i; };

        // Peringkat kabupaten menurut jumlah perkara (di antara titik yang tampil sesuai filter).
        const points = [...DATA.points].sort((a, b) => b.total - a.total);
        const rankOf = new Map(points.map((p, i) => [p, i + 1]));

        let group = null;
        let markers = [];
        // Disaring per pulau/klasifikasi: tampilan awal = titik yang tampil; tanpa filter = seluruh Indonesia.
        const fitPoints = (animate = false) => {
            if (!DATA.filtered || !markers.length) return dash.fitIndonesia(animate);
            map.fitBounds(L.latLngBounds(markers.map((m) => m.getLatLng())), { padding: [30, 30], maxZoom: 8, animate });
        };

        const dash = gdDashMap.create(el, { maxZoom: 12, card: root.querySelector('[data-map-card]'), onReset: () => fitPoints(true) });
        const map = dash.map;

        // Cincin komposisi klasifikasi, urut sama dengan daftar di sidebar; sisa tanpa klasifikasi berwarna abu-abu.
        function ring(counts, total) {
            let start = 0;
            const stops = Object.entries(counts).sort(([a], [b]) => rank(a) - rank(b)).map(([label, value]) => {
                const end = start + value / total * 100;
                const stop = `${colorOf(label)} ${start}% ${end}%`;
                start = end;
                return stop;
            });
            if (start < 99.9) stops.push(`#d1d5db ${start}% 100%`);
            return `conic-gradient(${stops.join(',')})`;
        }

        // Donat berangka: luasnya naik secara logaritmik agar kabupaten kecil tetap terlihat.
        function donut(total, counts, title = '') {
            // Angka ribuan ("5.466") butuh ruang lebih agar tetap muat di tengah donat.
            const size = Math.max(Math.round(18 + 5 * Math.log10(Math.max(1, total))), total >= 1000 ? 38 : 0);
            const thick = size < 28 ? 3 : 4;
            const font = size < 26 ? 9 : 10;
            return L.divIcon({
                className: 'perkara-donut',
                html: `<div ${title ? `title="${esc(title)}"` : ''} style="
                    width:${size}px;height:${size}px;border-radius:50%;padding:${thick}px;box-sizing:border-box;
                    background:${ring(counts, total)};box-shadow:0 1px 4px rgba(0,0,0,.3);cursor:pointer;
                "><div style="
                    width:100%;height:100%;border-radius:50%;background:#fff;
                    display:flex;align-items:center;justify-content:center;
                    font-family:inherit;font-weight:700;font-size:${font}px;line-height:1;color:#0B1E07;
                ">${fmt(total)}</div></div>`,
                iconSize: L.point(size, size),
                tooltipAnchor: L.point(0, -size / 2),
                popupAnchor: L.point(0, -size / 2),
            });
        }

        function popup(p) {
            const bar = p.klasifikasi.map((k) => `<span style="width:${k.value / p.total * 100}%;background:${colorOf(k.label)}"></span>`).join('');
            const rows = p.klasifikasi.map((k) => `
                <li style="display:flex;justify-content:space-between;align-items:center;gap:12px">
                    <span style="display:flex;align-items:center;gap:6px;min-width:0">
                        <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:${colorOf(k.label)}"></span>${esc(k.label)}
                    </span>
                    <span style="white-space:nowrap"><strong>${fmt(k.value)}</strong> <span style="color:#6b7268">${pct(k.value, p.total)}</span></span>
                </li>`).join('');
            const place = DATA.i18n.rank.replace(':rank', fmt(rankOf.get(p))).replace(':count', fmt(points.length));

            return `
                <div style="min-width:240px;font-family:inherit">
                    <div style="font-weight:700;font-size:14px;color:#0B1E07;padding-right:10px">${esc(p.name)}</div>
                    <div style="font-size:12px;color:#374151;margin-top:2px">${fmt(p.total)} ${esc(DATA.i18n.cases)} · ${fmt(p.terdakwa)} ${esc(DATA.i18n.defendants)}</div>
                    <div style="font-size:11px;color:#6b7268;margin:2px 0 10px">${esc(place)}</div>
                    <div style="display:flex;height:6px;border-radius:3px;overflow:hidden;background:#E2E6DA;margin-bottom:8px">${bar}</div>
                    <ul style="list-style:none;margin:0;padding:0;font-size:11px;color:#374151;display:grid;gap:4px">${rows}</ul>
                </div>`;
        }

        markers = DATA.points.map((p) => {
            const counts = Object.fromEntries(p.klasifikasi.map((k) => [k.label, k.value]));
            // Kabupaten dengan perkara lebih sedikit digambar di atas, agar tetap bisa diklik.
            const marker = L.marker([p.lat, p.lng], { icon: donut(p.total, counts), zIndexOffset: -p.total, total: p.total, klasifikasi: p.klasifikasi, title: p.name });
            marker.bindPopup(popup(p), { maxWidth: 320, autoPanPadding: [40, 40] });
            marker.bindTooltip(`${esc(p.name)}: ${fmt(p.total)} ${esc(DATA.i18n.cases)}`, { direction: 'top' });
            // Tooltip tidak perlu muncul saat popup kabupaten yang sama sedang terbuka.
            marker.on('popupopen', () => marker.closeTooltip());
            p.marker = marker;
            return marker;
        });

        // Kabupaten yang berdekatan digabung menjadi satu donat (jumlah perkara + komposisi gabungan).
        if (typeof L.markerClusterGroup === 'function') {
            group = L.markerClusterGroup({
                maxClusterRadius: 35,
                disableClusteringAtZoom: 8,
                showCoverageOnHover: false,
                iconCreateFunction: (cluster) => {
                    const children = cluster.getAllChildMarkers();
                    const counts = {};
                    let total = 0;
                    children.forEach((m) => {
                        total += m.options.total;
                        m.options.klasifikasi.forEach((k) => { counts[k.label] = (counts[k.label] ?? 0) + k.value; });
                    });
                    const title = DATA.i18n.cluster.replace(':regencies', fmt(children.length)).replace(':cases', fmt(total));
                    return donut(total, counts, title);
                },
            });
            group.addLayers(markers);
            map.addLayer(group);
        } else {
            markers.forEach((m) => m.addTo(map));
        }

        // Cari kabupaten/kota: urut perkara terbanyak, lalu peta diarahkan ke kabupaten yang dipilih.
        if (points.length) {
            gdDashMap.search(map, points.map((p) => ({ label: p.name, sub: `${fmt(p.total)} ${DATA.i18n.cases}`, marker: p.marker })), {
                placeholder: DATA.i18n.search,
                onPick: (item) => gdDashMap.reveal(map, group, item.marker, 8),
            });
        }

        fitPoints();

        return () => {
            cleanup();
            dash.destroy();
        };
    };
</script>
