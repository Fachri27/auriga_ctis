<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test tidak boleh memanggil API sungguhan (DeepL, Google Drive, dll.); pakai Http::fake().
        Http::preventStrayRequests();
    }
}
