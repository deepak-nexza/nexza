<?php

namespace Nexza\Core\Foundation;

use Illuminate\Foundation\Application as BaseApplication;
use Nexza\Core\Support\ServiceProvider\CoreServiceProvider;
use Illuminate\Support\Facades\Request;

class Application extends BaseApplication
{
       protected $non_enc_routes = [
            'event-detail',
            'event_desc',
        ];
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
