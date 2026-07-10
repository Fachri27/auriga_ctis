<?php

namespace App\Models;

use App\Mail\CaseStatusUpdatedMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class CaseSubscription extends Model
{
    protected $fillable = ['email', 'case_id'];

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    /**
     * Kirim email update status ke subscriber yang mengikuti kasus ini.
     * Hanya subscriber dengan case_id terisi (kasus spesifik);
     * case_id null = langganan kasus baru, tidak ikut notifikasi update status.
     */
    public static function notifyStatusUpdate(object $case, string $oldStatus, string $newStatus): void
    {
        static::where('case_id', $case->id)
            ->pluck('email')
            ->each(fn ($email) => Mail::to($email)->queue(
                new CaseStatusUpdatedMail($case, $oldStatus, $newStatus)
            ));
    }
}
