<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessWebsite extends Model
{
    protected $fillable = ['business_id','url','domain','confidence_score','verification_evidence','status','source'];
    protected function casts(): array { return ['verification_evidence'=>'array','confidence_score'=>'integer']; }
    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
}
