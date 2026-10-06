<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSearch extends Model
{
    protected $fillable = ['location_id','business_category_id','provider','offset','status','results_count','error'];
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function category(): BelongsTo { return $this->belongsTo(BusinessCategory::class, 'business_category_id'); }
}
