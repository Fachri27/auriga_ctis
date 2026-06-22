<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    protected $fillable = [];

    public function translations()
    {
        return $this->hasMany(AboutPageTranslation::class);
    }

    public function translation($locale = 'id')
    {
        return $this->translations->where('locale', $locale)->first();
    }
}
