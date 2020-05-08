<?php

namespace App\Repositories\Events;

use Mail;
use App;
use Auth;
use Helpers;
use Exception;
use App\Repositories\Models\User;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Factory\Events\BaseEvent;
use App\Repositories\Models\Master\EmailTemplate;
use App\Repositories\Models\Master\SmsTemplate;
use Twilio;

class HsbcEventsListener extends BaseEvent
{

    use SerializesModels;

    private $phoneno_prefix = '';
    private $default_receiver_phoneno = '';

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->phoneno_prefix = getenv('PHONE_PREFIX') ?: '';
        $this->default_receiver_phoneno = getenv('DEFAULT_RECEIVER') ?: '';
    }

    /**
     * Event that would be fired on a new registration
     *
     * @param object $user User object on registration
     *
     * @since 0.1
     */
    public function onTest($user)
    {
        

    }


    /**
     * Event subscribers
     *
     * @param mixed $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'hsbc.registered',
            'App\Repositories\Events\HsbcEventsListener@onTest'
        );
    }

}
