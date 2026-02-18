<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseActionFile extends Model
{
    protected $fillable = [
        'case_action_id',
        'file_path',
        'file_name',
        'mime_type',
    ];

    public function action()
    {
        return $this->belongsTo(CaseAction::class, 'case_action_id');
    }
}

