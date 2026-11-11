<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CaseModel extends Model
{
    protected $table = 'cases';

    protected $fillable = [
        'case_number',
        'source_ref',
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
        'pelapor',
        'terlapor',
        'instansi',
        'sumber',
        'status_narasi',
        'cover_image',
        'cover_credit',

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

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    /** Panjang maksimum bagian judul di URL publik (karakter). */
    private const URL_TITLE_LENGTH = 60;

    /**
     * URL publik halaman detail kasus: /id/kasus/{judul}-{kode} atau /en/cases/{title}-{code},
     * mis. /id/kasus/suap-perizinan-tambang-ilegal-xltlr. Judul mengikuti terjemahan bahasa tersebut.
     */
    public function publicUrl(?string $locale = null, bool $absolute = true): string
    {
        $locale ??= app()->getLocale();
        $translation = $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'id')
            ?? $this->translations->first();

        return self::publicUrlFor($this->case_number, $translation?->title, $locale, $absolute);
    }

    /** Sama dengan publicUrl(), untuk data kasus yang bukan model (mis. di email). */
    public static function publicUrlFor(string $caseNumber, ?string $title, ?string $locale = null, bool $absolute = true): string
    {
        $locale = $locale === 'en' ? 'en' : 'id';

        return route("public.case.{$locale}", ['locale' => $locale, 'slug' => self::publicSlug($caseNumber, $title)], $absolute);
    }

    /** "CASE-XLTLR" + "Suap Perizinan Tambang Ilegal" → "suap-perizinan-tambang-ilegal-xltlr". */
    public static function publicSlug(string $caseNumber, ?string $title): string
    {
        $title = Str::slug(html_entity_decode(strip_tags((string) $title)));
        if (strlen($title) > self::URL_TITLE_LENGTH) {
            // Potong di batas kata agar tidak ada kata terpotong.
            $title = Str::beforeLast(substr($title, 0, self::URL_TITLE_LENGTH + 1), '-');
        }

        return trim($title.'-'.self::urlCode($caseNumber), '-');
    }

    /** Kode kasus di URL: nomor kasus tanpa awalan "CASE-", huruf kecil ("CASE-XLTLR" → "xltlr"). */
    public static function urlCode(string $caseNumber): string
    {
        return Str::slug(Str::after($caseNumber, 'CASE-'));
    }

    /**
     * Cari kasus publik dari slug URL. Kode kasus ada di akhir slug dan bisa memuat tanda hubung
     * ("...-2024-001"), jadi beberapa akhiran dicoba sekaligus; yang terpanjang dan cocok dipakai.
     */
    public static function findPublicBySlug(string $slug): ?self
    {
        $parts = explode('-', Str::lower($slug));
        $candidates = [];
        for ($i = count($parts) - 1; $i >= max(0, count($parts) - 4); $i--) {
            $code = Str::upper(implode('-', array_slice($parts, $i)));
            array_push($candidates, 'CASE-'.$code, $code);
        }

        return static::query()
            ->where('is_public', true)
            ->whereIn('case_number', $candidates)
            ->get()
            ->filter(fn (self $case) => str_ends_with(Str::lower($slug), self::urlCode($case->case_number)))
            ->sortByDesc(fn (self $case) => strlen($case->case_number))
            ->first();
    }

    /**
     * URL publik foto sampul (lihat App\Services\CaseCoverImage), null bila kasus belum punya foto sampul.
     */
    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_image ? asset('storage/'.$this->cover_image) : null;
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
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
