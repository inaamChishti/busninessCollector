<?php

namespace App\Contracts;

use App\Models\BusinessCategory;
use App\Models\Location;

interface BusinessDiscoveryProviderInterface
{
    public function key(): string;
    public function configured(): bool;
    /** @return array{items: array<int,array<string,mixed>>, total:int, has_more:bool} */
    public function search(Location $location, BusinessCategory $category, int $offset = 0): array;
}
