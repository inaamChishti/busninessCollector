<?php

namespace App\Jobs;

use App\Contracts\BusinessDiscoveryProviderInterface;
use App\Models\BusinessSearch;
use App\Models\CollectionJob;
use App\Models\Location;
use App\Models\Setting;
use App\Services\BusinessUpsertService;
use App\Support\ProviderNotConfiguredException;
use App\Support\ProviderQuotaExceededException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SearchBusinessesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 4; public int $timeout = 150;
    public function __construct(public readonly int $searchId) { $this->onQueue('business-discovery'); }
    public function backoff(): array { return [60,300,900]; }
    public function middleware(): array { return [(new WithoutOverlapping('business-search:'.$this->searchId))->expireAfter(180)]; }

    public function handle(BusinessDiscoveryProviderInterface $provider, BusinessUpsertService $upsert): void
    {
        if (Setting::valueOf('collection_paused', true)) { $this->release(60); return; }
        $search=BusinessSearch::with(['location','category'])->findOrFail($this->searchId);
        if ($search->status==='completed') return;
        $search->update(['status'=>'processing','error'=>null]);
        $track=CollectionJob::firstOrCreate(['uuid'=>'search-'.$search->id],['type'=>'business_search','subject_type'=>BusinessSearch::class,'subject_id'=>$search->id,'status'=>'processing','started_at'=>now()]);
        try {
            $result=$provider->search($search->location,$search->category,$search->offset); $accepted=0;
            foreach($result['items'] as $item){
                if(Setting::valueOf('exact_postcode_match',false) && Location::normalizePostcode((string)$item['postcode'])!==$search->location->postcode_normalized) continue;
                $business=$upsert->persist($item,$search->location,$search->business_category_id); $accepted++;
                if(!$business->official_website && in_array($business->website_status,['pending','retry_required','website_not_found'],true)) FindBusinessWebsiteJob::dispatch($business->id);
            }
            $search->update(['status'=>'completed','results_count'=>$accepted]);
            $track->update(['status'=>'completed','progress_current'=>$accepted,'progress_total'=>$result['total'],'completed_at'=>now()]);
            if($result['has_more']){
                $next=BusinessSearch::firstOrCreate(['location_id'=>$search->location_id,'business_category_id'=>$search->business_category_id,'provider'=>$search->provider,'offset'=>$search->offset+50],['status'=>'queued']);
                if($next->status!=='completed') SearchBusinessesJob::dispatch($next->id);
            }
            $this->finishLocationIfReady($search->location);
        } catch(ProviderNotConfiguredException|ProviderQuotaExceededException $e){ $search->update(['status'=>'paused','error'=>$e->getMessage()]); $track->update(['status'=>'paused','last_error'=>$e->getMessage()]); Setting::put('collection_paused',true); }
        catch(Throwable $e){$search->update(['status'=>'failed','error'=>mb_substr($e->getMessage(),0,2000)]);$track->update(['status'=>'failed','last_error'=>mb_substr($e->getMessage(),0,2000)]);throw $e;}
    }

    private function finishLocationIfReady(Location $location): void
    {
        $active=BusinessSearch::where('location_id',$location->id)->whereIn('status',['pending','queued','processing'])->exists();
        if(!$active){$count=$location->businesses()->count();$location->update(['status'=>'completed','businesses_discovered'=>$count,'completed_at'=>now(),'last_processed_at'=>now()]);}
    }
}
