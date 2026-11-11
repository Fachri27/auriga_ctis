<?php

namespace App\Services;

use App\Mail\{NewCaseMail, NewCasesDigestMail};
use App\Models\CaseSubscription;
use Illuminate\Support\Facades\{DB, Mail};

/**
 * Publikasi kasus ke situs publik: dipakai halaman detail kasus (satu kasus) dan
 * aksi massal di daftar kasus.
 */
class CasePublisher
{
    /**
     * Jadikan kasus publik dan tampilkan titiknya di peta publik.
     *
     * @return bool false bila kasus sudah publik sebelumnya
     */
    public function publish(int $caseId): bool
    {
        return DB::transaction(function () use ($caseId) {
            $case = DB::table('cases')->where('id', $caseId)->lockForUpdate()->first()
                ?? throw new \RuntimeException("Case {$caseId} not found.");

            if ($case->is_public) {
                return false;
            }

            DB::table('cases')->where('id', $caseId)->update([
                'is_public' => true,
                'published_at' => $case->published_at ?? now(),
                'updated_at' => now(),
            ]);

            $this->syncGeometry($caseId);

            return true;
        });
    }

    /**
     * Sembunyikan kasus dari publik (geometri disembunyikan, tidak dihapus).
     *
     * @return bool false bila kasus memang belum publik
     */
    public function unpublish(int $caseId): bool
    {
        return DB::transaction(function () use ($caseId) {
            $isPublic = DB::table('cases')->where('id', $caseId)->lockForUpdate()->value('is_public');

            if (! $isPublic) {
                return false;
            }

            DB::table('cases')->where('id', $caseId)->update(['is_public' => 0, 'updated_at' => now()]);
            DB::table('case_geometries')->where('case_id', $caseId)->update(['is_public' => 0, 'updated_at' => now()]);

            DB::table('case_timelines')->insert([
                'case_id' => $caseId,
                'actor_id' => auth()->id(),
                'notes' => 'Case unpublished (removed from public view) by '.auth()->user()?->name.'.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        });
    }

    /**
     * Beri tahu subscriber tentang kasus yang baru dipublikasikan.
     *
     * Subscriber "semua kasus" (case_id NULL) menerima semua kasus, subscriber kasus tertentu
     * hanya kasusnya. Satu kasus → email kasus seperti biasa; lebih dari satu → satu email
     * ringkasan per subscriber, agar publikasi massal tidak membanjiri kotak masuk.
     *
     * @param  list<int>  $caseIds
     * @return int jumlah subscriber yang dikirimi email
     */
    public function notifySubscribers(array $caseIds): int
    {
        if ($caseIds === []) {
            return 0;
        }

        $cases = DB::table('cases')
            ->leftJoin('case_translations', function ($q) {
                $q->on('case_translations.case_id', '=', 'cases.id')->where('case_translations.locale', 'id');
            })
            ->whereIn('cases.id', $caseIds)
            ->select('cases.id', 'cases.case_number', 'case_translations.title', 'case_translations.description')
            ->get()
            ->keyBy('id');

        $perEmail = [];
        foreach (CaseSubscription::whereNull('case_id')->orWhereIn('case_id', $caseIds)->get(['email', 'case_id']) as $subscription) {
            foreach ($subscription->case_id ? [$subscription->case_id] : $caseIds as $id) {
                if (isset($cases[$id])) {
                    $perEmail[$subscription->email][$id] = $cases[$id];
                }
            }
        }

        foreach ($perEmail as $email => $subscribed) {
            $mail = count($subscribed) === 1
                ? new NewCaseMail((object) [...(array) reset($subscribed), 'locale' => app()->getLocale()])
                : new NewCasesDigestMail(array_values($subscribed));

            Mail::to($email)->queue($mail);
        }

        return count($perEmail);
    }

    /**
     * Simpan/perbarui titik kasus di case_geometries (dipakai peta publik).
     */
    private function syncGeometry(int $caseId): void
    {
        $case = DB::table('cases')
            ->leftJoin('categories', 'categories.id', '=', 'cases.category_id')
            ->where('cases.id', $caseId)
            ->select('cases.*', 'categories.slug as category_name')
            ->first();

        if (is_null($case->latitude) || is_null($case->longitude)) {
            return;
        }

        $lat = (float) $case->latitude;
        $lon = (float) $case->longitude;

        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            throw new \InvalidArgumentException('Invalid latitude / longitude.');
        }

        $wkt = sprintf('POINT(%.15f %.15f)', $lat, $lon);

        // Ambil semua translations — prioritas id, fallback ke yang pertama ada
        $translations = DB::table('case_translations')->where('case_id', $caseId)->get();
        $trans = $translations->firstWhere('locale', 'id') ?? $translations->first();

        $title = $trans?->title ?? ('Case '.$case->case_number);
        $plainDesc = $trans ? strip_tags($trans->description ?? '') : null;

        if (DB::table('case_geometries')->where('case_id', $caseId)->exists()) {
            DB::statement(
                'UPDATE case_geometries
                 SET geom             = ST_GeomFromText(?, 4326),
                     title            = ?,
                     category         = ?,
                     case_description = ?,
                     is_public        = 1,
                     updated_at       = ?
                 WHERE case_id        = ?',
                [$wkt, $title, $case->category_name ?? null, $plainDesc, now(), $caseId]
            );
        } else {
            DB::statement(
                'INSERT INTO case_geometries
                 (case_id, geom, title, category, case_description, status, is_public, created_at, updated_at)
                 VALUES (?, ST_GeomFromText(?, 4326), ?, ?, ?, ?, ?, ?, ?)',
                [$caseId, $wkt, $title, $case->category_name ?? null, $plainDesc, 'published', 1, now(), now()]
            );
        }
    }
}
