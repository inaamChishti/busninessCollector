<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCategory extends Model
{
    protected $fillable = ['name','api_alias','search_term','enabled','priority'];
    protected function casts(): array { return ['enabled'=>'boolean','priority'=>'integer']; }
}
