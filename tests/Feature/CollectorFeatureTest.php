<?php

namespace Tests\Feature;

use App\Contracts\BusinessDiscoveryProviderInterface;
use App\Jobs\SearchBusinessesJob;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessSearch;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use App\Services\BusinessUpsertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectorFeatureTest extends TestCase
{
    use RefreshDatabase;
    public function test_admin_pages_require_authentication():void{$this->get('/businesses')->assertRedirect('/login');}
    public function test_non_admin_is_forbidden():void{$user=User::factory()->create(['is_admin'=>false]);$this->actingAs($user)->get('/')->assertForbidden();}
    public function test_business_upsert_deduplicates_provider_identity():void{$location=Location::create(['postcode'=>'E6 1AA','postcode_normalized'=>'E61AA','city'=>'London']);$data=['provider'=>'yelp','external_id'=>'abc123','name'=>'ABC Ltd','address_line_1'=>'1 Road','address_line_2'=>null,'city'=>'London','region'=>'London','postcode'=>'E6 1AA','country'=>'GB','latitude'=>null,'longitude'=>null,'phone'=>'0201','category'=>'Accountants','provider_url'=>'https://www.yelp.com/biz/abc','raw'=>[]];$service=app(BusinessUpsertService::class);$service->persist($data,$location);$service->persist($data,$location);$this->assertSame(1,Business::count());$this->assertSame(1,$location->businesses()->count());}
    public function test_search_job_is_idempotent_after_completion():void{$location=Location::create(['postcode'=>'E6 1AA','postcode_normalized'=>'E61AA','city'=>'London','status'=>'processing']);$category=BusinessCategory::create(['name'=>'Accountants','enabled'=>true,'priority'=>1]);$search=BusinessSearch::create(['location_id'=>$location->id,'business_category_id'=>$category->id,'provider'=>'fake','offset'=>0,'status'=>'queued']);Setting::put('collection_paused',false);$provider=new class implements BusinessDiscoveryProviderInterface{public function key():string{return'fake';}public function configured():bool{return true;}public function search(Location$l,BusinessCategory$c,int$o=0):array{return['items'=>[['provider'=>'fake','external_id'=>'1','name'=>'One Ltd','address_line_1'=>null,'address_line_2'=>null,'city'=>'London','region'=>null,'postcode'=>'E6 1AA','country'=>'GB','latitude'=>null,'longitude'=>null,'phone'=>null,'category'=>'Accountants','provider_url'=>null,'raw'=>[]]],'total'=>1,'has_more'=>false];}};$job=new SearchBusinessesJob($search->id);$job->handle($provider,app(BusinessUpsertService::class));$job->handle($provider,app(BusinessUpsertService::class));$this->assertSame(1,Business::count());}
}
