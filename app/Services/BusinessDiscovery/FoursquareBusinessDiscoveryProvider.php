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

class FoursquareBusinessDiscoveryProvider implements BusinessDiscoveryProviderInterface
{
    public function key(): string { return 'foursquare'; }

    private function apiKey(): ?string { return Setting::valueOf('foursquare_api_key', config('services.foursquare.key')); }

    public function configured(): bool { return filled($this->apiKey()); }

    public function search(Location $location, BusinessCategory $category, int $offset = 0): array
    {
        if (! $this->configured()) {
            throw new ProviderNotConfiguredException('Foursquare provider is not configured. Add an API key in Settings.');
        }

        $limit = 50;
        $rpm   = (int) Setting::valueOf('max_api_requests_per_minute', 60);

        if (RateLimiter::tooManyAttempts('provider:foursquare', max(1, $rpm))) {
            throw new ProviderQuotaExceededException('Local Foursquare request rate limit reached.');
        }
        RateLimiter::hit('provider:foursquare', 60);

        // Build query parameters
        $query = [
            'query'  => $category->search_term ?: $category->name,
            'near'   => trim($location->city . ', ' . $location->country),
            'limit'  => $limit,
            'fields' => 'fsq_id,name,location,geocodes,tel,website,email,categories,link',
        ];

        // Use foursquare category ID if stored in api_alias
        if (filled($category->api_alias) && is_numeric($category->api_alias)) {
            $query['categories'] = $category->api_alias;
        }

        // Foursquare v3 uses cursor-based pagination; we simulate offset via multiple pages
        // The API supports an 'offset' parameter (undocumented but functional)
        if ($offset > 0) {
            $query['offset'] = $offset;
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders(['Authorization' => $this->apiKey()])
                ->timeout((int) Setting::valueOf('request_timeout', 15))
                ->retry(
                    (int) Setting::valueOf('retry_attempts', 3),
                    fn (int $attempt) => min(1000 * (2 ** ($attempt - 1)), 8000),
                    throw: false
                )
                ->get('https://places-api.foursquare.com/places/search', $query);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Foursquare connection failed: ' . $e->getMessage(), previous: $e);
        }

        if ($response->status() === 429) {
            throw new ProviderQuotaExceededException('Foursquare API quota or rate limit exceeded.');
        }

        if ($response->failed()) {
            throw new RuntimeException(
                'Foursquare API returned HTTP ' . $response->status() . ': ' . mb_substr($response->body(), 0, 500)
            );
        }

        $json  = $response->json();
        $raw   = $json['results'] ?? [];

        $items = array_map(function (array $place) use ($category, $location): array {
            $loc = $place['location'] ?? [];

            return [
                'provider'       => 'foursquare',
                'external_id'    => $place['fsq_id'] ?? null,
                'name'           => $place['name'] ?? 'Unknown business',
                'address_line_1' => $loc['address'] ?? null,
                'address_line_2' => $loc['address_extended'] ?? null,
                'city'           => $loc['locality'] ?? $loc['cross_street'] ?? null,
                'region'         => $loc['region'] ?? null,
                'postcode'       => $loc['postcode'] ?? null,
                'country'        => $loc['country'] ?? $location->country,
                'latitude'       => $place['geocodes']['main']['latitude'] ?? null,
                'longitude'      => $place['geocodes']['main']['longitude'] ?? null,
                'phone'          => $place['tel'] ?? null,
                'category'       => $place['categories'][0]['name'] ?? $category->name,
                'provider_url'   => $place['link'] ?? null,
                'website'        => $place['website'] ?? null,
                'email'          => $place['email'] ?? null,
                'raw'            => $place,
            ];
        }, $raw);

        // Foursquare does not expose a total count — we cap pagination at 1000 like Yelp
        $returned = count($items);
        $total    = ($returned === $limit) ? min($offset + $limit + 1, 1000) : $offset + $returned;
        $hasMore  = $returned === $limit && ($offset + $limit) < 950;

        return [
            'items'    => $items,
            'total'    => $total,
            'has_more' => $hasMore,
        ];
    }
}
