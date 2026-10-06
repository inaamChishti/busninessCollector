<?php

namespace App\Providers;

use App\Contracts\BusinessDiscoveryProviderInterface;
use App\Contracts\WebsiteSearchProviderInterface;
use App\Models\Setting;
use App\Services\BusinessDiscovery\FoursquareBusinessDiscoveryProvider;
use App\Services\BusinessDiscovery\YelpBusinessDiscoveryProvider;
use App\Services\WebsiteSearch\GoogleCustomSearchProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BusinessDiscoveryProviderInterface::class, function () {
            $provider = Setting::valueOf('business_provider', 'yelp');

            return match ($provider) {
                'foursquare' => new FoursquareBusinessDiscoveryProvider(),
                default      => new YelpBusinessDiscoveryProvider(),
            };
        });

        $this->app->bind(WebsiteSearchProviderInterface::class, GoogleCustomSearchProvider::class);
    }

    public function boot(): void {}
}
