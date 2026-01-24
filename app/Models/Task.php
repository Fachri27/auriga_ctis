<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['process_id', 'due_days', 'is_required'];
    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function translations()
    {
        return $this->hasMany(TaskTranslation::class);
    }

    public function translation($locale = 'id')
    {
        return $this->translations->where('locale', $locale)->first();
    }

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function requirements()
    {
        return $this->hasMany(TaskRequirement::class);
    }
}
