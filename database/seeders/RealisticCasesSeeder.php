<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * RealisticCasesSeeder
 *
 * Inserts 10 realistic case records intended for local development and
 * manual QA of the simplified public UI. This seeder intentionally does NOT
 * trigger the legacy task generator or complex workflow -- it focuses on
 * case + simple timeline + documents + geometry to exercise the simplified
 * story model presented to non-technical users.
 */
class RealisticCasesSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('id_ID');

        // Gather some reference IDs (create minimal fallbacks if missing)
        $statusInvestigation = DB::table('statuses')->where('key', 'investigation')->value('id') ?: DB::table('statuses')->value('id');
        $statusOpen = DB::table('statuses')->where('key', 'open')->value('id') ?: $statusInvestigation;

        $firstCategory = DB::table('categories')->value('id') ?? DB::table('categories')->insertGetId([
            'slug' => 'uncategorized',
            'icon' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $firstUser = DB::table('users')->value('id');

        $provinces = DB::table('provinces')->get();

        for ($i = 0; $i < 10; $i++) {
            // make a unique case number
            $caseNumber = 'CASE-'.strtoupper(Str::random(6));

            // random status distribution
            $statusId = ($i % 3 === 0) ? $statusInvestigation : $statusOpen;

            $eventDate = $faker->dateTimeBetween('-12 months', 'now');

            // Random lat/lng within Indonesia approx bounds
            $lat = $faker->optional(0.95)->latitude($min = -8, $max = 6);
            $lng = $faker->optional(0.95)->longitude($min = 95, $max = 141);

            $isPublic = $faker->boolean(40); // ~40% public
            $publishedAt = $isPublic ? $faker->dateTimeBetween($eventDate, 'now') : null;

            $createdBy = $firstUser ?: null;

            // Insert case
            $caseId = DB::table('cases')->insertGetId([
                'case_number' => $caseNumber,
                'report_id' => null,
                'category_id' => $firstCategory,
                'status_id' => $statusId,
                'event_date' => $eventDate->format('Y-m-d'),
                'province_id' => $provinces->random()?->id ?? null,
                'district_id' => null,
                'latitude' => $lat,
                'longitude' => $lng,
                'is_public' => $isPublic,
                'published_at' => $publishedAt,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert translation
            $title = $faker->sentence(6);
            $summary = $faker->sentence(12);
            $description = $faker->paragraphs(3, true);

            DB::table('case_translations')->insert([
                'case_id' => $caseId,
                'locale' => 'id',
                'title' => $title,
                'summary' => $summary,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add 1-3 simplified timeline entries
            $timelineCount = rand(1, 3);
            $timelineBase = clone $eventDate;

            for ($t = 0; $t < $timelineCount; $t++) {
                $entryDate = (clone $timelineBase)->modify('+'.($t * rand(1, 14)).' days');

                DB::table('case_timelines')->insert([
                    'case_id' => $caseId,
                    'actor_id' => $createdBy,
                    'notes' => $faker->sentence(10),
                    'created_at' => $entryDate,
                    'updated_at' => $entryDate,
                ]);
            }

            // Add 0-2 documents
            $docCount = rand(0, 2);
            for ($d = 0; $d < $docCount; $d++) {
                DB::table('case_documents')->insert([
                    'case_id' => $caseId,
                    'process_id' => null,
                    'uploaded_by' => $createdBy,
                    'file_path' => 'seeded/document-'.Str::random(8).'.pdf',
                    'mime' => 'application/pdf',
                    'title' => $faker->sentence(4),
                    'meta' => json_encode(['source' => 'seeder']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Add a geometry record if coordinates exist
            if (! is_null($lat) && ! is_null($lng)) {
                // Use ST_GeomFromText; this mirrors publish logic
                $wkt = sprintf('POINT(%.15f %.15f)', (float) $lat, (float) $lng);

                DB::statement(
                    'INSERT INTO case_geometries (case_id, geom, title, category, status, is_public, created_at, updated_at) VALUES (?, ST_GeomFromText(?, 4326), ?, ?, ?, ?, ?, ?)',
                    [
                        $caseId,
                        $wkt,
                        $title,
                        null,
                        'draft',
                        $isPublic ? 1 : 0,
                        now(),
                        now(),
                    ]
                );
            }

            // Optional: add an actor for some cases
            if ($faker->boolean(30)) {
                DB::table('case_actors')->insert([
                    'case_id' => $caseId,
                    'type' => $faker->randomElement(['citizen', 'corporate', 'government']),
                    'name' => $faker->name,
                    'description' => $faker->optional()->sentence(12),
                    'metadata' => json_encode([]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // NOTE: We intentionally DO NOT invoke CaseTaskGenerator::generate here
        // to avoid re-introducing complex task orchestration into the simplified flow.
    }
}
