<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseActor extends Model
{
    protected $fillable = [
        'case_id',
        'type',
        'name',
        'description'
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class);
    }
}
