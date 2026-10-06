<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Business extends Model
{
    use SoftDeletes;
    protected $fillable = ['external_provider','external_business_id','business_name','address_line_1','address_line_2','city','region','postcode','country','latitude','longitude','phone','primary_category','provider_url','official_website','website_domain','website_confidence_score','website_verification_evidence','website_origin','website_manually_verified','primary_email','website_status','email_status','has_contact_form','contact_form_url','discovered_at','website_checked_at','email_checked_at'];
    protected function casts(): array { return ['latitude'=>'decimal:7','longitude'=>'decimal:7','website_verification_evidence'=>'array','website_manually_verified'=>'boolean','has_contact_form'=>'boolean','discovered_at'=>'datetime','website_checked_at'=>'datetime','email_checked_at'=>'datetime']; }
    public function emails(): HasMany { return $this->hasMany(BusinessEmail::class); }
    public function websites(): HasMany { return $this->hasMany(BusinessWebsite::class); }
    public function sources(): HasMany { return $this->hasMany(BusinessSource::class); }
    public function crawlPages(): HasMany { return $this->hasMany(CrawlPage::class); }
    public function locations(): BelongsToMany { return $this->belongsToMany(Location::class, 'location_business')->withPivot(['searched_postcode','business_category_id'])->withTimestamps(); }
}
