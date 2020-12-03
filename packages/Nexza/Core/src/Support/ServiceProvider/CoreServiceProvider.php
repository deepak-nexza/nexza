<?php

namespace Nexza\Core\Support\ServiceProvider;

use Illuminate\Support\ServiceProvider;
use Nexza\Core\Support\UrlParamProtector;
use Illuminate\Support\Facades\Request;

class CoreServiceProvider extends ServiceProvider
{


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->make('Illuminate\Contracts\Http\Kernel')
        ->pushMiddleware('Nexza\Core\Support\Middleware\UrlParamRevealerMiddleware');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerUrlProtector();
    }

    /**
     * Register UrlProtector class.
     */
    protected function registerUrlProtector()
    {
        
        $this->app->singleton('urlprotector', function () {
            return new UrlParamProtector();
        });
    }
}
