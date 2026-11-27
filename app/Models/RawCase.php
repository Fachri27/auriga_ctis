<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RawCase extends Model
{
    protected $fillable = [
        'no_perkara', 'pengadilan', 'perkara', 'klasifikasi', 'klasifikasi_clean',
        'tahun', 'kabupaten', 'pulau', 'terdakwa', 'jumlah_terdakwa',
        'subjek_hukum', 'penyertaan', 'tuntutan_penjara', 'vonis_penjara', 'subsidair',
        'tuntutan_denda', 'vonis_denda', 'vonis_putusan', 'biaya_perkara', 'jaksa',
        'nama_hakim', 'kabupaten_kota',
    ];

    /**
     * Perkara dalam rentang tahun dashboard, termasuk baris yang tahunnya kosong. Tab Dashboard di spreadsheet
     * (dihitung dari tab CleanData) juga menghitung baris itu, sehingga total di web sama dengan spreadsheet.
     *
     * @param  array{0: int, 1: int}  $years
     */
    public function scopeInPeriod(Builder $query, array $years): Builder
    {
        return $query->where(fn (Builder $q) => $q->whereBetween('tahun', $years)->orWhereNull('tahun'));
    }
}
