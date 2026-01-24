<?php

namespace App\Http\Livewire;

use App\Models\CaseModel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class PublishCases extends Component
{
    use AuthorizesRequests;

    // NEW: requestPublish / approveAndPublish / unpublish / publishMap methods
    public function requestPublish(CaseModel $case)
    {
        $this->authorize('requestPublish', $case);

        $case->update([
            'publish_status' => 'pending_review',
            'publish_requested_at' => now(),
            'publish_requested_by' => auth()->id(),
        ]);

        // timeline entry
        \Illuminate\Support\Facades\DB::table('case_timelines')->insert([
            'case_id' => $case->id,
            'actor_id' => auth()->id(),
            'notes' => 'Publish requested',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->emit('publishRequested', $case->id);
    }

    public function approveAndPublish(CaseModel $case)
    {
        $this->authorize('approvePublish', $case);

        \DB::transaction(function () use ($case) {
            $case->update([
                'publish_status' => 'published',
                'is_public' => true,
                'published_at' => now(),
                'published_by' => auth()->id(),
            ]);

            \DB::table('case_timelines')->insert([
                'case_id' => $case->id,
                'actor_id' => auth()->id(),
                'notes' => 'Publish approved and case made public',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \App\Jobs\SyncCaseGeometry::dispatch($case->id);
        });

        $this->emit('casePublished', $case->id);
    }

    public function unpublish(CaseModel $case)
    {
        $this->authorize('unpublish', $case);

        \DB::transaction(function () use ($case) {
            $case->update([
                'publish_status' => 'unpublished',
                'is_public' => false,
            ]);

            \DB::table('case_timelines')->insert([
                'case_id' => $case->id,
                'actor_id' => auth()->id(),
                'notes' => 'Case unpublished',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // hide geometry
            \DB::table('case_geometries')->where('case_id', $case->id)->update(['is_public' => 0, 'updated_at' => now()]);
        });

        $this->emit('caseUnpublished', $case->id);
    }

    public function publishMap(CaseModel $case)
    {
        $this->authorize('publishMap', $case);

        \App\Jobs\SyncCaseGeometry::dispatch($case->id);

        $case->update(['map_published_at' => now(), 'map_published_by' => auth()->id()]);

        \DB::table('case_timelines')->insert([
            'case_id' => $case->id,
            'actor_id' => auth()->id(),
            'notes' => 'Case map published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->emit('caseMapPublished', $case->id);
    }

    // LEGACY: simple single-action publish method (kept for fallback)
    /*
    public function publish(CaseModel $case)
    {
        $this->authorize('publish', $case);

        $case->is_public = true;
        $case->published_at = now();
        $case->published_by = auth()->id();
        $case->save();

        $this->emit('casePublished', $case->id);
    }
    */

    public function render()
    {
        return view('livewire.publish-cases');
    }
}
