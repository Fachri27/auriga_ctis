<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Status extends Model
{
    /** Status kasus yang dihitung "Selesai" (warna titik di peta publik); selain ini dihitung "Aktif". */
    public const FINISHED_KEYS = ['berkekuatan-hukum-tetap', 'executed', 'completed', 'closed', 'rejected'];

    /**
     * "selesai" atau "aktif". Key dinormalkan karena ada data lama berbentuk "Berkekuatan hukum tetap".
     */
    public static function group(?string $key): string
    {
        return in_array(Str::slug((string) $key), self::FINISHED_KEYS, true) ? 'selesai' : 'aktif';
    }

    /** Status dengan key ini (dinormalkan seperti group()), mis. dari tabel "Tahapan Kasus" dokumen impor. */
    public static function findByKey(string $key): ?self
    {
        return static::query()->get(['id', 'key', 'name'])->first(fn ($status) => Str::slug($status->key) === Str::slug($key));
    }

    protected $fillable = [
        'key',
        'name'
    ];

    public function reports()
    {
        return $this->hasMany(Report::class, 'status_id');
    }
}
