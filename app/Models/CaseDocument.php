<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseDocument extends Model
{
    protected $fillable = [
        'case_id',
        'process_id',
        'uploaded_by',
        'file_path',
        'mime',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
