<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtikelTranslation extends Model
{
    protected $fillable = [
        'artikel_id',
        'locale',
        'title',
        'excerpt',
        'content',
    ];

    public function artikel()
    {
        return $this->hasMany(Artikel::class);
    }
}
