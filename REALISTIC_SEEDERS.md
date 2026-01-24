# Realistic Seeders - CTIS System

## 📋 Overview

Seeder realistis berdasarkan laporan kehidupan nyata untuk sistem CTIS (Case Tracking Information System). Data yang dihasilkan mencerminkan skenario kasus nyata yang mungkin terjadi dalam sistem pelacakan kasus hukum/perkara.

---

## 🗂️ Seeders Created

### 1. RealisticProvinceSeeder

Seeder untuk provinsi dan kabupaten/kota di Indonesia dengan data realistis.

**Features:**
- ✅ 5 provinsi utama (DKI Jakarta, Jawa Barat, Jawa Tengah, Jawa Timur, Bali)
- ✅ 20+ kabupaten/kota dengan kode dan nama lengkap
- ✅ Data berdasarkan struktur administratif Indonesia

**Usage:**
```bash
php artisan db:seed --class=RealisticProvinceSeeder
```

---

### 2. RealisticCategorySeeder

Seeder untuk kategori kasus realistis berdasarkan jenis kasus yang umum terjadi.

**Categories:**
- **Corruption** (Korupsi) - Kasus korupsi, penyalahgunaan wewenang
- **Environment** (Lingkungan Hidup) - Pencemaran lingkungan, illegal logging
- **Violence** (Kekerasan) - Kekerasan fisik, psikis, pelanggaran HAM
- **Fraud** (Penipuan) - Penipuan, investasi bodong
- **Labor** (Ketenagakerjaan) - Pelanggaran hak pekerja, upah tidak dibayar
- **Consumer** (Perlindungan Konsumen) - Produk tidak layak, jasa menipu

**Features:**
- ✅ Multilingual (Indonesian & English)
- ✅ Icons untuk setiap kategori
- ✅ Deskripsi lengkap

**Usage:**
```bash
php artisan db:seed --class=RealisticCategorySeeder
```

---

### 3. RealisticReportSeeder

Seeder untuk laporan masyarakat realistis berdasarkan skenario kehidupan nyata.

**Report Scenarios:**

1. **RPT-2024-001** - Korupsi Proyek Pembangunan Jalan
   - Status: Verified
   - Category: Corruption
   - Lokasi: Jakarta Selatan
   - Deskripsi: Dugaan korupsi dengan indikasi mark-up harga material dan penyimpangan anggaran Rp 5 miliar

2. **RPT-2024-002** - Pencemaran Sungai Ciliwung
   - Status: Converted (sudah jadi case)
   - Category: Environment
   - Lokasi: Jakarta Selatan
   - Deskripsi: Pencemaran sungai oleh pabrik tekstil

3. **RPT-2024-003** - Kekerasan terhadap Pekerja
   - Status: Open (belum diverifikasi)
   - Category: Violence
   - Lokasi: Jakarta Selatan
   - Deskripsi: Kekerasan terhadap pekerja di proyek pembangunan apartemen

4. **RPT-2024-004** - Penipuan Investasi Emas
   - Status: Verified
   - Category: Fraud
   - Lokasi: Jakarta Selatan
   - Deskripsi: Penipuan investasi bodong dengan modus investasi emas, kerugian Rp 50 juta

5. **RPT-2024-005** - Pelanggaran Hak Pekerja
   - Status: Open (belum diverifikasi)
   - Category: Labor
   - Lokasi: Jakarta Pusat
   - Deskripsi: Upah tidak dibayar selama 3 bulan, 50 pekerja terkena dampak

6. **RPT-2024-006** - Pembakaran Hutan Ilegal
   - Status: Verified
   - Category: Environment
   - Lokasi: Jakarta Selatan
   - Deskripsi: Pembakaran hutan ilegal untuk pembukaan lahan, 10 hektar terbakar

7. **RPT-2024-007** - Laporan Ditolak
   - Status: Rejected
   - Category: Corruption
   - Lokasi: Jakarta Selatan
   - Deskripsi: Laporan ditolak karena bukti tidak cukup

**Features:**
- ✅ Data pelapor lengkap (NIK, alamat, kontak)
- ✅ Lokasi geografis (latitude/longitude)
- ✅ Bukti dokumentasi (foto, video, dokumen)
- ✅ Multilingual descriptions
- ✅ Status berbeda-beda (open, verified, converted, rejected)
- ✅ Tanggal realistis (spread across months)

**Usage:**
```bash
php artisan db:seed --class=RealisticReportSeeder
```

---

### 4. RealisticCaseSeeder

Seeder untuk kasus realistis yang terhubung dengan laporan real-life, menggunakan action-based workflow.

**Case Scenarios:**

1. **CASE-2024-001** - Kasus Korupsi Proyek Pembangunan Jalan
   - Status: Investigation
   - Category: Corruption
   - Source: RPT-2024-001
   - Timeline: 3 timeline entries
   - Actors: Kepala Dinas PUPR, PT Jaya Konstruksi
   - Description: Mark-up harga material hingga 30%, penyimpangan Rp 5 miliar

2. **CASE-2024-002** - Kasus Pencemaran Sungai Ciliwung
   - Status: Prosecution
   - Category: Environment
   - Source: RPT-2024-002
   - Timeline: 4 timeline entries (including actions)
   - Actors: PT Tekstil Maju, Dinas Lingkungan Hidup
   - Description: Pabrik membuang limbah berbahaya tanpa pengolahan
   - **Published**: ✅ Public case

3. **CASE-2024-003** - Kasus Penipuan Investasi Emas
   - Status: Trial
   - Category: Fraud
   - Source: RPT-2024-004
   - Timeline: 6 timeline entries (full workflow)
   - Actors: PT Berkah Emas, Korban (50 orang)
   - Description: Investasi bodong dengan kerugian Rp 500 juta
   - **Published**: ✅ Public case

4. **CASE-2024-004** - Kasus Pembakaran Hutan Ilegal
   - Status: Executed
   - Category: Environment
   - Source: RPT-2024-006
   - Timeline: 6 timeline entries (completed workflow)
   - Actors: Petani Lokal (pelaku)
   - Description: Pembakaran 10 hektar hutan, vonis 2 tahun penjara + denda
   - **Published**: ✅ Public case

**Features:**
- ✅ Terhubung dengan laporan real-life
- ✅ Action-based timeline entries
- ✅ Status berbeda (investigation, prosecution, trial, executed)
- ✅ Actors lengkap (government, corporate, citizen)
- ✅ Multilingual (Indonesian & English)
- ✅ Geometry data untuk public cases
- ✅ Tanggal realistis dengan timeline progression

**Timeline Entries Include:**
- Action: Convert to Case
- Action: Complete Investigation
- Action: Start Prosecution
- Action: Start Trial
- Action: Execute Verdict
- Regular timeline notes

**Usage:**
```bash
php artisan db:seed --class=RealisticCaseSeeder
```

---

## 🚀 Quick Start

### Run All Seeders

```bash
# Seed all realistic data
php artisan db:seed --class=RealisticProvinceSeeder
php artisan db:seed --class=RealisticCategorySeeder
php artisan db:seed --class=RealisticReportSeeder
php artisan db:seed --class=RealisticCaseSeeder
```

### Run Specific Seeder

```bash
# Seed only reports
php artisan db:seed --class=RealisticReportSeeder

# Seed only cases
php artisan db:seed --class=RealisticCaseSeeder
```

### Recommended Order

1. **StatusSeeder** (must be first - statuses are required)
2. **RealisticProvinceSeeder** (location data)
3. **RealisticCategorySeeder** (category data)
4. **RealisticReportSeeder** (reports data)
5. **RealisticCaseSeeder** (cases data - depends on reports)

---

## 📊 Data Summary

### Reports Generated

- ✅ **7 Reports** dengan berbagai status
- ✅ **Multiple Categories** (Corruption, Environment, Violence, Fraud, Labor)
- ✅ **Various Statuses** (Open, Verified, Converted, Rejected)
- ✅ **Complete Reporter Data** (NIK, address, contact)
- ✅ **Geographic Locations** (Jakarta area)
- ✅ **Evidence Documentation** (photos, videos, documents)

### Cases Generated

- ✅ **4 Cases** dengan berbagai tahapan
- ✅ **Full Workflow** (Investigation → Prosecution → Trial → Executed)
- ✅ **Action-Based Timeline** (menggunakan action workflow)
- ✅ **Multiple Actors** (Government, Corporate, Citizen)
- ✅ **Public Cases** (3 cases published with geometry)
- ✅ **Linked to Reports** (semua case terhubung ke report)

---

## 📝 Notes

### Status Mapping

- **Report Statuses**: `open`, `verified`, `converted`, `rejected`
- **Case Statuses**: `investigation`, `prosecution`, `trial`, `executed`, `closed`

### Action-Based Workflow

Timeline entries menggunakan format action-based:
- `Action: Convert to Case`
- `Action: Complete Investigation`
- `Action: Start Prosecution`
- `Action: Start Trial`
- `Action: Execute Verdict`

### Realistic Data

Data yang dihasilkan mencerminkan:
- ✅ Skenario kasus nyata yang umum terjadi
- ✅ Detail lengkap dan realistis
- ✅ Progression timeline yang masuk akal
- ✅ Lokasi geografis Indonesia
- ✅ Format nama dan identitas Indonesia

---

## 🔧 Customization

### Adding New Reports

Edit `database/seeders/RealisticReportSeeder.php` dan tambahkan item baru ke array `$reports`:

```php
[
    'report_code' => 'RPT-2024-008',
    'category_id' => $categoryEnvironment,
    'status_key' => 'open',
    'nama_lengkap' => 'Nama Lengkap',
    // ... fields lainnya
],
```

### Adding New Cases

Edit `database/seeders/RealisticCaseSeeder.php` dan tambahkan item baru ke array `$caseScenarios`:

```php
[
    'report_id' => $reports->where('report_code', 'RPT-2024-XXX')->first()?->id,
    'category_slug' => 'environment',
    'case_number' => 'CASE-2024-XXX',
    // ... fields lainnya
],
```

---

## ✅ Testing

Setelah menjalankan seeders, Anda dapat:

1. **Test Report Flow**
   - View reports dengan berbagai status
   - Test verify/reject actions
   - Test convert to case action

2. **Test Case Flow**
   - View cases dengan berbagai status
   - Test action buttons (Complete Investigation, Start Trial, etc.)
   - View timeline entries
   - View actors

3. **Test Public Cases**
   - View public cases with geometry
   - Test map display
   - Test published status

---

## 📚 Related Documentation

- `SIMPLIFIED_INTERNAL_FLOW.md` - Action-based workflow documentation
- `ACTION_MAPPING_REFERENCE.md` - Action mapping reference
- `REFACTORING_CASE_STATUS_SEPARATION.md` - Status separation refactoring

---

**Last Updated**: 2025-01-XX  
**Version**: 1.0.0  
**Status**: ✅ Production Ready

