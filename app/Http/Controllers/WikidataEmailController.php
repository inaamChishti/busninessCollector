<?php

namespace App\Http\Controllers;

use App\Models\WikidataBusiness;
use App\Models\WikidataEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WikidataEmailController extends Controller
{
    // ── Email extraction page ───────────────────────────────────────
    public function index()
    {
        $query = WikidataBusiness::withCount('emails')->with('emails')->latest();

        if (request()->filled('city'))     $query->where('city', request('city'));
        if (request()->filled('category')) $query->where('category', request('category'));
        if (request()->filled('search'))   $query->where('name', 'like', '%'.request('search').'%');

        $businesses = $query->paginate(50)->withQueryString();
        $cities     = WikidataBusiness::distinct()->orderBy('city')->pluck('city');
        $categories = WikidataBusiness::distinct()->orderBy('category')->pluck('category');

        return view('wikidata.emails', compact('businesses', 'cities', 'categories'));
    }

    // ── Scrape emails from a website (AJAX: single business) ────────
    public function scrape(Request $request)
    {
        $request->validate([
            'website'     => 'required|url|max:500',
            'business_id' => 'nullable|integer|exists:wikidata_businesses,id',
        ]);

        $baseUrl = rtrim($request->input('website'), '/');
        $emails  = [];
        $visited = [];

        // Crawl up to 8 pages (homepage + linked pages)
        $queue = [$baseUrl];
        $maxPages = 8;

        while (!empty($queue) && count($visited) < $maxPages) {
            $url = array_shift($queue);

            // Normalise — only same-origin pages
            if (!$this->sameDomain($baseUrl, $url)) continue;
            if (in_array($url, $visited))           continue;

            $visited[] = $url;

            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; BusinessCollector/1.0)',
                    'Accept'     => 'text/html,application/xhtml+xml',
                ])->timeout(10)->get($url);
            } catch (\Throwable $e) {
                continue;
            }

            if (!$response->successful()) continue;

            $body = $response->body();

            // Extract emails from this page
            preg_match_all('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $body, $matches);
            foreach ($matches[0] as $email) {
                $email = strtolower(trim($email));
                // Skip image filenames, font names, common false positives
                if (str_ends_with($email, '.png') || str_ends_with($email, '.jpg') ||
                    str_ends_with($email, '.gif') || str_ends_with($email, '.woff') ||
                    str_contains($email, 'sentry') || str_contains($email, 'example.com')) {
                    continue;
                }
                $emails[$email] = $url;  // email => source_url
            }

            // Queue internal links (contact, about pages prioritised)
            if (count($visited) < $maxPages) {
                preg_match_all('/<a[^>]+href=["\']([^"\'#?]+)["\'][^>]*>/i', $body, $links);
                foreach ($links[1] as $href) {
                    $href = $this->absoluteUrl($baseUrl, $href);
                    if ($href && $this->sameDomain($baseUrl, $href) && !in_array($href, $visited)) {
                        // Prioritise contact/about pages
                        $lower = strtolower($href);
                        if (str_contains($lower, 'contact') || str_contains($lower, 'about') ||
                            str_contains($lower, 'reach') || str_contains($lower, 'support')) {
                            array_unshift($queue, $href);
                        } else {
                            $queue[] = $href;
                        }
                    }
                }
                // Dedupe queue
                $queue = array_values(array_unique($queue));
            }
        }

        $result = collect($emails)->map(fn($src, $email) => [
            'email'      => $email,
            'source_url' => $src,
        ])->values()->all();

        return response()->json([
            'status'       => 'success',
            'website'      => $baseUrl,
            'pages_crawled'=> count($visited),
            'total'        => count($result),
            'data'         => $result,
        ]);
    }

    // ── Store scraped emails to DB ───────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|integer|exists:wikidata_businesses,id',
            'emails'      => 'required|array|min:1',
            'emails.*.email' => 'required|email|max:255',
        ]);

        $businessId = $request->input('business_id');
        $inserted   = 0;
        $skipped    = 0;

        foreach ($request->input('emails') as $row) {
            $email     = strtolower(trim($row['email']));
            $sourceUrl = trim($row['source_url'] ?? '') ?: null;

            $exists = WikidataEmail::where('wikidata_business_id', $businessId)
                ->where('email', $email)
                ->exists();

            if ($exists) { $skipped++; continue; }

            WikidataEmail::create([
                'wikidata_business_id' => $businessId,
                'email'                => $email,
                'source_url'           => $sourceUrl,
            ]);
            $inserted++;
        }

        // Reload stored emails for this business so the UI can refresh
        $stored = WikidataEmail::where('wikidata_business_id', $businessId)
            ->orderBy('email')
            ->get(['id','email','source_url']);

        return response()->json([
            'status'   => 'success',
            'inserted' => $inserted,
            'skipped'  => $skipped,
            'message'  => "{$inserted} saved, {$skipped} already existed.",
            'stored'   => $stored,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────
    private function sameDomain(string $base, string $url): bool
    {
        $baseParsed = parse_url($base);
        $urlParsed  = parse_url($url);
        return isset($urlParsed['host'])
            && isset($baseParsed['host'])
            && strtolower($urlParsed['host']) === strtolower($baseParsed['host']);
    }

    private function absoluteUrl(string $base, string $href): ?string
    {
        $href = trim($href);
        if (!$href || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:') ||
            str_starts_with($href, 'javascript:')) {
            return null;
        }
        if (str_starts_with($href, 'http://') || str_starts_with($href, 'https://')) {
            return $href;
        }
        $parsed = parse_url($base);
        $scheme = $parsed['scheme'] ?? 'https';
        $host   = $parsed['host']   ?? '';
        if (str_starts_with($href, '//')) return $scheme . ':' . $href;
        if (str_starts_with($href, '/'))  return $scheme . '://' . $host . $href;
        // Relative path
        $path = isset($parsed['path']) ? dirname($parsed['path']) : '';
        return $scheme . '://' . $host . '/' . ltrim($path . '/' . $href, '/');
    }
}
