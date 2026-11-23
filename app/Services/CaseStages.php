<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Tahapan kasus di halaman detail publik. Tahapannya mengikuti kualifikasi kasus (CaseModel::KUALIFIKASI):
 * - pidana (juga bila kualifikasi belum ditentukan): penyelidikan, penyidikan, penuntutan, persidangan, vonis;
 * - perdata: gugatan, pembuktian, vonis;
 * - administrasi: sanksi.
 * Semua jalur diawali tahapan Terbuka dan Dipublikasikan. Keterangan tiap tahapan bisa diganti per kasus
 * (case_translations.keterangan_tahapan, diisi di form CMS kasus atau dari tabel "Tahapan Kasus" dokumen impor).
 *
 * Posisi kasus ditentukan dari status kasus (tabel statuses). Status yang nomornya melewati tahapan terakhir
 * (mis. selesai, ditutup) menandai semua tahapan sudah dilalui; status yang bukan bagian dari jalurnya
 * tidak menandai tahapan mana pun.
 */
class CaseStages
{
    /**
     * Nomor urut tahapan per status kasus, per jalur. Nomor yang tidak punya tahapan tampil (mis. 2 = verified)
     * tetap dipakai agar tahapan sebelumnya tertandai sudah dilalui.
     */
    private const STATUS_STEPS = [
        'pidana' => [
            'open' => 1, 'verified' => 2, 'published' => 3, 'penyelidikan' => 4, 'investigation' => 4,
            'penyidikan' => 5, 'prosecution' => 6, 'trial' => 7, 'vonis' => 8, 'berkekuatan-hukum-tetap' => 9,
            'executed' => 10, 'completed' => 11, 'closed' => 12, 'rejected' => 12, 'converted' => 12,
        ],
        'perdata' => [
            'open' => 1, 'verified' => 2, 'published' => 3, 'gugatan' => 4, 'pembuktian' => 5, 'trial' => 5,
            'vonis' => 6, 'berkekuatan-hukum-tetap' => 7, 'executed' => 7, 'completed' => 7,
            'closed' => 8, 'rejected' => 8, 'converted' => 8,
        ],
        'administrasi' => [
            'open' => 1, 'verified' => 2, 'published' => 3, 'sanksi' => 4, 'executed' => 5, 'completed' => 5,
            'closed' => 6, 'rejected' => 6, 'converted' => 6,
        ],
    ];

    /**
     * Label baris tabel "Tahapan Kasus" di dokumen kasus (slug) → key tahapan. Templat dokumen memakai daftar
     * tahapan pidana untuk semua kualifikasi, jadi beberapa label dipetakan ulang per jalur (DOCUMENT_ALIASES).
     */
    private const DOCUMENT_STAGES = [
        'terbuka' => 'terbuka', 'dipublikasi' => 'dipublikasikan', 'dipublikasikan' => 'dipublikasikan',
        'penyelidikan' => 'penyelidikan', 'penyidikan' => 'penyidikan', 'penuntutan' => 'penuntutan',
        'persidangan' => 'persidangan', 'gugatan' => 'gugatan', 'pembuktian' => 'pembuktian',
        'vonis' => 'vonis', 'putusan' => 'vonis', 'sanksi' => 'sanksi',
        'hukum-tetap' => 'hukum-tetap', 'berkekuatan-hukum-tetap' => 'hukum-tetap',
    ];

    /** Tahapan dokumen yang di jalur tertentu tampil sebagai tahapan lain (mis. putusan sanksi administratif). */
    private const DOCUMENT_ALIASES = [
        'perdata' => ['persidangan' => 'pembuktian'],
        'administrasi' => ['vonis' => 'sanksi'],
    ];

    /** Tahapan proses → status kasus (tabel statuses). Terbuka & Dipublikasikan tidak mengubah status. */
    private const STAGE_STATUS = [
        'penyelidikan' => 'penyelidikan', 'penyidikan' => 'penyidikan', 'penuntutan' => 'prosecution',
        'persidangan' => 'trial', 'gugatan' => 'gugatan', 'pembuktian' => 'pembuktian', 'vonis' => 'vonis',
        'sanksi' => 'sanksi', 'hukum-tetap' => 'berkekuatan-hukum-tetap',
    ];

    public static function isDocumentStage(string $label): bool
    {
        return isset(self::DOCUMENT_STAGES[Str::slug($label)]);
    }

    /**
     * Keterangan per tahapan dan status kasus dari tabel "Tahapan Kasus" di dokumen kasus. Status = tahapan
     * terjauh yang keterangannya terisi. Tahapan yang tidak tampil di jalurnya (mis. Hukum Tetap, atau Penyidikan
     * pada kasus administrasi) dikembalikan di 'skipped'; Hukum Tetap tetap menandai status berkekuatan hukum tetap.
     *
     * @param  list<array{label: string, text: string}>  $rows  baris tabel yang keterangannya terisi
     * @return array{notes: array<string, string>, status: ?string, skipped: list<string>}
     */
    public static function fromDocument(?string $kualifikasi, array $rows): array
    {
        $track = isset(self::STATUS_STEPS[$kualifikasi ?? '']) ? $kualifikasi : 'pidana';
        $shown = array_column(self::steps($track), 0);
        $notes = [];
        $skipped = [];
        $status = null;

        foreach ($rows as ['label' => $label, 'text' => $text]) {
            $stage = self::DOCUMENT_STAGES[Str::slug($label)] ?? null;
            $stage = self::DOCUMENT_ALIASES[$track][$stage] ?? $stage;

            if (in_array($stage, $shown, true)) {
                $notes[$stage] = isset($notes[$stage]) ? $notes[$stage]."\n".$text : $text;
            } else {
                $skipped[] = $label;
            }

            $candidate = self::STAGE_STATUS[$stage] ?? null;
            $step = self::STATUS_STEPS[$track][$candidate] ?? null;
            if ($step !== null && ($status === null || $step > self::STATUS_STEPS[$track][$status])) {
                $status = $candidate;
            }
        }

        return ['notes' => $notes, 'status' => $status, 'skipped' => $skipped];
    }

    /**
     * @param  array<string, string>|null  $notes  keterangan custom kasus ini per tahapan (key tahapan => teks),
     *                                             menggantikan keterangan standar; tahapan yang kosong tetap standar
     * @param  string|null  $locale  bahasa label & keterangan standar (bawaan: bahasa aktif)
     * @return array{steps: array<int, array{key: string, label: string, desc: string, custom: bool}>, current: int}
     */
    public static function for(?string $kualifikasi, ?string $statusKey, ?array $notes = [], ?string $locale = null): array
    {
        $track = isset(self::STATUS_STEPS[$kualifikasi ?? '']) ? $kualifikasi : 'pidana';

        $steps = [];
        foreach (self::steps($track) as $step => [$key, $label, $desc]) {
            $note = trim((string) ($notes[$key] ?? ''));
            $steps[$step] = [
                'key' => $key,
                'label' => __($label, [], $locale),
                'desc' => $note !== '' ? $note : __($desc, [], $locale),
                'custom' => $note !== '',
            ];
        }

        return [
            'steps' => $steps,
            // Key dinormalkan karena ada data lama berbentuk "Berkekuatan hukum tetap" (lihat Status::group()).
            'current' => self::STATUS_STEPS[$track][Str::slug((string) $statusKey)] ?? 0,
        ];
    }

    /**
     * Tahapan tampil per jalur: nomor urut => [key tahapan, label, keterangan standar] (teks ID, diterjemahkan lewat __()).
     * Key tahapan dipakai menyimpan keterangan custom; key yang sama di jalur berbeda (vonis) berbagi keterangan.
     *
     * @return array<int, array{0: string, 1: string, 2: string}>
     */
    private static function steps(string $track): array
    {
        $start = [
            1 => ['terbuka', 'Terbuka', 'Laporan atau kasus baru masuk dan telah didaftarkan ke sistem.'],
            3 => ['dipublikasikan', 'Dipublikasikan', 'Informasi kasus telah dipublikasikan dan dapat diakses oleh publik.'],
        ];

        return $start + match ($track) {
            'perdata' => [
                4 => ['gugatan', 'Gugatan', 'Gugatan perdata didaftarkan ke pengadilan oleh pihak yang dirugikan, warga, organisasi lingkungan, atau pemerintah.'],
                5 => ['pembuktian', 'Pembuktian', 'Para pihak mengajukan bukti, saksi, dan ahli di persidangan untuk menguatkan dalil masing-masing.'],
                6 => ['vonis', 'Vonis', 'Hakim memutus gugatan, misalnya menghukum tergugat membayar ganti rugi atau memulihkan lingkungan.'],
            ],
            'administrasi' => [
                4 => ['sanksi', 'Sanksi', 'Pemerintah menjatuhkan sanksi administratif, misalnya teguran, paksaan pemerintah, pembekuan, atau pencabutan izin.'],
            ],
            default => [
                4 => ['penyelidikan', 'Penyelidikan', 'Penegak hukum sedang mengumpulkan bukti awal untuk menentukan apakah ada tindak pidana.'],
                5 => ['penyidikan', 'Penyidikan', 'Bukti sudah cukup. Penyidik resmi mengusut tersangka dan mengumpulkan alat bukti.'],
                6 => ['penuntutan', 'Penuntutan', 'Jaksa menyusun surat dakwaan dan mempersiapkan kasus untuk dibawa ke pengadilan.'],
                7 => ['persidangan', 'Persidangan', 'Kasus sedang disidangkan di pengadilan. Hakim memeriksa bukti dan keterangan saksi.'],
                8 => ['vonis', 'Vonis', 'Hakim telah menjatuhkan putusan bersalah atau tidak bersalah kepada terdakwa.'],
            ],
        };
    }
}
