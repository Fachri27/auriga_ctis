# CMS Language: Panduan Bahasa Sederhana (untuk Admin & CSO)

Tujuan: gunakan istilah yang mudah dipahami oleh pengguna non-teknis.

Ringkasan istilah yang dipakai di UI:

-   Laporan

    -   `Verifikasi Laporan` → klik ketika bukti minimal ada dan laporan valid
    -   `Tolak Laporan` → klik jika laporan tidak memenuhi syarat
    -   `Buat Case` → konversi laporan yang terverifikasi menjadi kasus internal

-   Case

    -   `Selesaikan Investigasi` → ketika investigasi lengkap dan siap dikirim ke penuntutan
    -   `Ajukan ke Kejaksaan` → mulai proses penuntutan
    -   `Mulai Sidang` → proses sidang
    -   `Jalankan Putusan` → jalankan putusan pengadilan
    -   `Tutup Kasus` → menutup kasus (final)
    -   `Publikasikan Kasus` → buat ringkasan publik terlihat di situs

-   Tugas (Task)
    -   `Lihat` → buka detail tugas dan requirement
    -   `Setujui` → supervisor menandai tugas selesai dan disetujui
    -   `Setujui` hanya muncul untuk pengguna yang berwenang

Prinsip penulisan:

-   Jangan gunakan istilah hukum panjang di tombol; gunakan kata kerja sederhana (Verifikasi, Buat, Publikasikan, Tutup).
-   Tambahkan tooltip kecil pada tiap tombol yang menjelaskan: siapa yang boleh klik, dan efeknya.
-   Timeline harus mencatat aksi secara ringkas: "Laporan diverifikasi oleh X", "Case dibuat dari laporan RPT-... oleh Y".

Jika ingin saya, saya bisa:

-   Menambahkan file translate (i18n) untuk semua label agar mudah diubah dari satu file.
-   Memasukkan tooltips ke lebih banyak tombol yang belum memiliki penjelasan.
