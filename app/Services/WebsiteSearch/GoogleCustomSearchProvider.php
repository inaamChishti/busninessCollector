<?php

namespace App\Services\WebsiteSearch;

use App\Contracts\WebsiteSearchProviderInterface;
use App\Models\Business;
use App\Models\Setting;
use App\Support\ProviderNotConfiguredException;
use App\Support\ProviderQuotaExceededException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

class GoogleCustomSearchProvider implements WebsiteSearchProviderInterface
{
    public function key(): string { return 'google_cse'; }
    private function apiKey(): ?string { return Setting::valueOf('google_search_api_key', config('services.google_search.key')); }
    private function engineId(): ?string { return Setting::valueOf('google_search_engine_id', config('services.google_search.engine_id')); }
    public function configured(): bool { return filled($this->apiKey()) && filled($this->engineId()); }

    public function search(Business $business): array
    {
        if (! $this->configured()) throw new ProviderNotConfiguredException('Google Programmable Search is not configured. Add the API key and engine ID in Settings.');
        $rpm = (int) Setting::valueOf('max_search_requests_per_minute', 30);
        if (RateLimiter::tooManyAttempts('provider:google_cse', max(1, $rpm))) throw new ProviderQuotaExceededException('Local website-search rate limit reached.');
        RateLimiter::hit('provider:google_cse', 60);
        $query = '"'.$business->business_name.'" '.implode(' ', array_filter([$business->city,$business->postcode,$business->phone]));
        $response = Http::acceptJson()->timeout((int) Setting::valueOf('request_timeout', 15))->get('https://customsearch.googleapis.com/customsearch/v1', ['key'=>$this->apiKey(),'cx'=>$this->engineId(),'q'=>$query,'num'=>5]);
        if ($response->status() === 429) throw new ProviderQuotaExceededException('Google search quota exceeded.');
        if ($response->failed()) throw new RuntimeException('Website search returned HTTP '.$response->status().'.');
        return array_values(array_map(fn (array $item) => ['url'=>(string)($item['link'] ?? ''),'title'=>(string)($item['title'] ?? ''),'snippet'=>(string)($item['snippet'] ?? '')], $response->json('items', [])));
    }
}
