<?php

namespace App\Services;

use InvalidArgumentException;

class SafeUrlService
{
    private const BLOCKED_HOSTS = ['localhost','localhost.localdomain','metadata.google.internal','instance-data','169.254.169.254'];

    public function assertPublicHttpUrl(string $url): string
    {
        $url = trim($url);
        if (! filter_var($url, FILTER_VALIDATE_URL)) throw new InvalidArgumentException('Invalid URL.');
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        if (! in_array($scheme, ['http','https'], true)) throw new InvalidArgumentException('Only HTTP and HTTPS URLs are allowed.');
        if (isset($parts['user']) || isset($parts['pass'])) throw new InvalidArgumentException('Credential-bearing URLs are not allowed.');
        $host = strtolower(rtrim($parts['host'] ?? '', '.'));
        if ($host === '' || in_array($host, self::BLOCKED_HOSTS, true) || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) throw new InvalidArgumentException('Internal hosts are not allowed.');
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : $this->resolve($host);
        if ($ips === []) throw new InvalidArgumentException('Host did not resolve.');
        foreach ($ips as $ip) if (! $this->isPublicIp($ip)) throw new InvalidArgumentException('The URL resolves to a private or reserved address.');
        return $url;
    }

    /** @return array<int,string> */
    private function resolve(string $host): array
    {
        $records = @dns_get_record($host, DNS_A | DNS_AAAA) ?: [];
        return array_values(array_unique(array_filter(array_map(fn ($r) => $r['ip'] ?? $r['ipv6'] ?? null, $records))));
    }

    public function isPublicIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    public function sameRegistrableHost(string $first, string $second): bool
    {
        $a = strtolower((string) parse_url($first, PHP_URL_HOST)); $b = strtolower((string) parse_url($second, PHP_URL_HOST));
        $a = preg_replace('/^www\./', '', $a); $b = preg_replace('/^www\./', '', $b);
        return $a !== '' && ($a === $b || str_ends_with($a, '.'.$b) || str_ends_with($b, '.'.$a));
    }
}
