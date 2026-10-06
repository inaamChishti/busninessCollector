<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Services\WebsiteVerificationService;
use Tests\TestCase;

class WebsiteVerificationServiceTest extends TestCase
{
    public function test_scores_multiple_matching_signals():void{$b=new Business(['business_name'=>'ABC Solicitors','city'=>'London','postcode'=>'E6 1AA','phone'=>'020 1234 5678','address_line_1'=>'10 High Street']);$result=app(WebsiteVerificationService::class)->score($b,['url'=>'https://abcsolicitors.co.uk','title'=>'ABC Solicitors London','snippet'=>'10 High Street E6 1AA. Call 020 1234 5678']);$this->assertGreaterThanOrEqual(80,$result['score']);$this->assertTrue($result['evidence']['postcode']);}
}
