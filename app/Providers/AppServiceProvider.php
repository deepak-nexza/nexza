<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Nexza\Core\Routing\UrlGenerator;
use Nexza\Core\Support\DomainMasking;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
//        if($this->app->environment('production')) {
//           \URL::forceScheme('https');
//        }
        /**
         * Somehow PHP is not able to write in default /tmp directory and SwiftMailer was failing.
         * To overcome this situation, we set the TMPDIR environment variable to a new value.
         */
        putenv('TMPDIR='.storage_path().'/tmp');

        /**
         * Unset the MAGIC environment variable to search MIME type from PHP's own database
         */
        putenv('MAGIC');

        $this->setUrlMask();
    }

    /**
     * Set reverse proxy details, if it is there.
     */
    protected function setUrlMask()
    {
        $requestedDomain = request()->server('HTTP_HOST');
        $maskedDomain = DomainMasking::maskDomain($requestedDomain);

        if ($maskedDomain && ($requestedDomain !== $maskedDomain)) {
            $urlParts = parse_url($maskedDomain);

            URL::forceRootUrl($maskedDomain);
            URL::forceSchema($urlParts['scheme']);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->setCustomUrlGenerator();
    }

    /**
     * Set our custom url generator here.
     *
     * @return void
     */
    protected function setCustomUrlGenerator()
    {
        $this->app->instance('url', new UrlGenerator(
            $this->app['router']->getRoutes(),
            $this->app->make('request')
        ));
    }
}