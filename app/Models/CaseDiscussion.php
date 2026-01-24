<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseDiscussion extends Model
{
    protected $fillable = [
        'case_id',
        'user_id',
        'message',
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
