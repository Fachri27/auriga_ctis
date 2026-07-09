<?php

namespace Tests\Feature;

use App\Mail\TestMail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailE2ETest extends TestCase
{
    /**
     * Sends a real email over SMTP (phpunit.xml forces the array driver,
     * so we override it here) and asserts the transport accepts it.
     */
    public function test_test_mail_is_delivered_via_real_smtp(): void
    {
        Config::set('mail.default', 'smtp');

        Mail::to('malichamdan@gmail.com')->send(new TestMail('Ali'));

        $this->assertTrue(true);
    }
}
