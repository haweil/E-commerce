<?php

namespace App\Providers;

use App\Models\Configuration;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $configuration = Configuration::first();

        if ($configuration) {
            Config::set('services.google.client_id', $configuration->google_client_id);
            Config::set('services.google.client_secret', $configuration->google_client_secret);
            Config::set('services.google.redirect', $configuration->google_redirect_uri);
            Config::set('services.stripe.key', $configuration->stripe_public_key);
            Config::set('services.stripe.secret', $configuration->stripe_secret_key);
        }
    }
}
