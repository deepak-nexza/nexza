<?php

namespace App\Repositories\Events;

use Mail;
use Helpers;
use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Factory\Events\BaseEvent;
use App\Repositories\Models\Master\EmailTemplate;
use Illuminate\Events\Dispatcher;

class OtpEventsListener extends BaseEvent
{
   

    use SerializesModels;
     /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
//
    }
 

    
    /**
     * Event that would be fired for sending OTP, when first time user registers
     *
     * @param object $user User object on registration
     *
     * @since 0.1
     */
    public function sendOtp($user)
      {
      $user = unserialize($user);
      $expName = explode('@', $user["email"]);
      $name = $expName[0];
      $otp_content = EmailTemplate::getEmailTemplate("OTP");
      if ($otp_content) {
      $mail_body = str_replace(
      ['%name', '%otp', '%tollnumber'], [ucwords($name), $user['otp'], config('b2c_common.TOLL_FREE_NUMBER2')], $otp_content->en_mail_body
      );
     $sent = Mail::send('email', ['varContent' => $mail_body,  'to' => $user["email"]
                    ], function ($message) use ($user, $otp_content) {
                        $message->from(config('common.FROM_EMAIL'), config('common.FROM_EMAIL'));
                        $message->to($user["email"])->subject($otp_content->en_mail_subject);
                    });
      if ($sent) {
      return true;
      }
      return false;
      } else {
      //self::addActivityLog(config('b2c_common.activity_type.registration_success'), trans('activity_messages.mail_not_send'), $user);
      }
      }
       
    /**
     * Event subscribers
     *
     * @param mixed $events
     */
    public function subscribe($events)
    { 
        $events->listen(
            'otp.sendotp',
            'Nexza\Otp\Events\OtpEventsListener@sendOtp'
        );
        
        $events->listen(
            'otp.finalRegister',
            'Nexza\Otp\Events\OtpEventsListener@finalRegister'
        );
    }
}
