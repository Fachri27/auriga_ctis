<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    protected $fillable = [
        'slug',
        'type',
        'image',
        'published_at',
        'status',
        'link',
        'user_id',
        'category_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function translation()
    {
        return $this->hasMany(ArtikelTranslation::class);
    }
    
}
