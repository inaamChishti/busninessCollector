<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessEmail extends Model
{
    protected $fillable = ['business_id','email','email_type','source_url','is_primary','is_generic','validation_status','first_seen_at','last_seen_at'];
    protected function casts(): array { return ['is_primary'=>'boolean','is_generic'=>'boolean','first_seen_at'=>'datetime','last_seen_at'=>'datetime']; }
    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
}
