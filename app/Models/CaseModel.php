<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseModel extends Model
{
    protected $table = 'cases';

    protected $fillable = [
        'case_number',
        'report_id',
        'category_id',
        'status_id',
        'event_date',
        'latitude',
        'longitude',
        'is_public',
        'published_at',
        'is_tasks_completed',
        'tasks_completed_at',
        'created_by',
        'verified_by',
        'bukti',
        'korban',
        'pekerjaan',
        'jenis_kelamin',
        'jumlah_korban',
        'konflik',
        'category_ids',

    ];

    protected $casts = [
        'published_at' => 'datetime',
        'tasks_completed_at' => 'datetime',
        'is_tasks_completed' => 'boolean',
        'is_public' => 'boolean',
        'bukti' => 'array',
        'category_ids' => 'array',
    ];

    /**
     * Appended computed attributes for simplified UI
     */
    protected $appends = [
        'current_status_label',
        'last_update_date',
        'simple_timeline',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Legacy: returns timeline entries that may be tied to processes/tasks.
     * @deprecated Complex workflow-based timeline. Kept for backward compatibility.
     * NEW: Use `simpleTimeline()` or `simple_timeline` attribute for public/CMS UI.
     */
    public function timelines()
    {
        return $this->hasMany(CaseTimeline::class, 'case_id');
    }

    public function documents()
    {
        return $this->hasMany(CaseDocument::class, 'case_id');
    }

    /**
     * @deprecated Internal discussion model: kept for admin use only.
     * Public UI should avoid showing internal discussions.
     */
    public function discussions()
    {
        return $this->hasMany(CaseDiscussion::class, 'case_id');
    }

    /**
     * @deprecated Actors are internal metadata and should not be exposed to public UI.
     */
    public function actors()
    {
        return $this->hasMany(CaseActor::class, 'case_id');
    }

    public function translations()
    {
        return $this->hasMany(CaseTranslation::class, 'case_id', 'id');
    }

    public function geometries()
    {
        return $this->hasMany(Geometry::class, 'case_id', 'id');
    }

    /**
     * Simplified timeline for public & CMS UI
     *
     * Returns a chronological list of entries with:
     * - notes (required)
     * - created_at (date)
     * - title (optional)
     *
     * OLD: timeline tied to process & task (started_at/finished_at)
     * NEW: simplified timeline for public clarity
     */
    public function simpleTimeline()
    {
        return $this->timelines()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function (CaseTimeline $t) {
                return [
                    'notes' => $t->notes ?? '',
                    'created_at' => $t->created_at,
                    // Some legacy entries may include a `title` field; keep null by default.
                    'title' => $t->title ?? null,
                ];
            })
            ->values();
    }

    /**
     * Accessor for simplified timeline (appended attribute)
     */
    public function getSimpleTimelineAttribute()
    {
        return $this->simpleTimeline();
    }

    /**
     * Human-friendly current status label for public UI
     */
    public function getCurrentStatusLabelAttribute()
    {
        return $this->status?->name ?? 'Unknown';
    }

    /**
     * Last update date (useful for public summary views)
     */
    public function getLastUpdateDateAttribute()
    {
        return $this->updated_at?->toDateTimeString();
    }

    public function actions()
    {
        return $this->hasMany(CaseAction::class, 'case_id');
    }
}
