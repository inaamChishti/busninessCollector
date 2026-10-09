<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google_search' => [
        'key' => env('GOOGLE_SEARCH_KEY'),
        'engine_id' => env('GOOGLE_SEARCH_ENGINE_ID'),
    ],

    'yelp' => [
        'key' => env('YELP_API_KEY'),
    ],

    'foursquare' => [
        'key' => env('FOURSQUARE_API_KEY'),
    ],

    'companies_house' => [
        'key'      => env('COMPANIES_HOUSE_API_KEY'),
        'base_url' => 'https://api.company-information.service.gov.uk',
    ],

    'google_places' => [
        'key' => env('GOOGLE_PLACES_API_KEY'),
    ],

];
