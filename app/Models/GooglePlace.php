<?php

namespace App\Models;

use App\Models\GooglePlaceEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GooglePlace extends Model
{
    protected $table = 'google_places';

    protected $fillable = [
        'place_id','name','search_query','search_location','search_type',
        'formatted_phone_number','international_phone_number','website',
        'formatted_address','street_number','route','sublocality','locality',
        'administrative_area_level_1','administrative_area_level_2','country',
        'postal_code','postal_town',
        'lat','lng','plus_code','compound_code',
        'types','primary_type','business_status','price_level',
        'rating','user_ratings_total','permanently_closed',
        'open_now','opening_hours','current_opening_hours','secondary_opening_hours',
        'editorial_summary','icon','icon_background_color','icon_mask_base_uri',
        'photos','url','vicinity',
        'wheelchair_accessible_entrance','delivery','dine_in','takeout',
        'reservable','serves_breakfast','serves_lunch','serves_dinner',
        'serves_beer','serves_wine','serves_brunch','serves_vegetarian_food',
        'reviews','utc_offset','utc_offset_minutes',
        'address_components','plus_code_full','fetched_at',
    ];

    protected $casts = [
        'types'                    => 'array',
        'opening_hours'            => 'array',
        'current_opening_hours'    => 'array',
        'secondary_opening_hours'  => 'array',
        'photos'                   => 'array',
        'reviews'                  => 'array',
        'address_components'       => 'array',
        'plus_code_full'           => 'array',
        'permanently_closed'       => 'boolean',
        'open_now'                 => 'boolean',
        'wheelchair_accessible_entrance' => 'boolean',
        'delivery'                 => 'boolean',
        'dine_in'                  => 'boolean',
        'takeout'                  => 'boolean',
        'reservable'               => 'boolean',
        'serves_breakfast'         => 'boolean',
        'serves_lunch'             => 'boolean',
        'serves_dinner'            => 'boolean',
        'serves_beer'              => 'boolean',
        'serves_wine'              => 'boolean',
        'serves_brunch'            => 'boolean',
        'serves_vegetarian_food'   => 'boolean',
        'lat'                      => 'float',
        'lng'                      => 'float',
        'rating'                   => 'float',
        'fetched_at'               => 'datetime',
    ];

    public function emails(): HasMany
    {
        return $this->hasMany(GooglePlaceEmail::class, 'google_place_id');
    }
}
