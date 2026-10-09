<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GooglePlaceEmail extends Model
{
    protected $table    = 'google_place_emails';
    protected $fillable = ['google_place_id', 'email', 'source_url'];

    public function place(): BelongsTo
    {
        return $this->belongsTo(GooglePlace::class, 'google_place_id');
    }
}
