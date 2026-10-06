<?php

namespace App\Services;

use App\Jobs\FindBusinessWebsiteJob;
use App\Jobs\SearchBusinessesJob;
use App\Models\Business;
use App\Models\BusinessCategory;
use App\Models\BusinessSearch;
use App\Models\Location;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class CollectionManager
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function start(?array $locationIds = null): int
    {
        Setting::put('collection_paused', false);
        $activeProvider = Setting::valueOf('business_provider', 'yelp');
        $categoryIds = BusinessCategory::where('enabled', true)->orderBy('priority')->pluck('id');
        $query = Location::query()->whereIn('status',['pending','paused','failed']);
        if ($locationIds) $query->whereIn('id',$locationIds);
        $count = 0;
        $query->chunkById(100, function ($locations) use ($categoryIds, $activeProvider, &$count) {
            foreach ($locations as $location) {
                $location->update(['status'=>'processing','started_at'=>$location->started_at ?: now(),'last_error'=>null]);
                foreach ($categoryIds as $categoryId) {
                    $search = BusinessSearch::firstOrCreate(['location_id'=>$location->id,'business_category_id'=>$categoryId,'provider'=>$activeProvider,'offset'=>0],['status'=>'pending']);
                    if (! in_array($search->status,['completed','processing'],true)) { $search->update(['status'=>'queued']); SearchBusinessesJob::dispatch($search->id); }
                }
                $count++;
            }
        });
        $this->activity->log('collection.started', "Collection started for {$count} locations.");
        return $count;
    }

    public function pause(): void { Setting::put('collection_paused', true); Location::where('status','processing')->update(['status'=>'paused']); $this->activity->log('collection.paused','Collection paused.'); }
    public function resume(): int { Location::where('status','paused')->update(['status'=>'processing']); $this->activity->log('collection.resumed','Collection resumed.'); return $this->start(); }

    public function findMissingWebsites(?array $businessIds = null): int
    {
        $query=Business::whereNull('official_website')->whereIn('website_status',['pending','website_not_found','request_failed','retry_required']); if($businessIds)$query->whereIn('id',$businessIds); $count=0;
        $query->select('id')->chunkById(200,function($rows)use(&$count){foreach($rows as $row){FindBusinessWebsiteJob::dispatch($row->id);$count++;}}); return $count;
    }
}
