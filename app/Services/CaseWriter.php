<?php

namespace App\Services;

use App\Models\{CaseModel, CaseTranslation};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Logika simpan kasus yang dipakai bersama oleh form CMS (CaseForm) dan impor dokumen (CaseImporter).
 */
class CaseWriter
{
    /**
     * Buat atau perbarui kasus beserta terjemahannya.
     *
     * Nomor kasus hanya dibuat untuk kasus baru; kasus yang diedit tetap memakai
     * nomornya karena kode di URL publik (/id/kasus/{judul}-{kode}) berasal dari nomor ini.
     *
     * @param  array<string, mixed>  $attributes  kolom tabel cases
     * @param  array<string, array<string, mixed>>  $translations  per locale: title, description, perkembangan, ...
     */
    public function save(?CaseModel $case, array $attributes, array $translations): CaseModel
    {
        $case ??= new CaseModel;
        unset($attributes['case_number']);

        if (! $case->exists) {
            $attributes['case_number'] = $this->generateCaseNumber();
        }

        $case->fill($attributes)->save();
        $case->refresh();

        foreach ($translations as $locale => $fields) {
            if (array_key_exists('description', $fields)) {
                $fields['summary'] = (string) Str::of($fields['description'] ?? '')->stripTags()->limit(300, '...');
            }

            CaseTranslation::query()->updateOrCreate(
                ['case_id' => $case->id, 'locale' => $locale],
                $fields,
            );
        }

        // Auto-generate tasks for this category (if templates exist)
        try {
            CaseTaskGenerator::generate($case->id, $case->category_ids ?? []);
        } catch (\Throwable $e) {
            // don't block case creation; log and continue
            Log::error('Case task generation failed: '.$e->getMessage());
        }

        return $case;
    }

    public function generateCaseNumber(): string
    {
        do {
            $number = 'CASE-'.strtoupper(Str::random(5));
        } while (CaseModel::where('case_number', $number)->exists());

        return $number;
    }
}
