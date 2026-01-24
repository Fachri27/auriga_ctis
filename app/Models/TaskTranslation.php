<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskTranslation extends Model
{
    protected $fillable = [
        'task_id',
        'locale',
        'name',
        'description'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
