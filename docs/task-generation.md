# Auto Task Generation (CTIS)

Ringkasan:

-   Saat `Convert to Case` (dari laporan terverifikasi), sistem akan otomatis membuat `case_tasks` berdasarkan _process_ dan _task templates_ untuk kategori case.
-   Penanggung jawab (owner) tugas belum otomatis diassign; admin dapat menugaskan manual dari UI.

Aturan implementasi:

-   Templates didefinisikan di tabel `processes`, `tasks`, `task_requirements`.
-   Seeder `RealisticCorruptionTemplatesSeeder` menambahkan template realistis untuk kategori `corruption`.
-   Migration `add_columns_to_case_tasks` menambahkan: `process_id`, `assigned_to`, `due_date` pada `case_tasks`.
-   Service `App\Services\CaseTaskGenerator::generate($caseId, $categoryId)` melakukan:
    -   buat `case_tasks` dengan `process_id`, `due_date` (berdasarkan `tasks.due_days`), status `pending`.
    -   buat `case_task_requirements` untuk setiap requirement template.

Tips admin:

-   Untuk edit template: buka UI kategori → proses → tugas → requirement.
-   Jika ingin assign otomatis, implementasikan logika assign di `CaseTaskGenerator` (assign by role or round-robin).
