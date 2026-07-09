<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawCase extends Model
{
    protected $fillable = [
        'no_perkara', 'pengadilan', 'perkara', 'klasifikasi', 'klasifikasi_clean',
        'tahun', 'kabupaten', 'pulau', 'terdakwa', 'jumlah_terdakwa',
        'subjek_hukum', 'penyertaan', 'vonis_penjara', 'subsidair',
        'vonis_denda', 'vonis_putusan', 'biaya_perkara', 'jaksa',
        'nama_hakim', 'kabupaten_kota',
    ];
}
