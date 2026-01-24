# Panduan Realistic Seeders - CTIS System

## 🎯 Overview

Seeder realistis berdasarkan laporan kehidupan nyata untuk sistem CTIS. Data yang dihasilkan mencerminkan skenario kasus nyata yang mungkin terjadi dalam sistem pelacakan kasus hukum/perkara.

---

## 📦 Seeders yang Tersedia

### 1. RealisticProvinceSeeder
- **Fungsi**: Menyediakan data provinsi dan kabupaten/kota di Indonesia
- **Data**: 5 provinsi, 20+ kabupaten/kota
- **Dependensi**: Tidak ada

### 2. RealisticCategorySeeder
- **Fungsi**: Menyediakan kategori kasus realistis
- **Data**: 6 kategori (Corruption, Environment, Violence, Fraud, Labor, Consumer)
- **Dependensi**: Tidak ada

### 3. RealisticReportSeeder
- **Fungsi**: Menyediakan laporan masyarakat realistis
- **Data**: 7 laporan dengan berbagai status dan kategori
- **Dependensi**: StatusSeeder, RealisticCategorySeeder

### 4. RealisticCaseSeeder
- **Fungsi**: Menyediakan kasus realistis yang terhubung dengan laporan
- **Data**: 4 kasus dengan berbagai tahapan dan timeline
- **Dependensi**: StatusSeeder, RealisticCategorySeeder, RealisticReportSeeder

---

## 🚀 Cara Menjalankan

### Opsi 1: Menjalankan Semua Seeder (Recommended)

```bash
# Pastikan database sudah fresh atau reset
php artisan migrate:fresh

# Jalankan semua seeder termasuk realistic seeders
php artisan db:seed
```

### Opsi 2: Menjalankan Seeder Spesifik

```bash
# Hanya provinsi
php artisan db:seed --class=RealisticProvinceSeeder

# Hanya kategori
php artisan db:seed --class=RealisticCategorySeeder

# Hanya laporan
php artisan db:seed --class=RealisticReportSeeder

# Hanya kasus
php artisan db:seed --class=RealisticCaseSeeder
```

### Opsi 3: Menggunakan DatabaseSeeder

Edit `database/seeders/DatabaseSeeder.php` dan pastikan urutan seeder:

```php
public function run(): void
{
    $this->call([
        // Basic seeders (required first)
        RolePermissionSeeder::class,
        UserSeeder::class,
        StatusSeeder::class,
        
        // Location data
        RealisticProvinceSeeder::class,
        
        // Category data
        RealisticCategorySeeder::class,
        
        // Process & Task (optional)
        ProcessSeeder::class,
        TaskSeeder::class,
        
        // Realistic data
        RealisticReportSeeder::class,   // Must be before RealisticCaseSeeder
        RealisticCaseSeeder::class,     // Depends on reports
        
        // Legacy seeders (optional)
        ReportSeeder::class,
        CaseSeeder::class,
    ]);
}
```

---

## 📋 Urutan Seeder yang Disarankan

1. **StatusSeeder** ⚠️ **WAJIB** (status diperlukan oleh semua seeder)
2. **RealisticProvinceSeeder** (data lokasi)
3. **RealisticCategorySeeder** (data kategori)
4. **RealisticReportSeeder** (data laporan)
5. **RealisticCaseSeeder** (data kasus - tergantung pada laporan)

---

## 📊 Data yang Dihasilkan

### Reports (7 items)
- ✅ RPT-2024-001: Korupsi Proyek Jalan (Verified)
- ✅ RPT-2024-002: Pencemaran Sungai (Converted)
- ✅ RPT-2024-003: Kekerasan Pekerja (Open)
- ✅ RPT-2024-004: Penipuan Investasi (Verified)
- ✅ RPT-2024-005: Pelanggaran Hak Pekerja (Open)
- ✅ RPT-2024-006: Pembakaran Hutan (Verified)
- ✅ RPT-2024-007: Laporan Ditolak (Rejected)

### Cases (4 items)
- ✅ CASE-2024-001: Korupsi Proyek Jalan (Investigation)
- ✅ CASE-2024-002: Pencemaran Sungai (Prosecution) - **Published**
- ✅ CASE-2024-003: Penipuan Investasi (Trial) - **Published**
- ✅ CASE-2024-004: Pembakaran Hutan (Executed) - **Published**

---

## ⚠️ Catatan Penting

### 1. Status "verified" dan "converted" Wajib Ada

Pastikan `StatusSeeder` sudah dijalankan dan memiliki status:
- `verified` - untuk report yang sudah diverifikasi
- `converted` - untuk report yang sudah dikonversi ke case

Status ini sudah ditambahkan di `StatusSeeder.php`.

### 2. Kategori Wajib Ada

Pastikan kategori berikut sudah ada:
- `corruption`
- `environment`
- `violence`
- `fraud`
- `labor`
- `consumer`

Kategori ini sudah ditambahkan di `RealisticCategorySeeder.php`.

### 3. Lokasi Geografis

Seeders menggunakan koordinat Jakarta area:
- Jakarta Selatan: -6.2297, 106.7985
- Jakarta Pusat: -6.2088, 106.8456

### 4. Timeline Entries

Case seeder menggunakan format action-based workflow:
- `Action: Convert to Case`
- `Action: Complete Investigation`
- `Action: Start Prosecution`
- `Action: Start Trial`
- `Action: Execute Verdict`

---

## 🔧 Troubleshooting

### Error: "Status 'verified' not found"
**Solusi**: Pastikan `StatusSeeder` sudah dijalankan terlebih dahulu.

### Error: "Category 'corruption' not found"
**Solusi**: Pastikan `RealisticCategorySeeder` sudah dijalankan terlebih dahulu.

### Error: "Report not found"
**Solusi**: Pastikan `RealisticReportSeeder` sudah dijalankan sebelum `RealisticCaseSeeder`.

### Error: "Foreign key constraint fails"
**Solusi**: Pastikan urutan seeder sesuai dengan dependensi:
1. StatusSeeder
2. RealisticProvinceSeeder
3. RealisticCategorySeeder
4. RealisticReportSeeder
5. RealisticCaseSeeder

---

## 📝 Customization

### Menambah Report Baru

Edit `database/seeders/RealisticReportSeeder.php`:

```php
$reports[] = [
    'report_code' => 'RPT-2024-008',
    'category_id' => $categoryEnvironment,
    'status_key' => 'open',
    'nama_lengkap' => 'Nama Lengkap',
    'nik' => '3175031501900008',
    // ... fields lainnya
];
```

### Menambah Case Baru

Edit `database/seeders/RealisticCaseSeeder.php`:

```php
$caseScenarios[] = [
    'report_id' => $reports->where('report_code', 'RPT-2024-XXX')->first()?->id,
    'category_slug' => 'environment',
    'case_number' => 'CASE-2024-XXX',
    // ... fields lainnya
];
```

---

## ✅ Testing

Setelah menjalankan seeders, uji:

1. **View Reports**
   ```bash
   # Akses route reports
   /reports
   ```

2. **View Cases**
   ```bash
   # Akses route cases
   /cases
   ```

3. **Test Action Buttons**
   - Verify Report
   - Convert to Case
   - Complete Investigation
   - Start Trial
   - etc.

4. **Test Timeline**
   - View timeline entries
   - Verify action-based entries
   - Check actor information

5. **Test Public Cases**
   - View published cases
   - Check geometry data
   - Test map display

---

## 📚 Related Files

- `REALISTIC_SEEDERS.md` - Dokumentasi lengkap seeders
- `SIMPLIFIED_INTERNAL_FLOW.md` - Action-based workflow docs
- `ACTION_MAPPING_REFERENCE.md` - Action mapping reference

---

**Last Updated**: 2025-01-XX  
**Version**: 1.0.0  
**Status**: ✅ Ready to Use

