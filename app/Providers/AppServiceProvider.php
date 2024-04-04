<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        if(strpos(env('APP_URL'), 'https') === 0 || env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }
    }
}
