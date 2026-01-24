<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseTimeline extends Model
{
    protected $fillable = [
        'case_id',
        'process_id',
        'actor_id',
        'notes',
        'started_at',
        'finished_at',
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class);
    }

    /**
     * @deprecated Process relation is part of the legacy workflow system.
     * OLD: timeline entries used to be tied to `processes` and used `started_at`/`finished_at`.
     * NEW: For public UI use `simpleEntry()` or `CaseModel::simpleTimeline()`.
     */
    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Simplified timeline entry for UI consumption.
     * Returns array with `notes`, `date` and optional `title`.
     */
    public function simpleEntry()
    {
        return [
            'notes' => $this->notes ?? '',
            'date' => $this->created_at,
            'title' => $this->title ?? null,
        ];
    }
}
