<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CollectionJob extends Model
{
    use HasUuids;
    protected $fillable = ['uuid','type','status','subject_type','subject_id','attempts','progress_current','progress_total','payload','last_error','started_at','completed_at'];
    protected function casts(): array { return ['payload'=>'array','started_at'=>'datetime','completed_at'=>'datetime']; }
    public function uniqueIds(): array { return ['uuid']; }
}
