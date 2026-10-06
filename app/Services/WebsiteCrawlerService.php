<?php

namespace App\Services;

use App\Models\Business;
use App\Models\CrawlPage;
use App\Models\Setting;
use DOMDocument;
use DOMXPath;

class WebsiteCrawlerService
{
    public function __construct(private readonly SafeHttpClient $http, private readonly SafeUrlService $urls, private readonly RobotsService $robots) {}

    /** @return array{pages:int,documents:array<int,array{url:string,html:string,has_contact_form:bool}>,contact_form_url:?string,blocked:bool,request_failed:bool} */
    public function crawl(Business $business): array
    {
        $root = rtrim((string) $business->official_website, '/');
        $maxPages = max(1, min(50, (int) Setting::valueOf('max_pages_per_website', 12)));
        $maxDepth = max(0, min(5, (int) Setting::valueOf('max_crawl_depth', 2)));
        $queue = [[$root,0],[$root.'/contact',1],[$root.'/contact-us',1],[$root.'/about',1],[$root.'/about-us',1],[$root.'/team',1],[$root.'/privacy-policy',1]];
        $seen = []; $documents = []; $contactUrl = null; $blocked = false; $requestFailed = false;
        while ($queue && count($seen) < $maxPages) {
            [$url,$depth] = array_shift($queue); $hash = hash('sha256', $url);
            if (isset($seen[$hash]) || $depth > $maxDepth || ! $this->urls->sameRegistrableHost($root, $url)) continue;
            $seen[$hash] = true;
            $page = CrawlPage::firstOrNew(['business_id'=>$business->id,'url_hash'=>$hash]);
            $page->fill(['url'=>$url,'depth'=>$depth,'status'=>'processing']); $page->save();
            try {
                if (! $this->robots->allows($url)) { $page->update(['status'=>'blocked','error'=>'Blocked by robots.txt','crawled_at'=>now()]); $blocked = true; continue; }
                $response = $this->http->get($url);
                $type = strtolower((string) $response->header('Content-Type'));
                $page->http_status = $response->status(); $page->content_type = mb_substr($type,0,190);
                if (! $response->successful() || (! str_contains($type, 'text/html') && ! str_contains($type, 'text/plain'))) { $page->status='failed'; $page->error='HTTP '.$response->status().' or unsupported content type'; $page->crawled_at=now(); $page->save(); $requestFailed=true; continue; }
                $html = mb_substr($response->body(), 0, 2_000_000);
                [$links,$hasForm] = $this->inspect($url,$html);
                if ($hasForm && ! $contactUrl) $contactUrl = $url;
                $documents[] = ['url'=>$url,'html'=>$html,'has_contact_form'=>$hasForm];
                $page->fill(['status'=>'completed','has_contact_form'=>$hasForm,'error'=>null,'crawled_at'=>now()])->save();
                foreach ($links as $link) if ($depth < $maxDepth && $this->urls->sameRegistrableHost($root,$link)) $queue[] = [$link,$depth+1];
            } catch (\Throwable $e) { $page->fill(['status'=>'failed','error'=>mb_substr($e->getMessage(),0,1000),'crawled_at'=>now()])->save(); $requestFailed=true; }
        }
        return ['pages'=>count($documents),'documents'=>$documents,'contact_form_url'=>$contactUrl,'blocked'=>$blocked && $documents===[],'request_failed'=>$requestFailed && $documents===[]];
    }

    /** @return array{0:array<int,string>,1:bool} */
    private function inspect(string $base, string $html): array
    {
        $dom = new DOMDocument(); libxml_use_internal_errors(true); $dom->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET); libxml_clear_errors();
        $xpath = new DOMXPath($dom); $links=[];
        foreach ($xpath->query('//a[@href]') ?: [] as $node) {
            $href = trim($node->getAttribute('href')); $label = strtolower(trim($node->textContent.' '.$href));
            if ($href === '' || str_starts_with($href,'mailto:') || str_starts_with($href,'tel:') || str_starts_with($href,'#')) continue;
            if (! preg_match('/contact|about|team|email|enquir|support|customer|office|privacy|terms/', $label)) continue;
            $absolute = $this->absolute($base,$href); if ($absolute) $links[$absolute] = $absolute;
        }
        $hasForm = ($xpath->query('//form[.//input or .//textarea]')?->length ?? 0) > 0;
        return [array_values($links),$hasForm];
    }

    private function absolute(string $base, string $href): ?string
    {
        if (preg_match('#^https?://#i',$href)) return preg_replace('/#.*$/','',$href);
        $p=parse_url($base); if (!isset($p['scheme'],$p['host'])) return null; $origin=$p['scheme'].'://'.$p['host'].(isset($p['port'])?':'.$p['port']:'');
        if (str_starts_with($href,'//')) return $p['scheme'].':'.preg_replace('/#.*$/','',$href);
        if (str_starts_with($href,'/')) return $origin.preg_replace('/#.*$/','',$href);
        $dir=preg_replace('#/[^/]*$#','/',$p['path'] ?? '/'); return $origin.$dir.preg_replace('/#.*$/','',$href);
    }
}
