<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseTask extends Model
{
    protected $fillable = [
        'case_id',
        'task_id',
        'status',
        'submitted_at',
        'approved_at',
        'approved_by',
        'admin_notes',
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function requirements()
    {
        return $this->hasMany(CaseTaskRequirement::class);
    }
}