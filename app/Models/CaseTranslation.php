<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseTranslation extends Model
{
    protected $fillable = [
        'case_id',
        'locale',
        'title',
        'summary',
        'description'
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class);
    }
}
