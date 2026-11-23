<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseTranslation extends Model
{
    protected $fillable = [
        'case_id',
        'locale',
        'title',
        'cover_caption',
        'summary',
        'description',
        'perkembangan',
        'pembelajaran',
        'dugaan_permasalahan',
        'keterangan_tahapan',
    ];

    protected $casts = [
        // Keterangan custom per tahapan: key tahapan => teks (lihat App\Services\CaseStages).
        'keterangan_tahapan' => 'array',
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class);
    }
}
