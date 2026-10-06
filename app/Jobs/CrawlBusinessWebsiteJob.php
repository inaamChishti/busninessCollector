<?php

namespace App\Jobs;

use App\Models\Business;
use App\Models\CollectionJob;
use App\Models\Setting;
use App\Services\BusinessEmailService;
use App\Services\WebsiteCrawlerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

class CrawlBusinessWebsiteJob implements ShouldQueue
{
    use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;
    public int $tries=3;public int $timeout=300;
    public function __construct(public readonly int $businessId){$this->onQueue('crawler');}
    public function backoff():array{return[120,600];}
    public function middleware():array{return[(new WithoutOverlapping('crawl:'.$this->businessId))->expireAfter(360)];}
    public function handle(WebsiteCrawlerService $crawler,BusinessEmailService $emails):void
    {
        if(Setting::valueOf('collection_paused',true)){$this->release(60);return;}
        $business=Business::findOrFail($this->businessId);if(!$business->official_website)return;
        $track=CollectionJob::firstOrCreate(['uuid'=>'crawl-'.$business->id],['type'=>'website_crawl','subject_type'=>Business::class,'subject_id'=>$business->id,'status'=>'processing','started_at'=>now()]);
        try{$result=$crawler->crawl($business);$count=$emails->persistFromDocuments($business,$result['documents']);$business->refresh();
            if(!$count){$status=$result['blocked']?'crawl_blocked':($result['request_failed']?'request_failed':($result['contact_form_url']?'contact_form_only':'website_found_no_email'));$business->update(['email_status'=>$status,'has_contact_form'=>(bool)$result['contact_form_url'],'contact_form_url'=>$result['contact_form_url'],'email_checked_at'=>now()]);}
            foreach($business->emails as $email)ValidateBusinessEmailJob::dispatch($email->id);
            $track->update(['status'=>'completed','progress_current'=>$result['pages'],'progress_total'=>$result['pages'],'completed_at'=>now()]);
        }catch(Throwable$e){$business->update(['email_status'=>'request_failed','email_checked_at'=>now()]);$track->update(['status'=>'failed','last_error'=>mb_substr($e->getMessage(),0,2000)]);throw$e;}
    }
}
