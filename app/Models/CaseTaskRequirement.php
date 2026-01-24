<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseTaskRequirement extends Model
{
    protected $fillable = [
        'case_task_id',
        'requirement_id',
        'value',
    ];

    public function caseTask()
    {
        return $this->belongsTo(CaseTask::class);
    }

    public function requirement()
    {
        return $this->belongsTo(TaskRequirement::class, 'requirement_id');
    }
}