<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Str;

class WebsiteVerificationService
{
    /** @return array{score:int,evidence:array<string,bool>} */
    public function score(Business $business, array $result): array
    {
        $haystack = Str::lower(implode(' ', [$result['url'] ?? '',$result['title'] ?? '',$result['snippet'] ?? '']));
        $host = Str::lower((string) parse_url($result['url'] ?? '', PHP_URL_HOST));
        $normalize = fn (?string $v) => preg_replace('/[^a-z0-9]+/', '', Str::lower((string) $v));
        $name = $normalize($business->business_name); $hostNorm = $normalize(preg_replace('/\.(co\.uk|org\.uk|com|net|org)$/', '', preg_replace('/^www\./', '', $host)));
        $evidence = [
            'business_name' => $name !== '' && (str_contains($normalize($haystack), $name) || str_contains($hostNorm, $name) || str_contains($name, $hostNorm)),
            'city' => filled($business->city) && str_contains($haystack, Str::lower($business->city)),
            'postcode' => filled($business->postcode) && str_contains($normalize($haystack), $normalize($business->postcode)),
            'telephone' => filled($business->phone) && str_contains($normalize($haystack), $normalize($business->phone)),
            'address' => filled($business->address_line_1) && str_contains($normalize($haystack), $normalize($business->address_line_1)),
            'domain_similarity' => $name !== '' && $hostNorm !== '' && (str_contains($name, $hostNorm) || str_contains($hostNorm, mb_substr($name, 0, min(10, mb_strlen($name))))),
        ];
        $weights = ['business_name'=>35,'city'=>10,'postcode'=>20,'telephone'=>20,'address'=>10,'domain_similarity'=>15];
        $score = 0; foreach ($evidence as $key=>$matched) if ($matched) $score += $weights[$key];
        foreach (['yelp.','facebook.','instagram.','linkedin.','tripadvisor.','companieshouse.'] as $directory) if (str_contains($host, $directory)) $score -= 40;
        return ['score'=>max(0,min(100,$score)),'evidence'=>$evidence];
    }
}
