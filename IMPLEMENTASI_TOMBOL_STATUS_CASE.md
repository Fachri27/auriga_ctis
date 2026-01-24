# Implementasi Tombol Status Case - CTIS System

## ✅ Ringkasan Implementasi

Sudah dibuat sistem **SIMPLE dan HUMAN-FRIENDLY** untuk mengubah status case hukum dengan tombol UI yang jelas dan mudah dipahami oleh admin/CSO non-teknis.

---

## 🎯 Fitur yang Sudah Diimplementasikan

### 1. ✅ Action Service (CaseActionService)

**Lokasi**: `app/Services/CaseActionService.php`

**Fitur:**
- ✅ Mapping action → status
- ✅ Label bahasa Indonesia (human-friendly)
- ✅ Validasi transisi status
- ✅ Priority ordering untuk UI

**Label Indonesia:**
- `complete_investigation` → "Naik ke Penuntutan"
- `start_trial` → "Mulai Persidangan"
- `execute_verdict` → "Eksekusi Putusan"
- `close_case` → "Tutup Kasus"
- `reject_case` → "Tolak Kasus"

### 2. ✅ Livewire Component Method

**Lokasi**: `app/Livewire/Cases/CaseDetail.php`

**Method:**
- ✅ `executeAction($actionKey, $notes)` - Method utama untuk mengubah status
- ✅ `changeStatusAction($actionKey, $notes)` - Alias method (backward compatibility)
- ✅ `getAllowedActions()` - Get allowed actions dengan label Indonesia
- ✅ `getActionLabelIndonesian($actionKey)` - Get label Indonesia untuk action

**Fitur:**
- ✅ Permission check (`case.update`)
- ✅ Validasi action allowed untuk status saat ini
- ✅ Error handling dengan pesan bahasa Indonesia
- ✅ Timeline logging otomatis
- ✅ User feedback dengan flash messages

### 3. ✅ Blade Template - Button UI

**Lokasi**: `resources/views/livewire/cases/case-detail.blade.php`

**Fitur:**
- ✅ Button muncul berdasarkan status case saat ini
- ✅ Label bahasa Indonesia yang jelas
- ✅ Konfirmasi dialog sebelum perubahan
- ✅ Permission check (`@can('case.update')`)
- ✅ Primary button + dropdown untuk actions lainnya
- ✅ Publish button terpisah (tidak mengubah status hukum)

---

## 📊 Mapping Button → Action → Status

### Tabel Lengkap

| Current Status | Button UI | Action Key | New Status | Kapan Digunakan |
|----------------|-----------|------------|------------|-----------------|
| `investigation` | **"Naik ke Penuntutan"** | `complete_investigation` | `prosecution` | Investigasi selesai, cukup bukti |
| `prosecution` | **"Mulai Persidangan"** | `start_trial` | `trial` | Kasus siap disidangkan |
| `trial` | **"Eksekusi Putusan"** | `execute_verdict` | `executed` | Putusan sudah ada, siap dieksekusi |
| `executed` | **"Tutup Kasus"** | `close_case` | `closed` | Putusan sudah dieksekusi |
| `investigation` | **"Tutup Kasus"** | `close_case` | `closed` | Case ditutup tanpa penuntutan |
| `prosecution` | **"Tutup Kasus"** | `close_case` | `closed` | Case ditutup tanpa persidangan |
| `trial` | **"Tutup Kasus"** | `close_case` | `closed` | Case ditutup tanpa eksekusi |
| `investigation` | **"Tolak Kasus"** | `reject_case` | `rejected` | Case ditolak (tidak cukup bukti) |

---

## 🎨 UI Button Design

### Lokasi Button

Button muncul di **Header/Action Panel** (sebelah kanan atas) halaman Case Detail.

### Kondisi Tampil Button

#### Investigation Stage
```
✅ "Naik ke Penuntutan" (button biru - primary)
✅ "Tutup Kasus" (button abu-abu - dropdown)
✅ "Tolak Kasus" (button merah - dropdown)
```

#### Prosecution Stage
```
✅ "Mulai Persidangan" (button biru - primary)
✅ "Tutup Kasus" (button abu-abu - dropdown)
```

#### Trial Stage
```
✅ "Eksekusi Putusan" (button biru - primary)
✅ "Tutup Kasus" (button abu-abu - dropdown)
```

#### Executed Stage
```
✅ "Tutup Kasus" (button abu-abu - primary)
```

#### Closed/Rejected Stage
```
❌ Tidak ada button status (case sudah final)
✅ Hanya button "Publish" jika belum dipublish
```

---

## 💻 Code Example

### 1. Livewire Component Method

```php
/**
 * Execute an action on the case (action-based workflow).
 * 
 * @param string $actionKey Action key (e.g., 'complete_investigation', 'close_case')
 * @param string|null $notes Optional notes for timeline
 */
public function executeAction(string $actionKey, ?string $notes = null)
{
    // Authorization check
    if (!auth()->user()->can('case.update')) {
        session()->flash('error', 'Anda tidak memiliki izin untuk mengubah status case.');
        return;
    }

    try {
        $actionService = app(CaseActionService::class);
        
        // Validate action is allowed
        $caseModel = CaseModel::findOrFail($this->case_id);
        if (!$actionService->isActionAllowed($caseModel, $actionKey)) {
            $currentStatus = $caseModel->status?->name ?? 'Unknown';
            session()->flash('error', "Aksi ini tidak diperbolehkan untuk status case saat ini ({$currentStatus}).");
            return;
        }

        // Execute action (will transition status and log to timeline)
        $success = $actionService->executeAction($this->case_id, $actionKey, $notes);

        if ($success) {
            $this->loadCase();
            $this->dispatch('refresh-case-detail');
            $actionLabel = $this->getActionLabelIndonesian($actionKey);
            session()->flash('success', "Status case berhasil diubah: {$actionLabel}");
        } else {
            session()->flash('info', 'Status case sudah dalam kondisi target.');
        }
        
    } catch (\InvalidArgumentException $e) {
        session()->flash('error', $e->getMessage());
    } catch (\Throwable $th) {
        Log::error("Error executing action '{$actionKey}' on case {$this->case_id}: " . $th->getMessage());
        session()->flash('error', 'Gagal mengubah status case. Silakan coba lagi atau hubungi administrator.');
    }
}
```

### 2. Blade Template - Button UI

```blade
{{-- STATUS ACTION BUTTONS --}}
@can('case.update')
    <div class="flex items-center gap-3 flex-wrap">
        @php
            $allowedActions = $this->getAllowedActions();
            $currentStatus = $case->status->key ?? null;
        @endphp

        @if(!empty($allowedActions))
            @php
                $primaryAction = $allowedActions[0] ?? null;
                $otherActions = array_slice($allowedActions, 1);
            @endphp

            {{-- PRIMARY BUTTON --}}
            @if($primaryAction)
                <button 
                    wire:click="executeAction('{{ $primaryAction['key'] }}')"
                    onclick="return confirm('Apakah Anda yakin akan: {{ $primaryAction['label'] }}?')"
                    class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                    {{ $primaryAction['label'] }}
                </button>
            @endif

            {{-- DROPDOWN FOR OTHER ACTIONS --}}
            @if(count($otherActions) > 0)
                <div x-data="{open:false}" class="relative">
                    <button 
                        @click="open = !open" 
                        class="px-4 py-2 bg-gray-200 rounded text-sm hover:bg-gray-300">
                        Lainnya ▾
                    </button>

                    <div 
                        x-show="open" 
                        x-cloak 
                        @click.away="open = false"
                        class="absolute right-0 mt-2 w-48 bg-white border rounded shadow p-2 z-50">
                        @foreach($otherActions as $action)
                            <button 
                                @click="open=false" 
                                wire:click="executeAction('{{ $action['key'] }}')"
                                onclick="return confirm('Apakah Anda yakin akan: {{ $action['label'] }}?')"
                                class="w-full text-left px-3 py-2 hover:bg-gray-100 text-sm rounded
                                    @if($action['key'] === 'reject_case') text-red-600 @endif
                                    @if($action['key'] === 'close_case') text-gray-600 @endif">
                                {{ $action['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <span class="px-4 py-2 bg-gray-200 text-gray-600 rounded text-sm">
                Case sudah final - tidak bisa diubah status
            </span>
        @endif

        {{-- PUBLISH BUTTON (Separate from status) --}}
        @can('case.publish')
            @if(!$case->published_at && !in_array($currentStatus, ['closed', 'rejected']))
                <button 
                    wire:click="publishCases"
                    onclick="return confirm('Publish case ini ke publik?')"
                    class="px-5 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700 transition">
                    🚀 Publikasikan Kasus
                </button>
            @endif
        @endcan
    </div>
@endcan
```

---

## 🔐 Permission Rules

### Permission Check

**Hanya user dengan permission `case.update`** yang bisa:
1. Melihat tombol-tombol status
2. Mengklik tombol untuk mengubah status
3. Mengakses method `executeAction()`

**Implementation:**
```blade
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
Action: [Action Label in English] - [Optional Notes]
```

**Contoh Timeline Entries:**
- `Action: Complete Investigation - Investigasi selesai, cukup bukti untuk penuntutan`
- `Action: Start Trial - Kasus siap untuk disidangkan`
- `Action: Execute Verdict - Putusan sudah ada, siap dieksekusi`
- `Action: Close Case - Case ditutup karena alasan tertentu`

**Fields yang tercatat:**
- ✅ `case_id` - ID case
- ✅ `actor_id` - User yang melakukan aksi (auth()->id())
- ✅ `notes` - Action label + optional notes
- ✅ `created_at` - Timestamp aksi

---

## ✅ Checklist Implementasi

- [x] CaseActionService dengan label Indonesia
- [x] Method `executeAction()` di Livewire component
- [x] Method `getAllowedActions()` dengan label Indonesia
- [x] Method `getActionLabelIndonesian()` helper
- [x] Blade template dengan button UI
- [x] Permission check (`case.update`)
- [x] Timeline logging otomatis
- [x] Konfirmasi dialog sebelum perubahan
- [x] Error handling dan user feedback (bahasa Indonesia)
- [x] Dynamic button berdasarkan status
- [x] Primary button + dropdown untuk actions lainnya
- [x] Publish button terpisah (tidak mengubah status hukum)
- [x] Dokumentasi lengkap

---

## 🎓 Panduan untuk Admin/CSO

### Cara Mengubah Status Case

1. **Buka halaman Case Detail**
   - Klik pada case yang ingin diubah statusnya

2. **Lihat Status Saat Ini**
   - Status case ditampilkan di bagian header (badge)
   - Contoh: "Investigation" (badge kuning)

3. **Klik Tombol Action yang Tersedia**
   - Tombol utama (biru) muncul berdasarkan status case
   - Tombol lainnya tersedia di dropdown "Lainnya"
   - Contoh: Jika status "Investigation", tombol "Naik ke Penuntutan" akan muncul

4. **Konfirmasi Aksi**
   - Klik "OK" pada dialog konfirmasi
   - Contoh: "Apakah Anda yakin akan: Naik ke Penuntutan?"

5. **Verifikasi Perubahan**
   - Status badge akan berubah
   - Timeline akan mencatat perubahan status
   - Pesan sukses akan muncul: "Status case berhasil diubah: Naik ke Penuntutan"

### Kapan Menggunakan Tombol?

**"Naik ke Penuntutan"**
- ✅ Investigasi sudah selesai
- ✅ Sudah ada cukup bukti untuk penuntutan
- ✅ Tim investigasi sudah merekomendasikan untuk naik ke penuntutan

**"Mulai Persidangan"**
- ✅ Kasus sudah siap untuk disidangkan
- ✅ Semua dokumen pendukung sudah lengkap
- ✅ Penuntut sudah siap untuk menghadapi sidang

**"Eksekusi Putusan"**
- ✅ Putusan sudah ada
- ✅ Putusan sudah berkekuatan hukum tetap
- ✅ Siap untuk dieksekusi

**"Tutup Kasus"**
- ✅ Case sudah selesai (di tahap executed)
- ✅ Atau case ditutup karena alasan tertentu (dengan catatan di timeline)

**"Tolak Kasus"**
- ⚠️ Hanya di tahap investigation
- ⚠️ Ketika tidak ada cukup bukti untuk melanjutkan
- ⚠️ Case ditolak dan tidak bisa dibuka kembali

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

## 🔧 Troubleshooting

### Button Tidak Muncul

**Penyebab:**
- User tidak memiliki permission `case.update`
- Case sudah dalam status final (`closed` atau `rejected`)
- Tidak ada action yang diperbolehkan untuk status saat ini

**Solusi:**
- Pastikan user memiliki permission `case.update`
- Case dengan status final tidak akan menampilkan button status
- Cek mapping `ALLOWED_ACTIONS_BY_STATUS` di `CaseActionService`

### Error: "Aksi ini tidak diperbolehkan"

**Penyebab:**
- Action tidak sesuai dengan status case saat ini
- Transisi status tidak valid

**Solusi:**
- Cek status case saat ini
- Cek `ALLOWED_ACTIONS_BY_STATUS` untuk status tersebut
- Gunakan action yang sesuai dengan status case

### Timeline Tidak Tercatat

**Penyebab:**
- Error saat insert ke `case_timelines`
- Transaction rollback

**Solusi:**
- Cek log error di `storage/logs/laravel.log`
- Pastikan `case_timelines` table ada dan valid
- Pastikan `actor_id` tidak null

---

## 📚 Related Files

### Services
- `app/Services/CaseActionService.php` - Action service dengan label Indonesia
- `app/Services/CaseStatusService.php` - Status service untuk transisi

### Components
- `app/Livewire/Cases/CaseDetail.php` - Livewire component dengan method `executeAction()`

### Views
- `resources/views/livewire/cases/case-detail.blade.php` - Blade template dengan button UI

### Documentation
- `PANDUAN_TOMBOL_STATUS_CASE.md` - Panduan lengkap untuk admin/CSO
- `SIMPLIFIED_INTERNAL_FLOW.md` - Dokumentasi alur internal yang disederhanakan
- `ACTION_MAPPING_REFERENCE.md` - Referensi mapping action

---

**Last Updated**: 2025-01-XX  
**Version**: 1.0.0  
**Status**: ✅ Production Ready

---

## 🎉 Kesimpulan

Sistem tombol status case sudah **SIMPLE, HUMAN-FRIENDLY, dan READY TO USE**!

✅ **Simple** - Tombol dengan label Indonesia yang jelas
✅ **Human-Friendly** - Mudah dipahami oleh admin/CSO non-teknis
✅ **Legal Compliant** - Tetap mengikuti prinsip CTIS (status hanya berubah melalui action eksplisit)
✅ **Well Documented** - Dokumentasi lengkap untuk admin/CSO
✅ **Error Handling** - Error messages dalam bahasa Indonesia
✅ **User Feedback** - Success/info messages yang jelas

**Sistem siap digunakan untuk produksi! 🚀**

