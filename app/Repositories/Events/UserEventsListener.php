<?php
namespace App\Repositories\Events;

use Mail;
use Helpers;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Factory\Events\BaseEvent;
use App\Repositories\Models\Master\EmailTemplate;

class UserEventsListener extends BaseEvent
{

    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }
 
    /**
     * Event that would be fired on a new registration
     *
     * @param object $user User object on registration
     *
     * @since 0.1
     */
    
    public function onRegistered($user)
    {
        
        $user          = unserialize($user);
        $email_content = EmailTemplate::getEmailTemplate("Backend User Registration");
        $mail_body     = str_replace(
            ['%name', '%username','%loginlink'],
            [ucwords($user['first_name']), $user['email'], URL('password/reset/'.$user['token'])],
            $email_content->mail_body
        );
        try {
            $to         = $user["email"];
            $subject    = $email_content->mail_subject;
            $cc         = $email_content->reciepient_cc;
            $bcc        = $email_content->reciepient_bcc;
            $attachment = null;
            Helpers::sendEmail($to, $subject, $mail_body, $attachment, $cc, $bcc);
        } catch (Exception $ex) {
            if (method_exists($ex, 'getRequest')) {
                return response($ex->getRequest());
            }
        }

        /**
         * Call Activity Event
         */
        self::addActivityLog(1, trans('activity_messages.register_sucessfully'), $user);
    }

    /**
     * Event that would be fired on a registration failed
     *
     * @param array $user email data
     *
     * @since 0.1
     */
    public function onFailedRegistration($user)
    {
        $user = unserialize($user);
        self::addActivityLog(46, trans('activity_messages.registration_failed'), $user);
    }

    /**
     * Event that would be fired on a user login
     *
     * @param object $user Logged in user object
     *
     * @since 0.1
     */
    public function onLoginSuccess($user)
    {
        $user = unserialize($user);
        self::addActivityLog(3, trans('activity_messages.login_sucessfully'), $user);
    }

    /**
     * Event that would be fired on a user logout
     *
     * @param object $user Logged in user object
     *
     * @since 0.1
     */
    public function onLogoutSuccess($user)
    {
        $user = unserialize($user);
        self::addActivityLog(5, trans('activity_messages.logout_sucessfully'), $user);
    }

    /**
     * Event that would be fired on a failed login attempt
     *
     * @param array $user email data
     *
     * @since 0.1
     */
    public function onFailedLogin($user)
    {
        $user = unserialize($user);
        self::addActivityLog(4, trans('activity_messages.login_failed'), $user);
    }

    /**
     * Event that would be fired on change password
     *
     * @param array $user email data
     *
     * @since 0.1
     */
    public function onChangePassword($user)
    {
        $user = unserialize($user);
        self::addActivityLog(9, trans('activity_messages.change_password'), $user);
    }

    /**
     * Event that would be fired on a user login
     *
     * @param object $user Logged in user object
     *
     * @since 0.1
     */
    public function onResetPassword($user)
    {
        $user = unserialize($user);
        self::addActivityLog(1, trans('activity_messages.reset_password'), $user);
    }
    
     /**
     * Event that would be fired on a backend user active/deactive
     *
     * @param array $user_serialize_array Logged in user object
     *
     * @since 0.1
     */
    public function onBackendDeactivateUser($user_serialize_array)
    {
        $user_array = unserialize($user_serialize_array);
        self::addUserLog('deactivate_user', $user_array);
    }
    
    /**
     * Event that would be fired when a case is shared
     *
     * @param object $caseArray Case object on Share
     *
     * @since 0.1
     */
    public function onAssignCase($caseArray)
    {
        $arrData       = unserialize($caseArray);
        /**
         * Get Email Template from Database
         */
        $email_from = config('b2c_common.FRONTEND_FROM_EMAIL_NAME_EN');
        $email_content = EmailTemplate::getCaseShareEmailTemplate("Case Assign");
        $mail_body = str_replace( 
                [ '%to_name', '%to_lastname', '%to_email', '%from_name', '%from_lastname', '%case_id', '%case_fname',  '%comment' , '%role_name'],
                [ ucwords($arrData['to_name']), $arrData['to_lastname'], $arrData['to_email'], ucwords($arrData['from_name']), $arrData['from_lastname'], $arrData['app_id'], $arrData['lead_fname'], isset($arrData['comment']) ? $arrData['comment'] : null, $arrData['role_level'] ], $email_content->en_mail_body 
                );
        try {
           
            $to         = $arrData['to_email'];
            $subject = str_replace(
                    ['%app_id'], [$arrData['app_id']], $email_content->en_mail_subject);
            $cc         = $email_content->reciepient_cc;
            $bcc        = $email_content->reciepient_bcc;
            $attachment = null;
            Helpers::sendEmail($to, $subject, $mail_body, $attachment, $cc, $bcc,$email_from);
            
            
            /**
             * Call Activity Event
             */
            $activity = [];
            $activity['app_user_id'] = (int) $arrData['app_user_id'];
            $activity['app_id']      = (int) $arrData['app_id'];
            $activity['email']       = $arrData['to_email'];
            $activtyData = self::addActivityLog(40, trans('activity_messages.on_share_case'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
            if ($activtyData) {
                    self::addActivityEmail($activtyData, $sendEmailId);
            }
            
        } catch (Exception $ex) {
            if (method_exists($ex, 'getRequest')) {
                return response($ex->getRequest());
            }
        }

        /**
         * Call Activity Event
         */
        $activity = [];
        $activity['app_user_id'] = (int) $arrData['app_user_id'];
        $activity['app_id']      = (int) $arrData['app_id'];
        $activity['email']       = $arrData['to_email'];
        self::addActivityLog(40, $subject, $activity);
    }
 
    
     /**
     * Event that would be fired when a case is shared
     *
     * @param object $caseArray Case object on Share
     *
     * @since 0.1
     */
    public function autoAssignCase($caseArray)
    {
        $arrData       = unserialize($caseArray);
        /**
         * Get Email Template from Database
         */
        $email_from = config('b2c_common.FRONTEND_FROM_EMAIL_NAME_EN');
        $email_content = EmailTemplate::getCaseShareEmailTemplate("Case Auto Assign");
        $mail_body = str_replace( 
                [ '%case_id' , '%case_name' ,'%comment' ],
                [$arrData['app_id'], $arrData['customer_email'], isset($arrData['comment']) ? $arrData['comment'] : null ], $email_content->en_mail_body 
                );
        try {
            $to         = $arrData['to_email'];
            $subject = str_replace(
                    ['%app_id'], [$arrData['app_id']], $email_content->en_mail_subject);
            $cc         = $email_content->reciepient_cc;
            $bcc        = $email_content->reciepient_bcc;
            $attachment = null;
            Helpers::sendEmail($to, $subject, $mail_body, $attachment, $cc, $bcc,$email_from);
            
            
            /**
             * Call Activity Event
             */
            $activity = [];
            $activity['app_user_id'] = (int) $arrData['app_user_id'];
            $activity['app_id']      = (int) $arrData['app_id'];
            $activity['email']       = $arrData['to_email'];
            $activtyData = self::addActivityLog(40, trans('activity_messages.on_share_case'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
            if ($activtyData) {
                    self::addActivityEmail($activtyData, $sendEmailId);
            }
            
        } catch (Exception $ex) {
            if (method_exists($ex, 'getRequest')) {
                return response($ex->getRequest());
            }
        }

        /**
         * Call Activity Event
         */
        $activity = [];
        $activity['app_user_id'] = (int) $arrData['app_user_id'];
        $activity['app_id']      = (int) $arrData['app_id'];
        $activity['email']       = $arrData['to_email'];
        self::addActivityLog(40, $subject, $activity);
    }
    
    
    /**
     * Event that would be fired when a case is shared
     *
     * @param object $caseArray Case object on Share
     *
     * @since 0.1
     */
   /**
     * Event that would be fired when a case is shared
     *
     * @param object $caseArray Case object on Share
     *
     * @since 0.1
     */
    public function autoWithRoleAssignCase($caseArray)
    {
        $arrData       = unserialize($caseArray);
        
        /**
         * Get Email Template from Database
         */
        $email_from = config('b2c_common.FRONTEND_FROM_EMAIL_NAME_EN');
        if($arrData['is_case_assgin_to_rm'] == true) {
            $email_content = EmailTemplate::getCaseShareEmailTemplate("Case Auto Assign To RM Role");
        } else {
            $email_content = EmailTemplate::getCaseShareEmailTemplate("Case Auto Assign with Role");
        }
        $mail_body = str_replace( 
                [ '%from_name', '%from_lastname', '%case_id', '%case_fname', '%case_lname', '%comment' , '%role_name', '%line_of_credit'],
                [  ucwords($arrData['from_name']), $arrData['from_lastname'], $arrData['app_id'], $arrData['lead_fname'], $arrData['lead_lname'], isset($arrData['comment']) ? $arrData['comment'] : null, $arrData['role_level'] , trim($arrData['loan_amount']) ], $email_content->en_mail_body 
                );
        try {
           
            $to         = $arrData['to_email'];
            $subject = str_replace(
                    ['%app_id'], [$arrData['app_id']], $email_content->en_mail_subject);
            $cc         = $email_content->reciepient_cc;
            $bcc        = $email_content->reciepient_bcc;
            $attachment = null;           
            Helpers::sendEmail($to, $subject, $mail_body, $attachment, $cc, $bcc,$email_from);
            
            /**
             * Call Activity Event
             */
            $activity = [];
            $activity['app_user_id'] = (int) $arrData['app_user_id'];
            $activity['app_id']      = (int) $arrData['app_id'];
            $activity['email']       = $arrData['to_email'];
            $activtyData = self::addActivityLog(40, trans('activity_messages.on_share_case'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
            if ($activtyData) {
                    self::addActivityEmail($activtyData, $sendEmailId);
            }
            
        } catch (Exception $ex) {
            if (method_exists($ex, 'getRequest')) {
                return response($ex->getRequest());
            }
        }

        /**
         * Call Activity Event
         */
        $activity = [];
        $activity['app_user_id'] = (int) $arrData['app_user_id'];
        $activity['app_id']      = (int) $arrData['app_id'];
        $activity['email']       = $arrData['to_email'];
        self::addActivityLog(40, $subject, $activity);
    }
 
 
    /**
     * Event that would be fired when a backend user is added
     *
     * @param object $caseArray Case object on Share
     *
     * @since 0.1
     */
    public function addBackendUserCase($caseArray)
    {
        $arrData       = unserialize($caseArray);
        
        
        /**
         * Get Email Template from Database
         */
        $email_content = EmailTemplate::getCaseShareEmailTemplate("Email notification to backend user when user is added.");
        $mail_body = str_replace( 
                [ '%user','%email','%password'],
                [  ucwords($arrData['first_name']).' '.$arrData['last_name'],$arrData['email'],$arrData['raw_password']], $email_content->en_mail_body 
                );
        try {
           
            $to         = $arrData['email'];
            $subject =  $email_content->en_mail_subject;
            $cc         = $email_content->reciepient_cc;
            $bcc        = $email_content->reciepient_bcc;
            $attachment = null;           
            Helpers::sendEmail($to, $subject, $mail_body, $attachment, $cc, $bcc);
            
            /**
             * Call Activity Event
             */
           /* $activity = [];
            $activity['app_user_id'] = (int) $arrData['app_user_id'];
            $activity['app_id']      = (int) $arrData['app_id'];
            $activity['email']       = $arrData['email'];
            $activtyData = self::addActivityLog(40, trans('activity_messages.offer_accepeted_by_customer'), $activity);*/
           // $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
           /* if ($activtyData) {
                    self::addActivityEmail($activtyData, $sendEmailId);
            }*/
            
        } catch (Exception $ex) {
            if (method_exists($ex, 'getRequest')) {
                return response($ex->getRequest());
            }
        }

    }
 
     /**
     * Event that would be fired when a backend user is deactivated
     *
     * @param object $caseArray Case object on Share
     *
     * @since 0.1
     */
    public function deactivateBackendUserCase($caseArray)
    {
        $arrData       = unserialize($caseArray);
        
        /**
         * Get Email Template from Database
         */
        $email_content = EmailTemplate::getCaseShareEmailTemplate("Backend User Deactivate");
        $mail_body = str_replace( 
                [ '%user'],
                [  ucwords($arrData['first_name']).' '.$arrData['last_name']], $email_content->en_mail_body 
                );
       
        try {
           
            $to         = $arrData['email'];
            $subject =  $email_content->en_mail_subject;
            $cc         = $email_content->reciepient_cc;
            $bcc        = $email_content->reciepient_bcc;
            $attachment = null;           
            Helpers::sendEmail($to, $subject, $mail_body, $attachment, $cc, $bcc);
            
            /**
             * Call Activity Event
             */
           /* $activity = [];
            $activity['app_user_id'] = (int) $arrData['app_user_id'];
            $activity['app_id']      = (int) $arrData['app_id'];
            $activity['email']       = $arrData['email'];
            $activtyData = self::addActivityLog(40, trans('activity_messages.offer_accepeted_by_customer'), $activity);*/
           // $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
           /* if ($activtyData) {
                    self::addActivityEmail($activtyData, $sendEmailId);
            }*/
            
        } catch (Exception $ex) {
            if (method_exists($ex, 'getRequest')) {
                return response($ex->getRequest());
            }
        }

    }
    
    /**
     * Event that would be fired on a Reset Password(Backend)
     *
     * @param object $user User object on registration
     *
     * @since 0.1
     */
    public function onSendResetPassword($user)
    {
        $user          = unserialize($user);
        //$user['user_id'] = \Scramble::encrypt($user['user_id']);
        $user['user_id'] = \Crypt::encrypt($user['user_id']);
        $reset_link_a = "https://".config('event.event_frontend_url').'/password/reset/'.$user['token']."/". $user['user_id'];
        $reset_link = '<a href="'.$reset_link_a.'" target="_blank">'.trans('headings.click_here').'</a>';
        $activity = [];
        $activity['app_user_id'] = $user['user_id'];
        $activity['email'] = $user['email'];
        $expName = explode('@', $user['email']); 
        $name = (isset($user['first_name']) && !empty($user['first_name'])) ? $user['first_name'].' '.$user['last_name'] : $expName[0];

        $email_content = EmailTemplate::getEmailTemplate("Forgot Password");
        if ($email_content) {
            $mail_body = str_replace(
                ['%name', '%resetlink'],
                [ucwords($name),
                $reset_link],
                $email_content->en_mail_body
            );
            dd($mail_body);
            $subject =  $email_content->en_mail_subject;
            Mail::send('email', ['varContent' => $mail_body,  'to' => $user["email"]
            ], function ($message) use ($user, $subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($user['email'])->subject($subject);
            });
            
         //   self::addActivityLog(85, $subject, $activity);
            

//            $emailData['email_category_id'] = $email_content->email_cat_id;
//            $emailData['email_template'] = $email_content->id;
//            $emailData['template_type'] = $email_content->template_type;
//            $emailData['subject'] = $email_content->mail_subject;
//            $emailData['mail_content'] = $mail_body;
//            $emailData['receiver_id'] = $user["id"];
//            self::addSendEmailLog($emailData);

        }
    }
    
    /**
     * Event that would be fired on a Reset Password(Backend)
     *
     * @param object $user User object on registration
     *
     * @since 0.1
     */
    public function onSendResetPasswordBackend($user)
    {
        $user          = unserialize($user);
        //$user['user_id'] = \Scramble::encrypt($user['user_id']);
        $user['user_id'] = \Crypt::encrypt($user['user_id']);
        $reset_link_a = "https://".config('b2cin.backend_uri').'/backend/password/reset/'.$user['token'].'/'.$user['user_id'];
        $reset_link = '<a href="'.$reset_link_a.'" target="_blank">'.trans('headings.click_here').'</a>';
        $activity = [];
        $activity['app_user_id'] = $user['user_id'];
        $activity['email'] = $user['email'];
        $expName = explode('@', $user['email']); 
        $name = isset($user['first_name']) ? $user['first_name'].' '.$user['last_name'] : $expName[0];

        $email_content = EmailTemplate::getEmailTemplate("Forgot Password");
        if ($email_content) {
            $mail_body = str_replace(
                ['%name', '%resetlink'],
                [ucwords($name),
                $reset_link],
                $email_content->en_mail_body
            );
            $subject =  $email_content->en_mail_subject;
            Mail::send('email', ['varContent' => $mail_body,  'to' => $user["email"]
            ], function ($message) use ($user, $subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($user['email'])->subject($subject);
            });
            
         //   self::addActivityLog(85, $subject, $activity);
            

//            $emailData['email_category_id'] = $email_content->email_cat_id;
//            $emailData['email_template'] = $email_content->id;
//            $emailData['template_type'] = $email_content->template_type;
//            $emailData['subject'] = $email_content->mail_subject;
//            $emailData['mail_content'] = $mail_body;
//            $emailData['receiver_id'] = $user["id"];
//            self::addSendEmailLog($emailData);

        }
    }
    
    
    
     /**
     * Event that would be fired when a master record is approved by admin or manager
     *
     * @param object $arrData Case object on Share
     *
     * @since 0.1
     */
    public function onMasterAcceptRejectEmail($arrData)
    {
        $arrData       = unserialize($arrData);
        /** Get Email Template from Database **/
        $email_content = EmailTemplate::getCaseShareEmailTemplate("Email notification when master record is accepted or rejected");
        $mail_body = str_replace( 
                [ '%name','%record_data','%record_status','%from_customer'],
                [  ucwords($arrData['to_username']),$arrData['record_data'],$arrData['record_status'],$arrData['from_username']], $email_content->en_mail_body 
                );
        try {
            $to         = $arrData['to_email'];
            $subject    = $arrData['subject'];
            $cc         = $email_content->reciepient_cc;
            $bcc        = $email_content->reciepient_bcc;
            $attachment = null;           
            Helpers::sendEmail($to, $subject, $mail_body, $attachment, $cc, $bcc);
            
        } catch (Exception $ex) {
            if (method_exists($ex, 'getRequest')) {
                return response($ex->getRequest());
            }
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
            'user.newregistered',
            'App\Repositories\Events\UserEventsListener@onNewRegistered'
        );
        $events->listen(
            'user.registered',
            'App\Repositories\Events\UserEventsListener@onRegistered'
        );

        $events->listen(
            'user.registration.failed',
            'App\Repositories\Events\UserEventsListener@onFailedRegistration'
        );

        $events->listen(
            'user.login.success',
            'App\Repositories\Events\UserEventsListener@onLoginSuccess'
        );

        $events->listen(
            'user.logout.success',
            'App\Repositories\Events\UserEventsListener@onLogoutSuccess'
        );

        $events->listen(
            'user.login.failed',
            'App\Repositories\Events\UserEventsListener@onFailedLogin'
        );

        $events->listen(
            'user.changepassword',
            'App\Repositories\Events\UserEventsListener@onChangePassword'
        );

        $events->listen(
            'user.resetpassword',
            'App\Repositories\Events\UserEventsListener@onResetPassword'
        );
        
        $events->listen(
            'user.backenddeactivateuser',
            'App\Repositories\Events\UserEventsListener@onBackendDeactivateUser'
        );
        
        $events->listen(
            'case.assigned',
            'App\Repositories\Events\UserEventsListener@onAssignCase'
        );
        
        $events->listen(
            'case.autoassigned',
            'App\Repositories\Events\UserEventsListener@autoAssignCase'
        );
         $events->listen(
            'case.autoassignedwithrole',
            'App\Repositories\Events\UserEventsListener@autoWithRoleAssignCase'
        );
         $events->listen(
            'case.addbackenduser',
            'App\Repositories\Events\UserEventsListener@addBackendUserCase'
        );
        $events->listen(
            'case.deactivatebackenduser',
            'App\Repositories\Events\UserEventsListener@deactivateBackendUserCase'
        );
        $events->listen(
            'user.passwordrequested',
            'App\Repositories\Events\UserEventsListener@onSendResetPassword'
        );
        $events->listen(
            'user.passwordrequestedbackend',
            'App\Repositories\Events\UserEventsListener@onSendResetPasswordBackend'
        );
        $events->listen(
            'master.acceptreject.email',
            'App\Repositories\Events\UserEventsListener@onMasterAcceptRejectEmail'
        );
       }
}
