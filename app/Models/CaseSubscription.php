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
     * Kirim email update status ke subscriber:
     * 1. case_id terisi = mengikuti kasus spesifik ini
     * 2. case_id NULL = berlangganan semua kasus (juga dapat notifikasi status update)
     */
    public static function notifyStatusUpdate(object $case, string $oldStatus, string $newStatus): void
    {
        static::whereNull('case_id')
            ->orWhere('case_id', $case->id)
            ->pluck('email')
            ->unique()
            ->each(fn ($email) => Mail::to($email)->queue(
                new CaseStatusUpdatedMail($case, $oldStatus, $newStatus)
            ));
    }
}
