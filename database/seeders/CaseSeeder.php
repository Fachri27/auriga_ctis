<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaseModel;
use App\Models\CaseTranslation;
use App\Models\CaseTimeline;
use App\Models\CaseDocument;
use App\Models\CaseDiscussion;
use App\Models\CaseActor;
use App\Models\Report;
use App\Models\User;

class CaseSeeder extends Seeder
{
    public function run()
    {
        $report = Report::first();
        $user = User::first();

        $case = CaseModel::create([
            'case_number' => 'CASE-001',
            'report_id' => $report->id,
            'category_id' => 1,
            'event_date' => now(),
            'status_id' => 1,
            'latitude' => $report->lat,
            'longitude' => $report->lng,
            'created_by' => $user->id
        ]);

        CaseTranslation::create([
            'case_id' => $case->id,
            'locale' => 'id',
            'title' => 'Kasus Korupsi Pejabat'
        ]);

        CaseTranslation::create([
            'case_id' => $case->id,
            'locale' => 'en',
            'title' => 'Corruption Case'
        ]);

        CaseTimeline::create([
            'case_id' => $case->id,
            'process_id' => 1,
            'actor_id' => $user->id,
            'notes' => 'Penerimaan laporan awal',
            'started_at' => now()
        ]);

        CaseActor::create([
            'case_id' => $case->id,
            'type' => 'citizen',
            'name' => 'Pelapor Anonim',
            'description' => 'Memberikan informasi awal'
        ]);

        CaseDiscussion::create([
            'case_id' => $case->id,
            'user_id' => $user->id,
            'message' => 'Diskusi awal terkait bukti laporan.'
        ]);
    }
}
