<?php

namespace App\Services;

use Illuminate\Support\Str;

class EmailExtractionService
{
    /** @return array<int,array{email:string,is_generic:bool,type:string}> */
    public function extract(string $html): array
    {
        $text = html_entity_decode(strip_tags(str_replace(['(at)','[at]',' at ','&#64;'], '@', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s*@\s*/', '@', $text);
        preg_match_all('/(?<![\w.%-])([a-z0-9.!#$%&\'*+\/=\?^_`{|}~-]+@[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)+)/i', $text.' '.$html, $matches);
        $results = [];
        foreach ($matches[1] ?? [] as $raw) {
            $email = Str::lower(trim(html_entity_decode($raw), "<>[](){}.,;:\"' \t\n\r\0\x0B"));
            if (! $this->valid($email)) continue;
            [$local] = explode('@', $email, 2);
            $genericNames = ['info','contact','enquiries','enquiry','office','admin','sales','hello','support','customerservice','reception'];
            $results[$email] = ['email'=>$email,'is_generic'=>in_array($local,$genericNames,true),'type'=>in_array($local,$genericNames,true) ? $local : 'named'];
        }
        return array_values($results);
    }

    public function valid(string $email): bool
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) return false;
        $lower = Str::lower($email);
        foreach (['example.com','example.org','email.com','domain.com','sentry.io','wixpress.com'] as $fake) if (str_ends_with($lower, '@'.$fake)) return false;
        foreach (['.png','.jpg','.jpeg','.gif','.svg','.webp','.css','.js'] as $ext) if (str_ends_with($lower, $ext)) return false;
        $domain = substr(strrchr($lower, '@'), 1);
        return $domain !== '' && (app()->environment('testing') || checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A'));
    }
}
