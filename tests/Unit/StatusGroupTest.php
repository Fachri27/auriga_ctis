<?php

namespace Tests\Unit;

use App\Models\Status;
use PHPUnit\Framework\TestCase;

class StatusGroupTest extends TestCase
{
    public function test_finished_statuses_are_grouped_as_selesai(): void
    {
        foreach (['berkekuatan-hukum-tetap', 'Berkekuatan hukum tetap', 'executed', 'completed', 'closed', 'rejected'] as $key) {
            $this->assertSame('selesai', Status::group($key), $key);
        }
    }

    public function test_other_statuses_are_grouped_as_aktif(): void
    {
        foreach (['open', 'verified', 'published', 'penyelidikan', 'investigation', 'penyidikan', 'prosecution', 'trial', 'vonis', 'converted', null, ''] as $key) {
            $this->assertSame('aktif', Status::group($key), (string) $key);
        }
    }
}
