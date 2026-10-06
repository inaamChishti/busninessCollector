<?php

namespace Tests\Feature;

use App\Models\BusinessCategory;
use App\Models\Location;
use App\Models\Setting;
use App\Services\BusinessDiscovery\YelpBusinessDiscoveryProvider;
use App\Support\ProviderQuotaExceededException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProviderErrorHandlingTest extends TestCase
{
    use RefreshDatabase;
    public function test_yelp_429_becomes_quota_exception():void{Setting::put('yelp_api_key','secret','apis',true);Http::fake(['api.yelp.com/*'=>Http::response([],429)]);$this->expectException(ProviderQuotaExceededException::class);app(YelpBusinessDiscoveryProvider::class)->search(new Location(['postcode'=>'E6 1AA','city'=>'London','country'=>'GB']),new BusinessCategory(['name'=>'Accountants','api_alias'=>'accountants']));}
}
