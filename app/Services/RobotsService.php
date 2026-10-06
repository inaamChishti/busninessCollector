<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class RobotsService
{
    public function __construct(private readonly SafeHttpClient $http) {}

    public function allows(string $url): bool
    {
        $parts = parse_url($url); $origin = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '');
        $rules = Cache::remember('robots:'.sha1($origin), now()->addHours(12), function () use ($origin) {
            try { $response = $this->http->get($origin.'/robots.txt'); } catch (\Throwable) { return []; }
            if (! $response->successful()) return [];
            $active = false; $rules = [];
            foreach (preg_split('/\r?\n/', $response->body()) as $line) {
                $line = trim(preg_replace('/#.*/', '', $line));
                if (stripos($line, 'user-agent:') === 0) $active = trim(substr($line, 11)) === '*';
                elseif ($active && stripos($line, 'disallow:') === 0 && ($path = trim(substr($line, 9))) !== '') $rules[] = $path;
            }
            return $rules;
        });
        $path = ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
        foreach ($rules as $blocked) if ($blocked !== '/' && str_starts_with($path, $blocked) || $blocked === '/') return false;
        return true;
    }
}
