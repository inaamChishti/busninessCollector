<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Location extends Model
{
    protected $fillable = ['import_id','postcode','postcode_normalized','city','country','status','businesses_discovered','started_at','completed_at','last_processed_at','last_error'];
    protected function casts(): array { return ['started_at'=>'datetime','completed_at'=>'datetime','last_processed_at'=>'datetime']; }
    public function import(): BelongsTo { return $this->belongsTo(ImportRecord::class, 'import_id'); }
    public function businesses(): BelongsToMany { return $this->belongsToMany(Business::class, 'location_business')->withPivot(['searched_postcode','business_category_id'])->withTimestamps(); }
    public static function normalizePostcode(string $postcode): string { return strtoupper(preg_replace('/\s+/', '', trim($postcode))); }
}
