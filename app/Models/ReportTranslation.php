<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportTranslation extends Model
{
    protected $fillable = [
        'report_id',
        'locale',
        'description'
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
