<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportRecord extends Model
{
    protected $table = 'exports';
    protected $fillable = ['user_id','format','status','filters','filename','row_count','error','completed_at'];
    protected function casts(): array { return ['filters'=>'array','completed_at'=>'datetime']; }
}
