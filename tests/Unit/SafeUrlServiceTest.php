<?php

namespace Tests\Unit;

use App\Services\SafeUrlService;
use InvalidArgumentException;
use Tests\TestCase;

class SafeUrlServiceTest extends TestCase
{
    public function test_blocks_private_loopback_and_dangerous_protocols():void
    {
        foreach(['http://127.0.0.1/admin','http://169.254.169.254/latest/meta-data','file:///etc/passwd','ftp://example.com/file']as$url){try{app(SafeUrlService::class)->assertPublicHttpUrl($url);$this->fail("Allowed {$url}");}catch(InvalidArgumentException){$this->assertTrue(true);}}
    }
    public function test_public_ip_classifier_rejects_private_ranges():void{$service=app(SafeUrlService::class);$this->assertFalse($service->isPublicIp('10.0.0.1'));$this->assertFalse($service->isPublicIp('192.168.1.1'));$this->assertTrue($service->isPublicIp('1.1.1.1'));}
}
