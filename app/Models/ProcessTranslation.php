<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessTranslation extends Model
{
    protected $fillable = ['process_id', 'locale', 'name'];

    public function process()
    {
        return $this->belongsTo(Process::class);
    }
}
