<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id','action','description','subject_type','subject_id','metadata','ip_address'];
    protected function casts(): array { return ['metadata'=>'array']; }
}
