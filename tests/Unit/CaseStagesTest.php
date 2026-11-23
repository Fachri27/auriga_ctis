<?php

namespace Tests\Unit;

use App\Services\CaseStages;
use Tests\TestCase;

class CaseStagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
    }

    private function labels(?string $kualifikasi): array
    {
        return array_column(CaseStages::for($kualifikasi, 'open')['steps'], 'label');
    }

    public function test_each_kualifikasi_has_its_own_stages(): void
    {
        $this->assertSame(['Terbuka', 'Dipublikasikan', 'Penyelidikan', 'Penyidikan', 'Penuntutan', 'Persidangan', 'Vonis'], $this->labels('pidana'));
        $this->assertSame(['Terbuka', 'Dipublikasikan', 'Gugatan', 'Pembuktian', 'Vonis'], $this->labels('perdata'));
        $this->assertSame(['Terbuka', 'Dipublikasikan', 'Sanksi'], $this->labels('administrasi'));
    }

    public function test_empty_or_unknown_kualifikasi_uses_the_pidana_stages(): void
    {
        $this->assertSame($this->labels('pidana'), $this->labels(null));
        $this->assertSame($this->labels('pidana'), $this->labels('lainnya'));
    }

    public function test_custom_notes_replace_the_default_description_per_stage(): void
    {
        $steps = CaseStages::for('perdata', 'gugatan', ['gugatan' => '  Didaftarkan 12 Januari 2026.  ', 'pembuktian' => ' ', 'penyidikan' => 'Tidak dipakai di perdata.'])['steps'];

        $this->assertSame(['terbuka', 'dipublikasikan', 'gugatan', 'pembuktian', 'vonis'], array_column($steps, 'key'));
        $this->assertSame('Didaftarkan 12 Januari 2026.', $steps[4]['desc']);
        $this->assertTrue($steps[4]['custom']);
        // Isian kosong memakai keterangan standar.
        $this->assertFalse($steps[5]['custom']);
        $this->assertStringStartsWith('Para pihak mengajukan bukti', $steps[5]['desc']);
        $this->assertStringNotContainsString('Tidak dipakai', implode(' ', array_column($steps, 'desc')));
    }

    public function test_labels_and_defaults_follow_the_requested_locale(): void
    {
        $en = CaseStages::for('administrasi', null, [], 'en')['steps'];

        $this->assertSame(['Open', 'Published', 'Sanction'], array_column($en, 'label'));
        $this->assertStringStartsWith('The government imposes administrative sanctions', $en[4]['desc']);
        $this->assertSame('Sanksi', CaseStages::for('administrasi', null, [], 'id')['steps'][4]['label']);
    }

    public function test_current_stage_follows_the_case_status_per_track(): void
    {
        $current = fn (?string $kualifikasi, ?string $status) => CaseStages::for($kualifikasi, $status)['current'];

        $this->assertSame(4, $current('perdata', 'gugatan'));
        $this->assertSame(5, $current('perdata', 'pembuktian'));
        $this->assertSame(5, $current('perdata', 'trial'));
        $this->assertSame(6, $current('perdata', 'vonis'));
        $this->assertSame(4, $current('administrasi', 'sanksi'));
        $this->assertSame(8, $current('pidana', 'vonis'));

        // Selesai: semua tahapan sudah dilalui.
        $this->assertGreaterThan(6, $current('perdata', 'completed'));
        $this->assertGreaterThan(4, $current('administrasi', 'closed'));

        // Status dari jalur lain tidak menandai tahapan mana pun.
        $this->assertSame(0, $current('perdata', 'prosecution'));
        $this->assertSame(0, $current('administrasi', 'gugatan'));

        // Key lama "Berkekuatan hukum tetap" dinormalkan.
        $this->assertSame(9, $current('pidana', 'Berkekuatan hukum tetap'));
    }
}
