{{-- Gaya peta dashboard (mode Kasus & Perkara), lihat front/components/dashboard-map-script. Ditulis di sini, bukan
     sebagai kelas Tailwind, karena CSS Tailwind sudah di-build dan hanya memuat kelas yang sudah terpakai. --}}
<style>
    /* Tata letak kartu peta: di layar kecil peta di atas, daftar di bawah; di layar lebar daftar di kiri. */
    .gd-dash-layout { display: flex; flex-direction: column; }
    .gd-dash-canvas { position: relative; height: 380px; order: -1; }
    .gd-dash-side { display: flex; flex-direction: column; overflow: hidden; max-height: 340px; border-top: 1px solid #E2E6DA; }
    @media (min-width: 1024px) {
        .gd-dash-layout { flex-direction: row; height: 620px; }
        .gd-dash-canvas { order: 0; flex: 1; height: auto; }
        .gd-dash-side { width: 18rem; flex-shrink: 0; max-height: none; border-top: 0; border-right: 1px solid #E2E6DA; }
    }

    /* Layar penuh: kartu menutupi seluruh jendela, di atas navigasi (z-index 9999). */
    html.gd-map-fullscreen, html.gd-map-fullscreen body { overflow: hidden; }
    .gd-dash-card.is-fullscreen { position: fixed; inset: 0; z-index: 10000; border: 0; border-radius: 0; }
    .gd-dash-card.is-fullscreen .gd-dash-layout { height: 100%; }
    .gd-dash-card.is-fullscreen .gd-dash-canvas { flex: 1; height: auto; }
    @media (max-width: 1023px) { .gd-dash-card.is-fullscreen .gd-dash-side { max-height: 32vh; } }

    /* Kontrol peta */
    /* Latar = warna laut basemap. Ubin tidak dicampur "plus-lighter" (bawaan Leaflet 1.9) karena pada zoom pecahan
       tepi ubin yang bertumpuk jadi garis terang; celah sub-piksel yang tersisa tertutup warna latar ini. */
    .gd-dash-map.leaflet-container { font-family: inherit; background: #d0cfd4; }
    .gd-dash-map.leaflet-container img.leaflet-tile { mix-blend-mode: normal; }
    .gd-dash-map.leaflet-container .leaflet-bar { border: 1px solid #E2E6DA; border-radius: 2px; box-shadow: 0 1px 3px rgba(11, 30, 7, .12); overflow: hidden; }
    .gd-dash-map.leaflet-container .leaflet-bar a,
    .gd-map-tools button { display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; padding: 0; border: 0; border-bottom: 1px solid #E2E6DA; border-radius: 0; background: #fff; color: #0B1E07; font-size: 18px; line-height: 1; cursor: pointer; transition: background-color .15s, color .15s; }
    .gd-dash-map.leaflet-container .leaflet-bar a:last-child,
    .gd-map-tools button:last-child { border-bottom: 0; }
    .gd-dash-map.leaflet-container .leaflet-bar a:hover,
    .gd-map-tools button:hover { background: #F5F7F1; }
    .gd-map-tools button[aria-pressed="true"] { background: #0B1E07; color: #9BDB4D; }
    .gd-dash-map.leaflet-container .leaflet-bar a:focus-visible,
    .gd-map-tools button:focus-visible { outline: 2px solid #9BDB4D; outline-offset: -2px; }
    .gd-dash-map .leaflet-control-attribution { font-size: 10px; }

    /* Pesan "Ctrl + scroll" / "dua jari" dari leaflet-gesture-handling */
    .gd-dash-map.leaflet-gesture-handling:after { font-family: inherit; font-size: 15px; font-weight: 500; background: rgba(11, 30, 7, .6); }

    /* Kotak cari di peta */
    .gd-map-search { position: relative; width: 250px; max-width: calc(100vw - 120px); }
    .gd-map-search-field { display: flex; align-items: center; gap: 8px; height: 34px; padding: 0 10px; background: #fff; border: 1px solid #E2E6DA; border-radius: 2px; box-shadow: 0 1px 3px rgba(11, 30, 7, .12); color: #6b7268; cursor: text; }
    .gd-map-search-field:focus-within { border-color: #9BDB4D; box-shadow: 0 0 0 1px #9BDB4D; }
    .gd-map-search input { flex: 1; min-width: 0; height: 100%; padding: 0; border: 0; outline: 0; background: transparent; color: #0B1E07; font-size: 13px; }
    .gd-map-search ul { position: absolute; top: calc(100% + 4px); left: 0; right: 0; max-height: 280px; margin: 0; padding: 4px 0; overflow-y: auto; list-style: none; background: #fff; border: 1px solid #E2E6DA; box-shadow: 0 8px 20px rgba(11, 30, 7, .14); }
    .gd-map-search li { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; padding: 7px 10px; color: #0B1E07; font-size: 12px; line-height: 1.3; cursor: pointer; }
    .gd-map-search li[aria-selected="true"], .gd-map-search li:hover { background: #F5F7F1; }
    .gd-map-search li small { flex-shrink: 0; color: #6b7268; font-size: 11px; }
    .gd-map-search li.gd-map-search-empty { color: #6b7268; cursor: default; background: none; }

    /* Popup */
    .gd-dash-map .leaflet-popup-content-wrapper { border-radius: 4px; box-shadow: 0 8px 24px rgba(11, 30, 7, .18); }
    .gd-dash-map .leaflet-popup-content { margin: 14px 16px; font-size: 12px; line-height: 1.45; }
    .gd-dash-map .leaflet-container a.leaflet-popup-close-button,
    .gd-dash-map a.leaflet-popup-close-button { top: 4px; right: 4px; color: #6b7268; }
    .gd-dash-map .leaflet-tooltip { font-family: inherit; font-size: 12px; border-radius: 2px; }

    /* Legenda & keterangan di pojok kiri bawah peta; di layar kecil dinaikkan agar tidak menutupi atribusi. */
    .gd-map-corner, .gd-map-legend { position: absolute; left: 12px; bottom: 12px; z-index: 1000; }
    @media (max-width: 639px) { .gd-map-corner, .gd-map-legend { bottom: 24px; } }
    .gd-map-legend { display: flex; flex-wrap: wrap; gap: 4px 12px; max-width: calc(100% - 24px); padding: 6px 10px; background: rgba(255, 255, 255, .95); border: 1px solid #E2E6DA; border-radius: 2px; box-shadow: 0 1px 3px rgba(11, 30, 7, .12); color: #0B1E07; font-size: 11px; }
    .gd-map-legend span { display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
    .gd-map-legend i { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

    .gd-map-empty { position: absolute; inset: 0; z-index: 900; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(245, 247, 241, .65); pointer-events: none; }
    .gd-map-empty > div { max-width: 300px; padding: 16px 18px; background: #fff; border: 1px solid #E2E6DA; box-shadow: 0 8px 24px rgba(11, 30, 7, .12); color: #0B1E07; font-size: 13px; text-align: center; pointer-events: auto; }
    .gd-map-empty button, .gd-case-empty button { margin-top: 10px; padding: 7px 14px; background: #0B1E07; color: #fff; border-radius: 2px; font-size: 12px; font-weight: 600; cursor: pointer; }
    .gd-map-empty button:hover, .gd-case-empty button:hover { color: #9BDB4D; }

    /* Mode Kasus: penanda, kelompok, daftar samping, popup */
    .gd-case-dot > div, .case-cluster > div { transition: transform .15s ease, box-shadow .15s ease; }
    .gd-case-dot:hover > div { transform: scale(1.25); }
    .gd-case-dot.is-hover > div, .gd-case-dot.is-active > div { transform: scale(1.5); box-shadow: 0 0 0 4px rgba(155, 219, 77, .75), 0 1px 4px rgba(0, 0, 0, .35) !important; }
    .case-cluster.is-hover > div { transform: scale(1.12); box-shadow: 0 0 0 4px rgba(155, 219, 77, .75), 0 1px 6px rgba(0, 0, 0, .35) !important; }
    .gd-case-dot.is-hover, .gd-case-dot.is-active, .case-cluster.is-hover { z-index: 10000 !important; }

    .gd-case-item { display: block; width: 100%; padding: 11px 16px 11px 13px; text-align: left; background: #fff; border-bottom: 1px solid #E2E6DA; border-left: 3px solid transparent; cursor: pointer; transition: background-color .15s; }
    .gd-case-item:hover { background: #F5F7F1; }
    .gd-case-item:focus-visible { background: #F5F7F1; outline: 2px solid #9BDB4D; outline-offset: -2px; }
    .gd-case-item.is-active { background: #F5F7F1; border-left-color: #9BDB4D; }
    .gd-case-item-title { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; color: #0B1E07; font-size: 13px; font-weight: 600; line-height: 1.35; }
    .gd-case-item-meta { display: flex; align-items: center; gap: 6px; min-width: 0; margin-top: 6px; color: #6b7268; font-size: 11px; }
    .gd-case-item-meta .dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .gd-case-item-meta .status { flex-shrink: 0; }
    .gd-case-item-meta .loc { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .gd-case-item-code { display: block; margin-top: 3px; color: #9ca39a; font-size: 10px; letter-spacing: .03em; }
    .gd-case-empty { padding: 20px 16px; color: #6b7268; font-size: 12px; text-align: center; }

    .gd-pop { min-width: 220px; }
    .gd-pop-status { display: inline-flex; align-items: center; gap: 6px; font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .gd-pop-status i { width: 8px; height: 8px; border-radius: 50%; }
    .gd-pop-title { margin-top: 6px; padding-right: 10px; color: #0B1E07; font-size: 14px; font-weight: 700; line-height: 1.35; }
    .gd-pop-meta { margin-top: 2px; color: #6b7268; font-size: 11px; }
    .gd-pop-loc { display: flex; align-items: flex-start; gap: 5px; margin-top: 8px; color: #374151; font-size: 11px; }
    .gd-pop-loc svg { flex-shrink: 0; margin-top: 1px; color: #6b7268; }
    .gd-pop-cats { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 8px; }
    .gd-pop-cats span { padding: 2px 8px; background: #F5F7F1; border: 1px solid #E2E6DA; border-radius: 99px; color: #374151; font-size: 10px; }
    .gd-dash-map a.gd-pop-cta { display: block; margin-top: 12px; padding: 8px 12px; background: #0B1E07; color: #fff; border-radius: 2px; font-size: 11px; font-weight: 600; text-align: center; text-decoration: none; }
    .gd-dash-map a.gd-pop-cta:hover { background: #143009; color: #9BDB4D; }
</style>
