<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Report extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'report_code',

        // Identitas lengkap pelapor
        'nama_lengkap',
        'nik',
        'jenis_kelamin',
        'tanggal_lahir',
        'alamat',
        'no_hp',
        'email',
        'pekerjaan',
        'kewarganegaraan',
        'status_perkawinan',

        // Lokasi
        'lat',
        'lng',

        // Bukti
        'evidence',

        // Status
        'status_id',

        // category
        'category_id',

        // User pelapor (jika login)
        'created_by',

        // Publication
        'is_published',
        'published_at',
        'published_by',
        'category_ids',
        'type_pelapor',
    ];

    protected $casts = [
        'evidence' => 'array',
        'tanggal_lahir' => 'date',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'category_ids' => 'array',
    ];

    public function publishedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'published_by');
    }

    /* -----------------------------------------
        RELATIONSHIPS
    ----------------------------------------- */

    public function translations()
    {
        return $this->hasMany(ReportTranslation::class);
    }

    public function translation($locale = 'id')
    {
        return $this->translations
            ->where('locale', $locale)
            ->first();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    /* -----------------------------------------
        AUTO ANONIMISASI
    ----------------------------------------- */

    public function getAnonNameAttribute()
    {
        return 'Anonim';
    }

    public function getAnonNikAttribute()
    {
        if (!$this->nik) return null;
        return substr($this->nik, 0, 4) . '********';
    }

    public function getAnonNoHpAttribute()
    {
        if (!$this->no_hp) return null;
        return substr($this->no_hp, 0, 4) . '*****';
    }

    public function getAnonEmailAttribute()
    {
        return $this->email ? 'hidden@privacy.id' : null;
    }

    public function getAnonIdentityAttribute()
    {
        return [
            'nama_lengkap' => $this->anon_name,
            'nik' => $this->anon_nik,
            'no_hp' => $this->anon_no_hp,
            'email' => $this->anon_email,
        ];
    }

    /* -----------------------------------------
        OPENING STATEMENT (AUTO)
    ----------------------------------------- */

    public function getOpeningStatementAttribute()
    {
        $tanggal = $this->created_at->format('d F Y');
        $desc = $this->translation('id')?->description ?? '(deskripsi tidak tersedia)';

        return "Pada tanggal {$tanggal}, sistem menerima laporan mengenai: {$desc}.";
    }
}
