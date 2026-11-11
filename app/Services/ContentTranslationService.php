<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Menerjemahkan konten bilingual (tabel *_translations) dari locale "id" ke "en"
 * langsung di database. Dipakai halaman CMS Auto Translate dan job TranslateContent.
 *
 * Sebuah record dianggap "perlu diterjemahkan" bila ada field yang terisi di versi ID
 * tetapi kosong di versi EN (termasuk bila baris EN belum ada sama sekali).
 */
class ContentTranslationService
{
    public const TYPES = [
        'case' => [
            'label' => 'Kasus',
            'table' => 'case_translations',
            'parent' => 'cases',
            'fk' => 'case_id',
            'fields' => ['title', 'description', 'perkembangan', 'pembelajaran', 'dugaan_permasalahan'],
            'soft_deletes' => true,
        ],
        'artikel' => [
            'label' => 'Artikel',
            'table' => 'artikel_translations',
            'parent' => 'artikels',
            'fk' => 'artikel_id',
            'fields' => ['title', 'excerpt', 'content'],
        ],
        'about' => [
            'label' => 'Halaman About',
            'table' => 'about_page_translations',
            'parent' => 'about_pages',
            'fk' => 'about_page_id',
            'fields' => ['title', 'content', 'vision', 'mission'],
        ],
        'category' => [
            'label' => 'Kategori',
            'table' => 'category_translations',
            'parent' => 'categories',
            'fk' => 'category_id',
            'fields' => ['name', 'description'],
        ],
        'process' => [
            'label' => 'Proses',
            'table' => 'process_translations',
            'parent' => 'processes',
            'fk' => 'process_id',
            'fields' => ['name'],
        ],
        'task' => [
            'label' => 'Task',
            'table' => 'task_translations',
            'parent' => 'tasks',
            'fk' => 'task_id',
            'fields' => ['name', 'description'],
        ],
    ];

    public function __construct(private DeepLTranslator $translator) {}

    /**
     * Ringkasan per jenis konten untuk halaman CMS.
     *
     * @return list<array{type: string, label: string, total: int, pending: int, characters: int}>
     */
    public function summary(): array
    {
        $rows = [];

        foreach (self::TYPES as $type => $config) {
            $pending = $this->pendingQuery($type)->get();

            $rows[] = [
                'type' => $type,
                'label' => $config['label'],
                'total' => $this->sourceQuery($type)->count(),
                'pending' => $pending->count(),
                'characters' => $pending->sum(fn ($row) => $this->pendingCharacters($type, $row)),
            ];
        }

        return $rows;
    }

    /**
     * ID record (parent) yang versi EN-nya masih ada field kosong.
     *
     * @return Collection<int, int>
     */
    public function pendingIds(string $type): Collection
    {
        return $this->pendingQuery($type)->pluck('id')->map(fn ($id) => (int) $id);
    }

    /**
     * Terjemahkan satu record ID → EN dan simpan ke baris EN.
     *
     * @param  bool  $overwrite  false = hanya isi field EN yang masih kosong
     * @return int jumlah field yang diterjemahkan
     */
    public function translateRecord(string $type, int $id, bool $overwrite = false): int
    {
        $config = self::config($type);
        $table = $config['table'];
        $fk = $config['fk'];

        $source = DB::table($table)->where($fk, $id)->where('locale', 'id')->first();

        if (! $source) {
            return 0;
        }

        $existing = DB::table($table)->where($fk, $id)->where('locale', 'en')->first();

        $texts = [];
        foreach ($config['fields'] as $field) {
            if (blank($source->{$field} ?? null)) {
                continue;
            }

            if (! $overwrite && $existing && filled($existing->{$field} ?? null)) {
                continue;
            }

            $texts[$field] = $source->{$field};
        }

        if ($texts === []) {
            return 0;
        }

        $translated = $this->translator->translate($texts);

        // Ringkasan kasus diturunkan dari deskripsi, sama seperti CaseForm.
        if ($type === 'case' && isset($translated['description'])) {
            $translated['summary'] = (string) Str::of($translated['description'])->stripTags()->limit(300, '...');
        }

        if ($existing) {
            DB::table($table)->where('id', $existing->id)->update($translated + ['updated_at' => now()]);
        } else {
            DB::table($table)->insert(array_merge(
                // Kolom judul/nama wajib terisi; field lain disamakan dengan form ('' bila kosong).
                array_fill_keys($config['fields'], ''),
                $translated,
                [$fk => $id, 'locale' => 'en', 'created_at' => now(), 'updated_at' => now()],
            ));
        }

        return count($texts);
    }

    public static function config(string $type): array
    {
        return self::TYPES[$type] ?? throw new \InvalidArgumentException("Jenis konten '{$type}' tidak dikenal.");
    }

    /**
     * Baris versi ID yang induknya masih ada (kasus terhapus tidak ikut).
     */
    private function sourceQuery(string $type): Builder
    {
        $config = self::config($type);

        $query = DB::table("{$config['table']} as src")
            ->join("{$config['parent']} as p", 'p.id', '=', "src.{$config['fk']}")
            ->where('src.locale', 'id');

        if ($config['soft_deletes'] ?? false) {
            $query->whereNull('p.deleted_at');
        }

        return $query;
    }

    private function pendingQuery(string $type): Builder
    {
        $config = self::config($type);
        $fk = $config['fk'];

        $columns = ["src.{$fk} as id"];
        foreach ($config['fields'] as $field) {
            $columns[] = "src.{$field} as src_{$field}";
            $columns[] = "en.{$field} as en_{$field}";
        }

        return $this->sourceQuery($type)
            ->leftJoin("{$config['table']} as en", function ($join) use ($fk) {
                $join->on("en.{$fk}", '=', "src.{$fk}")->where('en.locale', 'en');
            })
            ->where(function ($query) use ($config) {
                foreach ($config['fields'] as $field) {
                    $query->orWhere(function ($q) use ($field) {
                        $q->whereNotNull("src.{$field}")
                            ->where("src.{$field}", '<>', '')
                            ->where(fn ($en) => $en->whereNull("en.{$field}")->orWhere("en.{$field}", ''));
                    });
                }
            })
            ->select($columns)
            ->orderBy("src.{$fk}");
    }

    /**
     * Perkiraan karakter yang akan ditagih DeepL untuk satu baris pending.
     */
    private function pendingCharacters(string $type, object $row): int
    {
        $characters = 0;

        foreach (self::config($type)['fields'] as $field) {
            if (filled($row->{"src_{$field}"}) && blank($row->{"en_{$field}"})) {
                $characters += mb_strlen($row->{"src_{$field}"});
            }
        }

        return $characters;
    }
}
