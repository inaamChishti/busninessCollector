<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

class SafeHttpClient
{
    public function __construct(private readonly SafeUrlService $urls) {}

    public function get(string $url, int $maxRedirects = 5): Response
    {
        for ($redirects = 0; $redirects <= $maxRedirects; $redirects++) {
            $url = $this->urls->assertPublicHttpUrl($url);
            $rpm = (int) Setting::valueOf('max_crawl_requests_per_minute', 30);
            if (RateLimiter::tooManyAttempts('crawler:global', max(1, $rpm))) throw new RuntimeException('Crawler rate limit reached; retry later.');
            RateLimiter::hit('crawler:global', 60);
            $delay = max(0, (int) Setting::valueOf('request_delay_ms', 750));
            if ($delay > 0) usleep($delay * 1000);
            $response = Http::withHeaders(['User-Agent'=>'BusinessEmailCollector/1.0 (+'.config('app.url').')','Accept'=>'text/html,application/xhtml+xml,text/plain;q=0.8,*/*;q=0.1'])
                ->timeout((int) Setting::valueOf('request_timeout', 15))->withOptions(['allow_redirects'=>false,'verify'=>true])
                ->retry((int)Setting::valueOf('retry_attempts',3),fn(int$attempt)=>min((int)Setting::valueOf('retry_delay',60)*1000*(2**($attempt-1)),300000),fn(\Throwable$e)=>$e instanceof ConnectionException||($e instanceof RequestException&&($e->response->status()===429||$e->response->serverError())),throw:false)->get($url);
            if($response->status()===429)throw new RuntimeException('Website returned HTTP 429; retry after the configured delay.');
            if (! in_array($response->status(), [301,302,303,307,308], true)) return $response;
            $next = $response->header('Location');
            if (! $next) return $response;
            $url = $this->absoluteUrl($url, $next);
        }
        throw new RuntimeException('Too many redirects.');
    }

    private function absoluteUrl(string $base, string $next): string
    {
        if (preg_match('#^https?://#i', $next)) return $next;
        $parts = parse_url($base); $origin = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
        if (str_starts_with($next, '//')) return $parts['scheme'].':'.$next;
        if (str_starts_with($next, '/')) return $origin.$next;
        $path = isset($parts['path']) ? preg_replace('#/[^/]*$#', '/', $parts['path']) : '/';
        return $origin.$path.$next;
    }
}
