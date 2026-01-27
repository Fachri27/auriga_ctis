<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['slug', 'icon', 'is_active'];

    public function translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function processes()
    {
        return $this->hasMany(Process::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function translation($locale = 'id')
    {
        return $this->translations->where('locale', $locale)->first();
    }

    public function artikel()
    {
        return $this->hasMany(Artikel::class);
    }
}
