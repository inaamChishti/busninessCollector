<?php

namespace Tests\Unit;

use App\Services\EmailExtractionService;
use Tests\TestCase;

class EmailExtractionServiceTest extends TestCase
{
    public function test_extracts_normal_mailto_and_obfuscated_addresses_without_guessing():void
    {
        $found=collect(app(EmailExtractionService::class)->extract('<a href="mailto:INFO@Example.co.uk">Email</a> Sales [at] example.co.uk <img src="logo@example.com.png">'))->pluck('email');
        $this->assertTrue($found->contains('info@example.co.uk'));$this->assertTrue($found->contains('sales@example.co.uk'));$this->assertFalse($found->contains('logo@example.com.png'));
    }
}
