<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskRequirement extends Model
{
    protected $casts = [
        'is_required' => 'boolean',
    ];
    protected $fillable = [
        'task_id',
        'name',
        'field_type',
        'is_required',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
