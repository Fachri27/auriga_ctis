# Panduan Tombol Status Case - CTIS System

## 🎯 Tujuan

Menyediakan cara **SIMPLE dan HUMAN-FRIENDLY** untuk mengubah status case hukum tanpa melanggar prinsip legal CTIS.

---

## 📋 Kapan Status Case Harus Berubah?

### ✅ Status Case Berubah Ketika:

1. **Fase hukum case berubah**
   - Case berpindah dari satu tahapan hukum ke tahapan berikutnya
   - Contoh: Investigation → Prosecution → Trial → Executed → Closed

2. **Ada keputusan legal yang eksplisit**
   - Keputusan untuk melanjutkan ke tahap berikutnya
   - Keputusan untuk menutup case
   - Keputusan untuk menolak case

3. **User mengklik tombol action secara MANUAL**
   - Status TIDAK berubah otomatis
   - Status TIDAK berubah karena task selesai
   - Status TIDAK berubah karena case dipublish

### ❌ Status Case TIDAK Berubah Ketika:

- ❌ Task selesai (task adalah checklist internal)
- ❌ Case dipublish (publishing adalah visibility, bukan status hukum)
- ❌ Semua dokumen sudah diupload (dokumen adalah bukti, bukan status)
- ❌ Timeline entry dibuat (timeline adalah log, bukan trigger)

---

## 👥 Siapa yang Boleh Mengubah Status?

**Hanya user dengan permission `case.update`** yang bisa:
- Melihat tombol-tombol status
- Mengklik tombol untuk mengubah status
- Melakukan perubahan status case

---

## 🗺️ Mapping Button → Action → Status

### Tabel Mapping Lengkap

| Current Status | Button UI | Action Key | New Status | Kapan Digunakan |
|----------------|-----------|------------|------------|-----------------|
| `investigation` | "Naik ke Penuntutan" | `complete_investigation` | `prosecution` | Investigasi selesai, cukup bukti untuk penuntutan |
| `prosecution` | "Mulai Persidangan" | `start_trial` | `trial` | Kasus siap untuk disidangkan |
| `trial` | "Eksekusi Putusan" | `execute_verdict` | `executed` | Putusan sudah ada, siap dieksekusi |
| `executed` | "Tutup Kasus" | `close_case` | `closed` | Putusan sudah dieksekusi, case selesai |
| `investigation` | "Tutup Kasus" | `close_case` | `closed` | Case ditutup tanpa penuntutan (alasan tertentu) |
| `prosecution` | "Tutup Kasus" | `close_case` | `closed` | Case ditutup tanpa persidangan (alasan tertentu) |
| `trial` | "Tutup Kasus" | `close_case` | `closed` | Case ditutup tanpa eksekusi (alasan tertentu) |
| `investigation` | "Tolak Kasus" | `reject_case` | `rejected` | Case ditolak karena tidak cukup bukti |

### Status Final (Tidak Ada Tombol)

Status berikut adalah **FINAL** - tidak ada tombol untuk mengubah:
- ✅ `closed` - Case sudah selesai
- ✅ `rejected` - Case sudah ditolak

---

## 🎨 Desain UI Button

### Lokasi Button

Button status case muncul di halaman **Case Detail**, di bagian **Header/Action Panel** (sebelah kanan atas).

### Kondisi Tampil Button

1. **Case status = `investigation`**
   ```
   ✅ "Naik ke Penuntutan" (button biru)
   ✅ "Tutup Kasus" (button abu-abu)
   ✅ "Tolak Kasus" (button merah)
   ```

2. **Case status = `prosecution`**
   ```
   ✅ "Mulai Persidangan" (button biru)
   ✅ "Tutup Kasus" (button abu-abu)
   ❌ Button lain disembunyikan
   ```

3. **Case status = `trial`**
   ```
   ✅ "Eksekusi Putusan" (button biru)
   ✅ "Tutup Kasus" (button abu-abu)
   ❌ Button lain disembunyikan
   ```

4. **Case status = `executed`**
   ```
   ✅ "Tutup Kasus" (button abu-abu)
   ❌ Button lain disembunyikan
   ```

5. **Case status = `closed` atau `rejected`**
   ```
   ❌ Tidak ada button status (case sudah final)
   ✅ Hanya button "Publish" jika belum dipublish
   ```

### Style Button

```html
<!-- Button Primary (Perubahan Normal) -->
<button class="btn btn-primary">Naik ke Penuntutan</button>

<!-- Button Secondary (Tutup Case) -->
<button class="btn btn-secondary">Tutup Kasus</button>

<!-- Button Danger (Tolak Case) -->
<button class="btn btn-danger">Tolak Kasus</button>
```

---

## 💻 Implementasi Code

### 1. Livewire Component Method

**File: `app/Livewire/Cases/CaseDetail.php`**

```php
/**
 * Change case status via action button.
 * 
 * @param string $actionKey Action key (e.g., 'complete_investigation')
 */
public function changeStatusAction(string $actionKey)
{
    // Authorization check
    if (!auth()->user()->can('case.update')) {
        session()->flash('error', 'Anda tidak memiliki izin untuk mengubah status case.');
        return;
    }

    try {
        $actionService = app(CaseActionService::class);
        
        // Validate action is allowed for current status
        $caseModel = CaseModel::findOrFail($this->case_id);
        if (!$actionService->isActionAllowed($caseModel, $actionKey)) {
            session()->flash('error', 'Aksi ini tidak diperbolehkan untuk status case saat ini.');
            return;
        }

        // Execute action (will transition status and log to timeline)
        $success = $actionService->executeAction(
            $this->case_id, 
            $actionKey,
            null, // notes - bisa ditambahkan di UI nanti
            auth()->id()
        );

        if ($success) {
            $this->loadCase();
            $this->dispatch('refresh-case-detail');
            
            $actionLabel = $actionService->getActionLabel($actionKey);
            session()->flash('success', "Status case berhasil diubah: {$actionLabel}");
        } else {
            session()->flash('info', 'Status case sudah dalam kondisi target.');
        }
        
    } catch (\InvalidArgumentException $e) {
        session()->flash('error', $e->getMessage());
    } catch (\Throwable $th) {
        Log::error("Error changing case status: " . $th->getMessage());
        session()->flash('error', 'Gagal mengubah status case. Silakan coba lagi.');
    }
}
```

### 2. Blade Template - Button UI

**File: `resources/views/livewire/cases/case-detail.blade.php`**

```blade
{{-- STATUS ACTION BUTTONS --}}
@can('case.update')
    <div class="flex items-center gap-3 flex-wrap">
        @php
            $currentStatus = $case->status->key ?? null;
            $allowedActions = $this->getAllowedActions();
        @endphp

        {{-- INVESTIGATION STAGE --}}
        @if($currentStatus === 'investigation')
            <button 
                wire:click="changeStatusAction('complete_investigation')"
                onclick="return confirm('Apakah Anda yakin akan memindahkan case ke tahap Penuntutan?')"
                class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                📋 Naik ke Penuntutan
            </button>
            
            <button 
                wire:click="changeStatusAction('close_case')"
                onclick="return confirm('Apakah Anda yakin akan menutup case ini?')"
                class="px-5 py-2 bg-gray-600 text-white rounded-lg shadow hover:bg-gray-700 transition">
                🔒 Tutup Kasus
            </button>
            
            <button 
                wire:click="changeStatusAction('reject_case')"
                onclick="return confirm('Apakah Anda yakin akan menolak case ini? Case yang ditolak tidak bisa dibuka kembali.')"
                class="px-5 py-2 bg-red-600 text-white rounded-lg shadow hover:bg-red-700 transition">
                ❌ Tolak Kasus
            </button>
        @endif

        {{-- PROSECUTION STAGE --}}
        @if($currentStatus === 'prosecution')
            <button 
                wire:click="changeStatusAction('start_trial')"
                onclick="return confirm('Apakah Anda yakin akan memindahkan case ke tahap Persidangan?')"
                class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                ⚖️ Mulai Persidangan
            </button>
            
            <button 
                wire:click="changeStatusAction('close_case')"
                onclick="return confirm('Apakah Anda yakin akan menutup case ini?')"
                class="px-5 py-2 bg-gray-600 text-white rounded-lg shadow hover:bg-gray-700 transition">
                🔒 Tutup Kasus
            </button>
        @endif

        {{-- TRIAL STAGE --}}
        @if($currentStatus === 'trial')
            <button 
                wire:click="changeStatusAction('execute_verdict')"
                onclick="return confirm('Apakah Anda yakin akan memindahkan case ke tahap Eksekusi Putusan?')"
                class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                ✅ Eksekusi Putusan
            </button>
            
            <button 
                wire:click="changeStatusAction('close_case')"
                onclick="return confirm('Apakah Anda yakin akan menutup case ini?')"
                class="px-5 py-2 bg-gray-600 text-white rounded-lg shadow hover:bg-gray-700 transition">
                🔒 Tutup Kasus
            </button>
        @endif

        {{-- EXECUTED STAGE --}}
        @if($currentStatus === 'executed')
            <button 
                wire:click="changeStatusAction('close_case')"
                onclick="return confirm('Apakah Anda yakin akan menutup case ini? Case yang ditutup tidak bisa dibuka kembali.')"
                class="px-5 py-2 bg-gray-600 text-white rounded-lg shadow hover:bg-gray-700 transition">
                🔒 Tutup Kasus
            </button>
        @endif

        {{-- CLOSED/REJECTED STAGE --}}
        @if(in_array($currentStatus, ['closed', 'rejected']))
            <span class="px-4 py-2 bg-gray-200 text-gray-600 rounded-lg text-sm">
                Case sudah final - tidak bisa diubah status
            </span>
        @endif

        {{-- PUBLISH BUTTON (Separate from status) --}}
        @if(!$case->published_at && !in_array($currentStatus, ['closed', 'rejected']))
            <button 
                wire:click="publishCases"
                onclick="return confirm('Publish case ini ke publik?')"
                class="px-5 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition">
                🚀 Publish Case
            </button>
        @endif
    </div>
@endcan
```

### 3. Alternative: Dynamic Button dari Action Service

**File: `resources/views/livewire/cases/case-detail.blade.php`** (Versi Dinamis)

```blade
{{-- DYNAMIC STATUS ACTION BUTTONS --}}
@can('case.update')
    <div class="flex items-center gap-3 flex-wrap">
        @php
            $allowedActions = $this->getAllowedActions();
        @endphp

        @foreach($allowedActions as $action)
            @php
                $buttonClass = match($action['key']) {
                    'reject_case' => 'bg-red-600 hover:bg-red-700',
                    'close_case' => 'bg-gray-600 hover:bg-gray-700',
                    default => 'bg-blue-600 hover:bg-blue-700',
                };
                
                $buttonIcon = match($action['key']) {
                    'complete_investigation' => '📋',
                    'start_trial' => '⚖️',
                    'execute_verdict' => '✅',
                    'close_case' => '🔒',
                    'reject_case' => '❌',
                    default => '🔄',
                };
            @endphp

            <button 
                wire:click="changeStatusAction('{{ $action['key'] }}')"
                onclick="return confirm('Apakah Anda yakin akan menjalankan aksi: {{ $action['label'] }}?')"
                class="px-5 py-2 {{ $buttonClass }} text-white rounded-lg shadow transition">
                {{ $buttonIcon }} {{ $action['label'] }}
            </button>
        @endforeach

        {{-- PUBLISH BUTTON (Separate from status) --}}
        @if(!$case->published_at)
            <button 
                wire:click="publishCases"
                onclick="return confirm('Publish case ini ke publik?')"
                class="px-5 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition">
                🚀 Publish Case
            </button>
        @endif
    </div>
@endcan
```

---

## 🔐 Permission Rules

### Permission Check

**Hanya user dengan permission `case.update`** yang bisa:
1. Melihat tombol-tombol status
2. Mengklik tombol untuk mengubah status
3. Mengakses method `changeStatusAction()`

**Implementasi:**
```php
@can('case.update')
    {{-- Button Status --}}
@endcan
```

### Role yang Umumnya Memiliki Permission

- ✅ **Admin** - Full access
- ✅ **CSO (Case Service Officer)** - Dapat mengubah status case
- ❌ **Viewer** - Hanya bisa melihat, tidak bisa mengubah status
- ❌ **Reporter** - Hanya bisa membuat laporan, tidak bisa mengubah status case

---

## 📝 Timeline Logging

Setiap perubahan status **OTOMATIS** dicatat ke `case_timelines` dengan format:

```
Action: [Action Label] - [Optional Notes]
```

**Contoh Timeline Entries:**
- `Action: Complete Investigation - Investigasi selesai, cukup bukti untuk penuntutan`
- `Action: Start Trial - Kasus siap untuk disidangkan`
- `Action: Execute Verdict - Putusan sudah ada, siap dieksekusi`
- `Action: Close Case - Case ditutup karena alasan tertentu`

**Fields yang tercatat:**
- ✅ `case_id` - ID case
- ✅ `actor_id` - User yang melakukan aksi
- ✅ `notes` - Action label + optional notes
- ✅ `created_at` - Timestamp aksi

---

## ⚠️ Aturan Penting

### ✅ BOLEH

1. ✅ Mengubah status case melalui tombol action
2. ✅ Menutup case kapan saja (jika diperbolehkan)
3. ✅ Menolak case di tahap investigation
4. ✅ Melakukan transisi status sesuai alur hukum

### ❌ TIDAK BOLEH

1. ❌ Mengubah status otomatis karena task selesai
2. ❌ Mengubah status otomatis karena case dipublish
3. ❌ Mengubah status dari `closed` atau `rejected` (final status)
4. ❌ Melewati tahapan hukum (mis: investigation langsung ke trial)
5. ❌ Mengubah status tanpa permission `case.update`

---

## 🎓 Panduan untuk Admin/CSO

### Cara Mengubah Status Case

1. **Buka halaman Case Detail**
   - Klik pada case yang ingin diubah statusnya

2. **Lihat Status Saat Ini**
   - Status case ditampilkan di bagian header (badge)

3. **Klik Tombol Action yang Tersedia**
   - Tombol yang muncul sesuai dengan status case saat ini
   - Contoh: Jika status "Investigation", tombol "Naik ke Penuntutan" akan muncul

4. **Konfirmasi Aksi**
   - Klik "OK" pada dialog konfirmasi
   - Status akan berubah dan tercatat di timeline

5. **Verifikasi Perubahan**
   - Status badge akan berubah
   - Timeline akan mencatat perubahan status
   - Pesan sukses akan muncul

### Kapan Menggunakan Tombol?

**"Naik ke Penuntutan"**
- Ketika investigasi sudah selesai
- Sudah ada cukup bukti untuk penuntutan
- Tim investigasi sudah merekomendasikan untuk naik ke penuntutan

**"Mulai Persidangan"**
- Ketika kasus sudah siap untuk disidangkan
- Semua dokumen pendukung sudah lengkap
- Penuntut sudah siap untuk menghadapi sidang

**"Eksekusi Putusan"**
- Ketika putusan sudah ada
- Putusan sudah berkekuatan hukum tetap
- Siap untuk dieksekusi

**"Tutup Kasus"**
- Ketika case sudah selesai (di tahap executed)
- Atau case ditutup karena alasan tertentu (dengan catatan di timeline)

**"Tolak Kasus"**
- Hanya di tahap investigation
- Ketika tidak ada cukup bukti untuk melanjutkan
- Case ditolak dan tidak bisa dibuka kembali

---

## ✅ Checklist Implementasi

- [x] CaseActionService sudah dibuat
- [x] Method `changeStatusAction()` di Livewire component
- [x] Blade template dengan button UI
- [x] Permission check (`case.update`)
- [x] Timeline logging otomatis
- [x] Konfirmasi dialog sebelum perubahan
- [x] Error handling dan user feedback
- [x] Dynamic button berdasarkan status
- [x] Dokumentasi lengkap

---

**Last Updated**: 2025-01-XX  
**Version**: 1.0.0  
**Status**: ✅ Production Ready

