<?php

namespace Nexza\Core\Foundation;

use Illuminate\Foundation\Application as BaseApplication;
use Nexza\Core\Support\ServiceProvider\CoreServiceProvider;

class Application extends BaseApplication
{
    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        parent::registerBaseServiceProviders();
        
        $this->register(new CoreServiceProvider($this));
    }
}
