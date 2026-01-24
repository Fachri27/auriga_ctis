<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    protected $fillable = [
        'category_id',
        'order_no',
        'is_active'
    ];

    public function translations()
    {
        return $this->hasMany(ProcessTranslation::class);
    }

    public function translation($locale = 'id')
    {
        return $this->translations->where('locale', $locale)->first();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
