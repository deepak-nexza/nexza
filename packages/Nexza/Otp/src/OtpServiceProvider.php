<?php

namespace Nexza\Otp;

use Illuminate\Support\ServiceProvider;

/**
 * class OtpServiceProvider
 *
 * @package Otp
 */
class OtpServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the service provider
     *
     * @return null
     */


    public function boot()
    {
        // Publish configuration files
        require_once __DIR__ . '/Http/Routes/otp.php';

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'otp');

        $this->loadViewsFrom(__DIR__.'/../views', 'otp');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
         // Bind Otp Interfaces
        $this->app->bind(
            'Nexza\Otp\Repositories\Otp\OtpInterface',
            'Nexza\Otp\Repositories\OtpRepository'
        );

        foreach (glob(__DIR__ . '/Helpers/*.php') as $eachFile) {
            require_once $eachFile;
        }
    }
}
