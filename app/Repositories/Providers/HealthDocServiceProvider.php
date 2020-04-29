<?php
namespace App\Repositories\Providers;


use Illuminate\Support\ServiceProvider;


class HealthDocServiceProvider extends ServiceProvider
{

    /**

     * Bootstrap the application services.

     *

     * @return void

     */

    public function boot()

    {

        

    }


    /**

     * Register the application services.

     *

     * @return void

     */

    public function register()

    {
        
        $this->app->bind('App\Repositories\User\UserInterface', 'App\Repositories\User\UserRepository');
        $this->app->bind('App\Repositories\Event\EventInterface', 'App\Repositories\Event\EventRepository');

    }

}