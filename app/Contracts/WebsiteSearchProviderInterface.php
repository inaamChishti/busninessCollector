<?php

namespace App\Contracts;

use App\Models\Business;

interface WebsiteSearchProviderInterface
{
    public function key(): string;
    public function configured(): bool;
    /** @return array<int,array{url:string,title:string,snippet:string}> */
    public function search(Business $business): array;
}
