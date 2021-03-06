<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();
        
        $this->mapEventRoutes();
        $this->mapAjaxRoutes();
        $this->mapOtpRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
    
     /**
     * Define the "Access" routes for the application.
     *
     * These routes are related to guest session.
     *
     * @return void
     */
    protected function mapApplicationRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/application.php'));
    }
    
     /**
     * Define the "Access" routes for the application.
     *
     * These routes are related to guest session.
     *
     * @return void
     */
    protected function mapEventRoutes()
    {
        Route::prefix('event')
             ->namespace($this->namespace)
             ->group(base_path('routes/event.php'));
    }
    
     /**
     * Define the "Access" routes for the application.
     *
     * These routes are related to guest session.
     *
     * @return void
     */
    protected function mapAjaxRoutes()
    {
        Route::prefix('ajax')
             ->namespace($this->namespace)
             ->group(base_path('routes/ajax.php'));
    }
    
     /**
     * Define the "Access" routes for the application.
     *
     * These routes are related to guest session.
     *
     * @return void
     */
    protected function mapOtpRoutes()
    {
        Route::prefix('otp')
             ->namespace($this->namespace)
             ->group(base_path('routes/otp.php'));
    }
    
}
