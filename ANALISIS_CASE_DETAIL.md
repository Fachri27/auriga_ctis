# Analisis Standar Case Tracking System - CaseDetail.php

## Ringkasan

File `CaseDetail.php` memiliki beberapa **masalah keamanan dan best practices** yang perlu diperbaiki untuk memenuhi standar case tracking system.

---

## ❌ MASALAH KRITIS

### 1. **SQL Injection Vulnerability** (SANGAT KRITIS)

**Lokasi:** Line 160 di method `publishCases()`

```php
'geom' => DB::raw("ST_GeomFromText('POINT({$lon} {$lat})')"),
```

**Masalah:** Nilai `$lon` dan `$lat` langsung di-interpolasi ke dalam query tanpa sanitization.

**Risiko:** Attacker bisa melakukan SQL injection jika bisa memanipulasi nilai latitude/longitude.

**Solusi:** Gunakan parameter binding atau validasi ketat:

```php
'geom' => DB::raw("ST_GeomFromText(?, 4326)", ["POINT({$lon} {$lat})"]),
// atau lebih baik lagi, validasi dulu:
if (!is_numeric($lat) || !is_numeric($lon)) {
    throw new \InvalidArgumentException('Invalid coordinates');
}
```

---

### 2. **Tidak Ada Authorization Checks** (KRITIS)

**Lokasi:** Method `approveTask()` dan `publishCases()`

**Masalah:**

-   Tidak ada pengecekan apakah user memiliki permission untuk approve task
-   Tidak ada pengecekan apakah user memiliki permission untuk publish case
-   Tidak ada pengecekan apakah task tersebut milik case yang sedang diakses

**Risiko:**

-   User yang tidak berwenang bisa approve task atau publish case
-   User bisa approve task dari case lain dengan memanipulasi `task_id`

**Solusi:** Tambahkan authorization checks:

```php
public function approveTask($task_id)
{
    // Check permission
    if (!auth()->user()->can('case.update')) {
        abort(403, 'Unauthorized');
    }

    // Verify task belongs to this case
    $task = DB::table('case_tasks')
        ->where('id', $task_id)
        ->where('case_id', $this->case_id)
        ->first();

    if (!$task) {
        session()->flash('error', 'Task not found or does not belong to this case.');
        return;
    }

    // Check if already approved
    if ($task->status === 'approved') {
        session()->flash('error', 'Task already approved.');
        return;
    }

    // ... rest of code
}
```

---

### 3. **Missing Data Validation** (PENTING)

**Masalah:**

-   Method `approveTask($task_id)` tidak memvalidasi input
-   Method `publishCases()` tidak memvalidasi kondisi sebelum publish
-   Tidak ada validasi bahwa case exists

**Solusi:** Tambahkan validasi:

```php
protected $rules = [
    'case_id' => 'required|exists:cases,id',
];

public function approveTask($task_id)
{
    $this->validate([
        'task_id' => 'required|exists:case_tasks,id',
    ]);
    // ...
}
```

---

### 4. **Missing Transaction in approveTask** (PENTING)

**Masalah:** Method `approveTask()` melakukan multiple database operations tanpa transaction.

**Risiko:** Jika salah satu operasi gagal, data bisa menjadi inconsistent.

**Solusi:** Wrap dalam transaction:

```php
public function approveTask($task_id)
{
    DB::beginTransaction();
    try {
        // ... all operations
        DB::commit();
    } catch (\Throwable $th) {
        DB::rollBack();
        session()->flash('error', 'Failed to approve task: ' . $th->getMessage());
        throw $th;
    }
}
```

---

### 5. **Incomplete Task Approval Logic** (PENTING)

**Masalah:**

-   Method `approveTask()` tidak mengupdate field `approved_by` dan `approved_at` yang ada di database schema
-   Tidak ada pengecekan apakah task sudah di-submit sebelum approve

**Solusi:**

```php
DB::table('case_tasks')
    ->where('id', $task_id)
    ->update([
        'status' => 'approved',
        'approved_by' => auth()->id(),
        'approved_at' => now(),
        'updated_at' => now(),
    ]);
```

---

### 6. **Missing Error Handling** (PENTING)

**Masalah:**

-   Method `approveTask()` tidak memiliki try-catch
-   Method `checkAutoComplateCase()` tidak memiliki error handling
-   Jika `$task` null di line 110, akan terjadi error

**Solusi:** Tambahkan error handling di semua method.

---

### 7. **No Case Existence Check** (PENTING)

**Masalah:** Method `mount()` tidak memverifikasi bahwa case dengan ID tersebut exists.

**Risiko:** Jika case tidak ada, akan terjadi error di view.

**Solusi:**

```php
public function mount($id)
{
    $this->case_id = $id;
    $this->loadCase();

    if (!$this->case) {
        abort(404, 'Case not found');
    }
}
```

---

## ⚠️ MASALAH MODERAT

### 8. **Using DB Facade Instead of Eloquent**

**Masalah:** Semua query menggunakan `DB::table()` instead of Eloquent models.

**Dampak:**

-   Lebih sulit maintenance
-   Tidak bisa menggunakan relationships
-   Tidak ada model events/observers

**Rekomendasi:** Gunakan Eloquent models yang sudah ada (`CaseModel`, `CaseTask`, dll).

---

### 9. **No Business Logic Validation in publishCases**

**Masalah:** Tidak ada pengecekan apakah semua required tasks sudah approved sebelum publish.

**Solusi:** Tambahkan validasi:

```php
// Check if all required tasks are approved
$pendingRequiredTasks = DB::table('case_tasks')
    ->join('tasks', 'tasks.id', '=', 'case_tasks.task_id')
    ->where('case_tasks.case_id', $this->case_id)
    ->where('tasks.is_required', true)
    ->where('case_tasks.status', '!=', 'approved')
    ->count();

if ($pendingRequiredTasks > 0) {
    session()->flash('error', 'Cannot publish case. All required tasks must be approved first.');
    return;
}
```

---

### 10. **No Duplicate Publish Check**

**Masalah:** Tidak ada pengecekan apakah case sudah pernah di-publish.

**Solusi:** Tambahkan check di awal method `publishCases()`.

---

### 11. **Hardcoded Locale**

**Masalah:** Locale 'id' di-hardcode di beberapa tempat.

**Rekomendasi:** Gunakan helper atau config untuk locale.

---

### 12. **Missing Logging/Audit Trail**

**Masalah:** Tidak ada logging untuk audit trail yang proper.

**Rekomendasi:** Gunakan Laravel's logging atau event system untuk track semua perubahan penting.

---

## ✅ YANG SUDAH BAIK

1. ✅ Menggunakan transaction di `publishCases()`
2. ✅ Ada timeline tracking untuk audit
3. ✅ Ada auto-complete logic ketika semua task approved
4. ✅ Menggunakan middleware auth di route level
5. ✅ Ada error handling di `publishCases()`

---

## 📋 REKOMENDASI PERBAIKAN PRIORITAS

### Prioritas 1 (SANGAT PENTING - Perbaiki Segera):

1. Fix SQL injection di line 160
2. Tambahkan authorization checks
3. Tambahkan validasi input
4. Fix missing `approved_by` dan `approved_at` fields

### Prioritas 2 (PENTING - Perbaiki Secepatnya):

5. Tambahkan transaction di `approveTask()`
6. Tambahkan error handling
7. Tambahkan case existence check
8. Tambahkan business logic validation untuk publish

### Prioritas 3 (REKOMENDASI - Perbaiki Saat Ada Waktu):

9. Migrate ke Eloquent models
10. Tambahkan proper logging/audit trail
11. Refactor hardcoded locale

---

## KESIMPULAN

**Status:** ❌ **BELUM MEMENUHI STANDAR**

File ini memiliki beberapa **vulnerability keamanan kritis** (SQL injection, missing authorization) yang harus segera diperbaiki sebelum production. Selain itu, ada beberapa best practices yang belum diikuti yang bisa menyebabkan masalah di kemudian hari.

**Rekomendasi:** Lakukan refactoring menyeluruh dengan fokus pada security dan data integrity sebelum deploy ke production.
