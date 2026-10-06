<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrawlPage extends Model
{
    protected $fillable = ['business_id','url','url_hash','depth','http_status','status','content_type','has_contact_form','error','crawled_at'];
    protected function casts(): array { return ['has_contact_form'=>'boolean','crawled_at'=>'datetime']; }
}
