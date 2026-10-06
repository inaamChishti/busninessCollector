<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WikidataEmail extends Model
{
    protected $table = 'wikidata_emails';

    protected $fillable = [
        'wikidata_business_id', 'email', 'source_url',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(WikidataBusiness::class, 'wikidata_business_id');
    }
}
