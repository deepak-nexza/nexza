<?php

namespace App\Repositories\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
    ];

     /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        'App\Repositories\Events\HsbcEventsListener',
        'App\Repositories\Events\UserEventsListener',
        'App\Repositories\Events\OtpEventsListener',
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
