<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WikidataBusiness extends Model
{
    protected $table = 'wikidata_businesses';

    protected $fillable = [
        'name', 'website', 'phone', 'address', 'city', 'category',
    ];

    public function emails(): HasMany
    {
        return $this->hasMany(WikidataEmail::class, 'wikidata_business_id');
    }
}
