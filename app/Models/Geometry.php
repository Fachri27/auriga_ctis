<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Geometry extends Model
{
    protected $table = 'case_geometries';
    protected $fillable = [
        'case_id',
        'title',
        'category',
        'geom',
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class);
    }
}
