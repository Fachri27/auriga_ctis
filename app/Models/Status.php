<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $fillable = [
        'name'
    ];

    public function reports()
    {
        return $this->hasMany(Report::class, 'status_id');
    }
}
