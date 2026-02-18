<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseAction extends Model
{
    protected $fillable = [
        'case_id',
        'title',
        'description',
        'created_by',
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function files()
    {
        return $this->hasMany(CaseActionFile::class);
    }
}

