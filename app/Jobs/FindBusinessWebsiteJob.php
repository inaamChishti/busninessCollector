<?php

namespace App\Jobs;

use App\Contracts\WebsiteSearchProviderInterface;
use App\Models\Business;
use App\Models\BusinessWebsite;
use App\Models\CollectionJob;
use App\Models\Setting;
use App\Services\SafeUrlService;
use App\Services\WebsiteVerificationService;
use App\Support\ProviderNotConfiguredException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

class FindBusinessWebsiteJob implements ShouldQueue
{
    use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;
    public int $tries=4; public int $timeout=90;
    public function __construct(public readonly int $businessId){$this->onQueue('website-search');}
    public function backoff(): array{return[60,300,900];}
    public function middleware():array{return[(new WithoutOverlapping('find-website:'.$this->businessId))->expireAfter(120)];}
    public function handle(WebsiteSearchProviderInterface $provider,WebsiteVerificationService $verifier,SafeUrlService $urls):void
    {
        if(Setting::valueOf('collection_paused',true)){$this->release(60);return;}
        $business=Business::findOrFail($this->businessId); if($business->website_manually_verified&&$business->official_website)return;
        $track=CollectionJob::firstOrCreate(['uuid'=>'website-'.$business->id],['type'=>'website_search','subject_type'=>Business::class,'subject_id'=>$business->id,'status'=>'processing','started_at'=>now()]);
        try{
            $results=$provider->search($business);$best=null;
            foreach($results as $result){try{$urls->assertPublicHttpUrl($result['url']);}catch(Throwable){continue;}$scored=$verifier->score($business,$result);$host=(string)parse_url($result['url'],PHP_URL_HOST);$candidate=BusinessWebsite::updateOrCreate(['business_id'=>$business->id,'url'=>$result['url']],['domain'=>preg_replace('/^www\./','',$host),'confidence_score'=>$scored['score'],'verification_evidence'=>$scored['evidence'],'status'=>'candidate','source'=>$provider->key()]);if(!$best||$candidate->confidence_score>$best->confidence_score)$best=$candidate;}
            if(!$best){$business->update(['website_status'=>'website_not_found','website_checked_at'=>now()]);$track->update(['status'=>'completed','progress_total'=>count($results),'completed_at'=>now()]);return;}
            $business->update(['website_status'=>'verifying']);$track->update(['progress_total'=>count($results)]);VerifyBusinessWebsiteJob::dispatch($business->id,$best->id);
        }catch(ProviderNotConfiguredException $e){$business->update(['website_status'=>'provider_not_configured']);$track->update(['status'=>'paused','last_error'=>$e->getMessage()]);}
        catch(Throwable $e){$business->update(['website_status'=>'request_failed']);$track->update(['status'=>'failed','last_error'=>mb_substr($e->getMessage(),0,2000)]);throw$e;}
    }
}
