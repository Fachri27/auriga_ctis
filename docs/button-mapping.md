# Button → Aksi → Status (Ringkas)

Tabel ringkas tombol penting dan efeknya:

-   **Verifikasi Laporan** (CSO / Admin)

    -   Report: `new` → `verified`
    -   Timeline: "Laporan diverifikasi oleh X"

-   **Tolak Laporan** (CSO / Admin)

    -   Report: `new` → `rejected`
    -   Timeline: "Laporan ditolak: alasan"

-   **Buat Case** (CSO / Admin)

    -   Report: `verified` → `converted`
    -   Case: dibuat dengan status `investigation`
    -   **Auto**: generate tugas sesuai template kategori (CaseTaskGenerator)
    -   Timeline: "Case dibuat dari report" + "Auto-generated X task(s)"

-   **Mulai Investigasi** (Investigator / Lead)

    -   Case: `open` → `investigation`
    -   Timeline: "Investigasi dimulai oleh Y"

-   **Mulai Tugas** (Assigned Investigator)

    -   Task: `pending` → `in_progress`

-   **Kirim Tugas** (Assigned Investigator)

    -   Task: `in_progress` → `submitted`

-   **Setujui Tugas** (Supervisor / Admin)

    -   Task: `submitted` → `approved`
    -   Jika semua tugas wajib disetujui → Case otomatis ke `ready_to_publish` (opsional)

-   **Publikasikan Kasus** (Admin)

    -   Case: `ready_to_publish` → `published`
    -   Set `is_public = true`

-   Close Case (Admin / Legal)
    -   Case: `published`/`investigating` → `closed`

Notes:

-   The "Convert to Case" button now has a tooltip explaining that it will auto-generate tasks.
-   Admins can edit templates (process/task/requirements) via seeders or by implementing a UI in admin panel.
