<?php

namespace App\Services\BusinessDiscovery;

use App\Contracts\BusinessDiscoveryProviderInterface;
use App\Models\BusinessCategory;
use App\Models\Location;
use App\Models\Setting;
use App\Support\ProviderNotConfiguredException;
use App\Support\ProviderQuotaExceededException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

class YelpBusinessDiscoveryProvider implements BusinessDiscoveryProviderInterface
{
    public function key(): string { return 'yelp'; }
    private function apiKey(): ?string { return Setting::valueOf('yelp_api_key', config('services.yelp.key')); }
    public function configured(): bool { return filled($this->apiKey()); }

    public function search(Location $location, BusinessCategory $category, int $offset = 0): array
    {
        if (! $this->configured()) throw new ProviderNotConfiguredException('Yelp provider is not configured. Add an API key in Settings.');
        $limit = 50;
        $rpm = (int) Setting::valueOf('max_api_requests_per_minute', 60);
        if (RateLimiter::tooManyAttempts('provider:yelp', max(1, $rpm))) throw new ProviderQuotaExceededException('Local Yelp request rate limit reached.');
        RateLimiter::hit('provider:yelp', 60);

        try {
            $response = Http::acceptJson()->withToken($this->apiKey())->timeout((int) Setting::valueOf('request_timeout', 15))
                ->retry((int) Setting::valueOf('retry_attempts', 3), fn (int $attempt) => min(1000 * (2 ** ($attempt - 1)), 8000), throw: false)
                ->get('https://api.yelp.com/v3/businesses/search', [
                    'location' => trim($location->postcode.' '.$location->city.' '.$location->country),
                    'categories' => $category->api_alias ?: null,
                    'term' => $category->search_term ?: $category->name,
                    'limit' => $limit,
                    'offset' => min($offset, 950),
                    'sort_by' => 'best_match',
                ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Yelp connection failed: '.$e->getMessage(), previous: $e);
        }
        if ($response->status() === 429) throw new ProviderQuotaExceededException('Yelp API quota or rate limit exceeded.');
        if ($response->failed()) throw new RuntimeException('Yelp API returned HTTP '.$response->status().': '.mb_substr($response->body(), 0, 500));

        $json = $response->json();
        $items = array_map(fn (array $b): array => [
            'provider'=>'yelp','external_id'=>$b['id'] ?? null,'name'=>$b['name'] ?? 'Unknown business',
            'address_line_1'=>$b['location']['address1'] ?? null,'address_line_2'=>trim(implode(' ', array_filter([$b['location']['address2'] ?? null,$b['location']['address3'] ?? null]))) ?: null,
            'city'=>$b['location']['city'] ?? null,'region'=>$b['location']['state'] ?? null,'postcode'=>$b['location']['zip_code'] ?? null,'country'=>$b['location']['country'] ?? $location->country,
            'latitude'=>$b['coordinates']['latitude'] ?? null,'longitude'=>$b['coordinates']['longitude'] ?? null,
            'phone'=>$b['display_phone'] ?? ($b['phone'] ?? null),'category'=>$b['categories'][0]['title'] ?? $category->name,
            'provider_url'=>$b['url'] ?? null,'raw'=>$b,
        ], $json['businesses'] ?? []);
        $total = min((int) ($json['total'] ?? count($items)), 1000);
        return ['items'=>$items,'total'=>$total,'has_more'=>($offset + count($items)) < $total && ($offset + $limit) <= 950];
    }
}
