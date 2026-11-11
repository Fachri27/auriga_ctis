<?php

/*
|--------------------------------------------------------------------------
| Berita penegakan hukum lingkungan & SDA (artisan news:pull)
|--------------------------------------------------------------------------
|
| Berita dari media lain ditarik berkala dan langsung tampil sebagai artikel
| "eksternal" (judul, ringkasan, sumber, tautan ke berita asli). Hanya berita
| yang memuat kata penegakan hukum DAN kata isu lingkungan/SDA yang diambil.
|
*/

return [

    // Kata pencarian berita. Dicari lewat Bing News (utama: tautan langsung ke berita asli dan
    // bergambar) dan Google News (pelengkap: tautan lewat pengalihan Google, tanpa gambar).
    'search_queries' => [
        '"penegakan hukum" lingkungan',
        'gakkum (kehutanan OR lingkungan OR KLHK)',
        '("tambang ilegal" OR "pembalakan liar" OR "perambahan hutan") (tersangka OR vonis OR ditangkap)',
        '(karhutla OR "kebakaran hutan") (tersangka OR gugatan OR korporasi)',
        '("perdagangan satwa" OR "satwa dilindungi") (tersangka OR vonis OR ditangkap)',
        'korupsi (tambang OR sawit OR "sumber daya alam")',
    ],

    // Mesin pencari yang dipakai; hapus 'google' agar sama sekali tidak bergantung pada Google.
    'search_engines' => ['bing', 'google'],

    // RSS media (menyertakan gambar & ringkasan); isinya berita umum, jadi disaring kata kunci di bawah.
    // Feed media hanya memuat berita beberapa jam terakhir, karena itu news:pull dijalankan tiap jam.
    'rss_feeds' => [
        'Mongabay Indonesia' => 'https://mongabay.co.id/feed/',
        'ANTARA News' => 'https://www.antaranews.com/rss/terkini.xml',
        'ANTARA News (Hukum)' => 'https://www.antaranews.com/rss/hukum.xml',
        'detikNews' => 'https://news.detik.com/rss',
        'CNN Indonesia' => 'https://www.cnnindonesia.com/nasional/rss',
        'Liputan6.com' => 'https://feed.liputan6.com/rss/news',
        'Okezone' => 'https://sindikasi.okezone.com/index.php/rss/1/RSS2.0',
        'SINDOnews' => 'https://www.sindonews.com/feed',
        'Republika' => 'https://www.republika.co.id/rss',
        'Tempo' => 'https://rss.tempo.co/nasional',
        // Feed tanpa gambar; gambarnya diambil dari halaman berita aslinya (NewsEnricher).
        'Media Indonesia' => 'https://mediaindonesia.com/feed/all',
    ],

    // Berita diambil bila JUDULNYA memuat kata isu lingkungan/SDA (temanya jadi pokok berita, bukan sekadar
    // disebut di ringkasan), judul atau ringkasannya memuat kata penegakan hukum, dan judulnya tidak memuat
    // kata di exclude_keywords.
    'enforcement_keywords' => [
        'penegakan hukum', 'gakkum', 'pidana', 'tersangka', 'terdakwa', 'vonis', 'divonis', 'putusan', 'pengadilan',
        'gugatan', 'digugat', 'menggugat', 'penyidik', 'penyidikan', 'penyelidikan', 'ditangkap', 'penangkapan', 'ditahan',
        'sanksi', 'disita', 'penyitaan', 'kpk', 'kejaksaan', 'kejagung', 'kejati', 'kejari', 'bareskrim', 'polda', 'polres',
        'hakim', 'jaksa', 'praperadilan', 'dakwaan', 'dituntut', 'penertiban', 'satgas pkh', 'disegel', 'penyegelan', 'ilegal',
        'diringkus', 'diamankan', 'dilaporkan', 'kriminalisasi', 'OTT',
    ],

    // "lingkungan", "kayu", "emas", dan "pencemaran" tidak dipakai sendirian karena sering bermakna lain ("lingkungan
    // sekolah", "dipukul kayu", "toko emas", "pencemaran nama baik"); yang dipakai frasa yang jelas bertema lingkungan.
    // Kata berhuruf besar semua (mis. 'PETI' = pertambangan tanpa izin) dicocokkan persis, agar "peti kemas" tidak ikut.
    'environment_keywords' => [
        'lingkungan hidup', 'hukum lingkungan', 'pidana lingkungan', 'kejahatan lingkungan', 'kerusakan lingkungan',
        'perusakan lingkungan', 'perusak lingkungan', 'pencemaran lingkungan', 'pelanggaran lingkungan', 'izin lingkungan',
        'persetujuan lingkungan', 'pemulihan lingkungan', 'dampak lingkungan', 'kasus lingkungan', 'sengketa lingkungan',
        'gugatan lingkungan', 'aktivis lingkungan', 'pejuang lingkungan', 'pembela lingkungan', 'hutan', 'kehutanan',
        'kayu ilegal', 'kayu bulat', 'kayu olahan', 'batang kayu', 'kubik kayu', 'kayu merbau', 'kayu ulin', 'pembalakan',
        'illegal logging', 'penebangan liar', 'perambahan', 'deforestasi', 'tambang', 'pertambangan', 'penambangan',
        'penambang', 'PETI', 'nikel', 'batu bara', 'batubara', 'tambang emas', 'emas ilegal', 'timah', 'bauksit', 'zirkon',
        'pasir laut', 'galian c', 'sawit', 'perkebunan', 'satwa', 'harimau', 'gajah', 'orangutan', 'trenggiling', 'tenggiling',
        'penyu', 'cenderawasih', 'karhutla', 'kebakaran hutan', 'kebakaran lahan', 'limbah', 'tercemar', 'pencemaran sungai',
        'pencemaran laut', 'pencemaran udara', 'pencemaran air', 'pencemaran tanah', 'pencemaran limbah',
        'mangrove', 'perikanan', 'pencurian ikan', 'illegal fishing', 'bom ikan', 'kapal ikan', 'gambut', 'konservasi',
        'taman nasional', 'suaka margasatwa', 'cagar alam', 'sumber daya alam', 'sda', 'klhk', 'kemenhut', 'esdm',
        'reklamasi', 'amdal', 'tata kelola lingkungan', 'walhi', 'jatam', 'auriga nusantara',
    ],

    // Judul yang memuat kata ini tidak diambil meski memuat kata di atas: topiknya di luar isu lingkungan/SDA
    // (mis. sosialisasi antikorupsi untuk pelajar, judi online, narkoba, dana desa), atau kata di atas bermakna
    // lain ("tali tambang", "Gajah Mada").
    'exclude_keywords' => [
        'pelajar', 'siswa', 'judol', 'judi online', 'narkoba', 'dana desa', 'gajah mada', 'tali tambang', 'tarik tambang',
    ],

    // Halaman publik hanya menampilkan berita bergambar; berita tanpa gambar menyusul tampil
    // setelah gambarnya ditemukan (NewsEnricher). Set false untuk menampilkan semua dengan gambar cadangan.
    'require_image' => true,

    // Batas berita baru per penarikan, dan umur berita maksimal.
    'max_per_run' => 60,
    'max_age_days' => 14,
];
