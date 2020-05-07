<?php

namespace Nexza\Otp\Events;

use App\Libraries\Sms\Sms;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Lang;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Factory\Events\BaseEvent;
use App\Repositories\Models\Master\EmailTemplate;
use App\Repositories\Models\Master\SmsTemplate;

class OtpEventsListener extends BaseEvent
{
   

    use SerializesModels;


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
      $otp_content = EmailTemplate::getEmailTemplate("OTP");

      if ($otp_content) {
      $mail_body = str_replace(
      ['%otp'], [$user['otp']], $otp_content->mail_body
      );
      $sent = Mail::send('email', [
      'varContent' => $mail_body,  'to' => $user["email"]
      ], function ($message) use ($user, $otp_content) {

      $message->to($user["email"], $user["first_name"])->subject($otp_content->mail_subject);

      if (!empty($otp_content->reciepient_cc)) {
      $message->cc($otp_content->reciepient_cc);
      }
      if (!empty($otp_content->reciepient_bcc)) {
      $message->bcc($otp_content->reciepient_bcc);
      }
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
     * Sms event that would be fired @register and @logintime
     *
     * @param object $user User object on registration and login
     *
     * @since 0.1
     */
    public function sendOtp1($user)
    {

        $user = unserialize($user);

        $otp_status = $user['otp_status'];

        /**
         * Get Email and SMS Template from Database
         */
        $sms_content = SmsTemplate::getSmsTemplate("OTP");
        if ($sms_content) {
            $sms_body = str_replace(
                ['%otp','%FirstName'],
                [$user['otp'],ucwords($user['first_name'])],
                $sms_content->sms_body
            );
            if ($otp_status === "Signup OTP") {
                $msg['success'] = "OTP SMS Sent";
                $msg['fail'] = "OTP SMS Failed";
            } else {
                $msg['success'] = "OTP SMS resent.";
                $msg['fail'] = "OTP SMS resent Failed";
            }

            // Send SMS.
            Sms::sendSms($otp_status, $sms_body, $user['mobile_number'], $user['user_id'], $msg);
        }
    }
}
