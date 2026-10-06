<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DuplicateCandidate extends Model
{
    protected $fillable = ['business_id','possible_duplicate_id','confidence_score','matching_fields','status'];
    protected function casts(): array { return ['matching_fields'=>'array']; }
    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function possibleDuplicate(): BelongsTo { return $this->belongsTo(Business::class, 'possible_duplicate_id'); }
}
