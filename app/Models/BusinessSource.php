<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSource extends Model
{
    protected $fillable = ['business_id','provider','provider_business_id','provider_url','raw_snapshot'];
    protected function casts(): array { return ['raw_snapshot'=>'array']; }
    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
}
