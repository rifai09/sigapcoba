<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
// use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use SocialiteProviders\Manager\SocialiteWasCalled;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        if (app()->runningInConsole() === false) {
            $appUrl = config('app.url');
            if ($appUrl) {
                $parsedUrl = parse_url($appUrl);
                if (!empty($parsedUrl['scheme'])) {
                    URL::forceScheme($parsedUrl['scheme']);
                }
                $basePath = '';
                if (isset($parsedUrl['path'])){
                    $basePath = $parsedUrl['path'];
                }
    
                URL::forceRootUrl($appUrl);
    
                // IMPORTANT TO DO PROPERLY FOR NORMAL LOGIN
                // ONLY IF USING LIVEWIRE
                // Livewire::setUpdateRoute(function ($handle) use ($basePath) {
                //     return Route::post($basePath . '/livewire/update', $handle)
                //         ->middleware(config('livewire.middleware_group'));
                // });
            }
        }
        Event::listen(function (SocialiteWasCalled $event) {
             $event->extendSocialite('sso', \SocialiteProviders\Keycloak\Provider::class);
        });
    }
}
