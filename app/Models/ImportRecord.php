<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportRecord extends Model
{
    protected $table = 'imports';
    protected $fillable = ['user_id','filename','status','total_rows','imported_rows','duplicate_rows','failed_rows','errors'];
    protected function casts(): array { return ['errors'=>'array']; }
}
