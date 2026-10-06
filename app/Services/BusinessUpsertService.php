<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessSource;
use App\Models\DuplicateCandidate;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BusinessUpsertService
{
    public function persist(array $data, Location $location, ?int $categoryId = null): Business
    {
        return DB::transaction(function () use ($data, $location, $categoryId) {
            $business = Business::withTrashed()->where('external_provider', $data['provider'])->where('external_business_id', $data['external_id'])->first();
            $attributes = ['business_name'=>$data['name'],'address_line_1'=>$data['address_line_1'],'address_line_2'=>$data['address_line_2'],'city'=>$data['city'],'region'=>$data['region'],'postcode'=>$data['postcode'],'country'=>$data['country'],'latitude'=>$data['latitude'],'longitude'=>$data['longitude'],'phone'=>$data['phone'],'primary_category'=>$data['category'],'provider_url'=>$data['provider_url'],'discovered_at'=>now()];
            if (! $business) {
                $business = Business::create(['external_provider'=>$data['provider'],'external_business_id'=>$data['external_id'],...$attributes]);
                $this->flagPossibleDuplicate($business);
            } else {
                $business->restore();
                foreach ($attributes as $key=>$value) if (filled($value) && blank($business->{$key})) $business->{$key} = $value;
                $business->save();
            }
            BusinessSource::updateOrCreate(['provider'=>$data['provider'],'provider_business_id'=>$data['external_id']], ['business_id'=>$business->id,'provider_url'=>$data['provider_url'],'raw_snapshot'=>$data['raw'] ?? null]);
            $business->locations()->syncWithoutDetaching([$location->id=>['searched_postcode'=>$location->postcode,'business_category_id'=>$categoryId]]);
            return $business;
        });
    }

    private function flagPossibleDuplicate(Business $business): void
    {
        $name = Str::lower(trim($business->business_name));
        $candidate = Business::query()->whereKeyNot($business->id)->where(function ($q) use ($business, $name) {
            $q->where(fn ($x) => $x->whereRaw('LOWER(business_name) = ?', [$name])->where('postcode', $business->postcode));
            if ($business->phone) $q->orWhere('phone', $business->phone);
        })->first();
        if (! $candidate) return;
        $matches = array_values(array_filter(['name'=>Str::lower($candidate->business_name)===$name ? 'name' : null,'postcode'=>$candidate->postcode===$business->postcode ? 'postcode' : null,'phone'=>$business->phone && $candidate->phone===$business->phone ? 'phone' : null]));
        DuplicateCandidate::firstOrCreate(['business_id'=>$business->id,'possible_duplicate_id'=>$candidate->id], ['confidence_score'=>min(95, count($matches)*30),'matching_fields'=>$matches]);
    }
}
