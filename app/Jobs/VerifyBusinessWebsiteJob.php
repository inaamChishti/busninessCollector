<?php

namespace App\Jobs;

use App\Models\Business;
use App\Models\BusinessWebsite;
use App\Models\CollectionJob;
use App\Models\Setting;
use App\Services\SafeHttpClient;
use App\Services\WebsiteVerificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class VerifyBusinessWebsiteJob implements ShouldQueue
{
    use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;
    public int $tries=3;public int $timeout=90;
    public function __construct(public readonly int $businessId,public readonly int $websiteId){$this->onQueue('website-search');}
    public function backoff():array{return[60,300];}
    public function handle(SafeHttpClient $http,WebsiteVerificationService $verifier):void
    {
        if(Setting::valueOf('collection_paused',true)){$this->release(60);return;}
        $business=Business::findOrFail($this->businessId);$candidate=BusinessWebsite::where('business_id',$business->id)->findOrFail($this->websiteId);
        try{$response=$http->get($candidate->url);if(!$response->successful())throw new \RuntimeException('Candidate website returned HTTP '.$response->status());$scored=$verifier->score($business,['url'=>$candidate->url,'title'=>'','snippet'=>mb_substr(strip_tags($response->body()),0,5000)]);$score=max($candidate->confidence_score,$scored['score']);$evidence=array_merge($candidate->verification_evidence??[],$scored['evidence']);$threshold=(int)Setting::valueOf('website_confidence_threshold',65);$approved=$score>=$threshold;
            $candidate->update(['confidence_score'=>$score,'verification_evidence'=>$evidence,'status'=>$approved?'approved':'low_confidence']);
            if($approved){$business->update(['official_website'=>$candidate->url,'website_domain'=>$candidate->domain,'website_confidence_score'=>$score,'website_verification_evidence'=>$evidence,'website_origin'=>'automatic','website_status'=>'verified','website_checked_at'=>now()]);CrawlBusinessWebsiteJob::dispatch($business->id);}else{$business->update(['website_status'=>'low_confidence','website_checked_at'=>now()]);}
            CollectionJob::where('uuid','website-'.$business->id)->update(['status'=>'completed','progress_current'=>1,'completed_at'=>now()]);
        }catch(Throwable $e){$candidate->update(['status'=>'request_failed']);$business->update(['website_status'=>'request_failed']);throw$e;}
    }
}
