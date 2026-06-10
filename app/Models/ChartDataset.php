<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartDataset extends Model
{
    protected $table = 'chart_data';

    protected $fillable = [
        'dataset',
        'year',
        'label',
        'value',
    ];
}
