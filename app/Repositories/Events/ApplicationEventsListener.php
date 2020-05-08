<?php

namespace App\Repositories\Events;

use Auth;
use Mail;
use App;
use Helpers;
use Exception;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Models\User;
use App\Repositories\Factory\Events\BaseEvent;
use App\Repositories\Models\Master\EmailTemplate;
use App\Repositories\Models\Master\SmsTemplate;
use App\Repositories\Contracts\ApplicationInterface;
use App\Repositories\Models\Appointment;
use App\Repositories\Models\ConsentSMS;
use App\Repositories\Models\ActivityLog;

class ApplicationEventsListener extends BaseEvent
{

    use SerializesModels;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ApplicationInterface $application)
    {
    $this->application = $application;        

    }
    
     /**
     * Event that would be fired on application start
     *
     * @param object $application application data
     *
     * @since 0.1
     *
     * @author Rajeev Sharma
     */
    public function onStartApplication($application)
    {
        $application = unserialize($application);
        $activity_type = isset($application['activity_type']) ? $application['activity_type'] : 66;
        self::addActivityLog($activity_type, $application['message'], $application);
    }

    /**
     * Email On share lead
     *
     * @param object $application application data
     */
    public function onShareCase($application)
    {
        $application = unserialize($application);

        $activity = [];
        $activity ['user_id'] = $application['lead_id'];
        $activity ['app_id'] = $application['app_id'];

        //Send mail to Case Manager
        $email_content = EmailTemplate::getEmailTemplate("Leadsheet shared notification to banker");
        if ($email_content) {
            $mail_body = str_replace(
                ['%name', '%leadname', '%leadid', '%appid', '%contactemail', '%contactphone', '%sharedate', '%backendurl'],
                [ucwords($application['cm_name']), ucwords($application['lead_name']), $application['lead_id'], $application['app_id'],
                $application['contact_email'], $application['contact_phone'], Helpers::getCurrentDateTime(), link_to($application['rlink'], Helpers::getBackendLoginPath())],
                $email_content->mail_body
            );

            Mail::send('email', ['varContent' => $mail_body, 'to' => $application["email"]
                    ], function ($message) use ($application, $email_content) {
                        $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                        $message->to($application["email"], $application["cm_name"])->subject($email_content->mail_subject);
                    });
        }
        $activtyData = self::addActivityLog(36, trans('activity_messages.case_shared'), $activity);
        $sendEmailId = self::addSendEmailLog($mail_body, $email_content->mail_subject, $email_content, $activity);
        if($activtyData){
            self::addActivityEmail($activtyData, $sendEmailId);
        }
    }
    /**
     * Email On share lead
     *
     * @param object $application application data
     */
    public function onShareApproval($application)
    {

        $application = unserialize($application);
        $mail_body = "";
        $mail_subject = "";
        $activity = [];
        //Send mail to Case Manager
        $main_guarantor = false;
        $email_content = EmailTemplate::getEmailTemplate("Communication to Owners, Guarantors, Borrowers");
        $name = $application['owner_detail']['first_name'].' '.$application['owner_detail']['last_name'];
        if ($email_content)
        {
            $main_guarantor = $this->application->getMainGurantorByAppid((int) $application['app_id']);
            $email_body = $email_content->en_mail_body;
            $email_subject = $email_content->en_mail_subject;
            $mail_subject = str_replace('%Applicant Name',ucwords($main_guarantor['first_name']),$email_subject);
            $mail_body = str_replace(
                ['%Owner/Borrower/Guarantor Name', '%Applicant Name', '%urllink', '%tollnumber', '%privacylink'],
                [ucwords($name), $main_guarantor['first_name'].' '.$main_guarantor['last_name'], $application['access_link'], config('b2c_common.TOLL_FREE_NUMBER2'), config('b2c_common.HSBC_PRIVACY')],
                $email_body
            );
            Mail::send('email', ['varContent' => $mail_body, 'to' => $application["email"]
                ], function ($message) use ($application, $mail_subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($application["email"],$application['owner_detail']['first_name'])->subject($mail_subject);
            });

            $activity['user_name'] = '';            
            $activity['app_user_id'] = $application['user_id'];
            $activity['app_id'] = $application['app_id'];
            $activity['is_display_in_activity'] = $application['is_display_in_activity'];
            $activtyData = self::addActivityLog(79, trans('activity_messages.email_guarantor_information_sent', ['guarantors' => $application["email"]]), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $mail_subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        } else {            
            $activity['user_name'] = '';
            $activity['app_user_id'] = $application['user_id'];
            $activity['app_id'] = $application['app_id'];
            $activity['is_display_in_activity'] = $application['is_display_in_activity'];
            $activtyData = self::addActivityLog(79, trans('activity_messages.email_guarantor_information_sent', ['guarantors' => $application["email"]]), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $mail_subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        }
    }

    
    
/**
     * Email On share lead
     *
     * @param object $application application data
     */
    public function onBackendShareApproval($application)
    {

        $application = unserialize($application);
        $mail_body = "";
        $mail_subject = "";
        $activity = [];
        //Send mail to primary user for business information
        $email_content = EmailTemplate::getEmailTemplate("Request for your information and consent");
        if ($email_content)
        {
            $email_body = $email_content->en_mail_body;
            $email_subject = $email_content->en_mail_subject;
            $mail_subject = str_replace('%Applicant Name',ucwords($application['owner_detail']['first_name']),$email_subject);
            
            $fr_email_body = $email_content->fr_mail_body;
                                   
            if(isset($application['link_type']) && $application['link_type'] == config('b2c_common.LINKTYPE.BIZLINK')) {
                $to_email = $application['owner_detail']['email'];
                $name = isset($application['owner_detail']['first_name']) ? $application['owner_detail']['first_name'].' '.$application['owner_detail']['last_name'] : null;
            } else {
                $to_email = $application['owner_detail']['cust_email'];
                $name = $application['owner_detail']['first_name'].' '.$application['owner_detail']['last_name'];
            }
            $mail_body = str_replace(
                ['%name', '%Applicant Name', '%urllink', '%tollnumber', '%privacylink'],
                [ucwords($name), $application['owner_detail']['first_name'].' '.$application['owner_detail']['last_name'], $application['access_link'].'&ln=en', config('b2c_common.TOLL_FREE_NUMBER2'), config('b2c_common.HSBC_PRIVACY')],
                $email_body
            );
            $fr_mail_body = str_replace(
                ['%name', '%Applicant Name', '%urllink', '%tollnumber', '%privacylink'],
                [ucwords($name), $application['owner_detail']['first_name'].' '.$application['owner_detail']['last_name'], $application['access_link'].'&ln=fr', config('b2c_common.TOLL_FREE_NUMBER2'), config('b2c_common.HSBC_PRIVACY')],
                $fr_email_body
            );
            Mail::send('email', ['varContent' => $mail_body,'to' => $to_email,'mail_title'=>'Request for your information and consent','fr_mail_body'=>$fr_mail_body,
                ], function ($message) use ($application, $mail_subject, $to_email) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($to_email,$application['owner_detail']['first_name'])->subject("Request for your business financial information/ Demande de renseignements financiers sur votre entreprise");
            });
            $activity['user_name'] = '';            
            $activity['app_user_id'] = $application['user_id'];
            $activity['app_id'] = $application['app_id'];
            $activity['is_display_in_activity'] = $application['is_display_in_activity'];
            $activity['email'] = $application['owner_detail']['cust_email'];
            $activtyData = self::addActivityLog(79, trans('activity_messages.email_business_info'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $mail_subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        } else {            
            $activity['user_name'] = '';
            $activity['app_user_id'] = $application['user_id'];
            $activity['app_id'] = $application['app_id'];
            $activity['is_display_in_activity'] = $application['is_display_in_activity'];
            $activity['email'] = $application['owner_detail']['cust_email'];
            $activtyData = self::addActivityLog(79, trans('activity_messages.email_business_info'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $mail_subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        }
    }
    
     /**
     * Email On share lead
     *
     * @param object $application application data
     */
    public function onElectronicDocApproval($application)
    {
        $application = unserialize($application);
        $mail_body = "";
        $mail_subject = "";
        $activity = [];
        //Send mail to primary user for business information
        $email_content = EmailTemplate::getEmailTemplate("Request for your business financial information");
        $name = $application['owner_detail']['first_name'].' '.$application['owner_detail']['last_name'];
        if ($email_content)
        {
            $email_body = $email_content->en_mail_body;
            $email_subject = $email_content->en_mail_subject;
            $fr_email_body = $email_content->fr_mail_body;
            $mail_subject = str_replace('%Applicant Name',ucwords($application['owner_detail']['first_name']),$email_subject);
            $mail_body = str_replace(
                ['%name', '%urllink', '%tollnumber', '%privacylink'],
                [ucwords($name), $application['access_link'].'&ln=en', config('b2c_common.TOLL_FREE_NUMBER2'), config('b2c_common.HSBC_PRIVACY')],
                $email_body
            );
            $fr_mail_body = str_replace(
                ['%name', '%urllink', '%tollnumber', '%privacylink'],
                [ucwords($name), $application['access_link'].'&ln=fr', config('b2c_common.TOLL_FREE_NUMBER2'), config('b2c_common.HSBC_PRIVACY')],
                $fr_email_body
            );
            Mail::send('email', ['varContent' => $mail_body,'to' => $application['owner_detail']['cust_email'],'mail_title'=>'Email to primary owner to provide consent for electronic collection','fr_mail_body'=>$fr_mail_body 
                ], function ($message) use ($application, $mail_subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($application['owner_detail']['cust_email'],$application['owner_detail']['first_name'])->subject("Request for your business financial information/ Demande de renseignements financiers sur votre entreprise");
            });
            $activity['user_name'] = '';            
            $activity['app_user_id'] = $application['user_id'];
            $activity['app_id'] = $application['app_id'];
            $activity['is_display_in_activity'] = $application['is_display_in_activity'];
            $activity['email'] = $application['owner_detail']['cust_email'];
            $activtyData = self::addActivityLog(79, trans('activity_messages.email_business_info'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $mail_subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        } else {            
            $activity['user_name'] = '';
            $activity['app_user_id'] = $application['user_id'];
            $activity['app_id'] = $application['app_id'];
            $activity['is_display_in_activity'] = $application['is_display_in_activity'];
            $activity['email'] = $application['owner_detail']['cust_email'];
            $activtyData = self::addActivityLog(79, trans('activity_messages.email_business_info'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $mail_subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        }
    }
    

    /**
     * Audit Application
     * @param type $application
     */
    public function onApplicationAudit($application)
    {
        $application = unserialize($application);
        self::addApplicationAuditLog($application);
    }

    /**
     * Event Fire whenever Application will Edit
     *
     * @param Array $application
     */
    public function onApplicationEdit($application)
    {
        $application = unserialize($application);
        self::addActivityLog(10, trans('activity_messages.application_edit'), $application);
    }

    /**
     * Event Fire whenever Application will view
     *
     * @param Array $application
     */
    public function onApplicationView($application)
    {
        $application = unserialize($application);
        self::addActivityLog(12, trans('activity_messages.application_view'), $application);
    }
    /**
     * Event Fire whenever Lead will view
     *
     * @param Array $application
     */
    public function onLeadView($lead)
    {
        $lead = unserialize($lead);
        self::addActivityLog(12, trans('activity_messages.lead_view'), $lead);
    }

    /**
     * Event view notes
     *
     * @param type $notesInfo
     */
    public function onNotesAndActivityView($notesInfo)
    {
        $notesInfo = unserialize($notesInfo);
        self::addActivityLog(17, trans('activity_messages.note_view'), $notesInfo);
    }

    /**
     * Event fire Note Save
     *
     * @param type $notesInfo
     */
    public function onNoteSave($notesInfo)
    {   
        $notesInfo = unserialize($notesInfo);
        self::addActivityLog(16, trans('activity_messages.add_case_note'), $notesInfo);
    }

     /**
     * Event fire on Mail Send to user
     *
     * @param string $mailInfo
     */
    public function onMailSend($mailInfo)
    {
        $mailInfo = unserialize($mailInfo);

        $activity = [];
        $activity['user_id'] = $mailInfo['receiver_id'];
        $activity['app_id'] = isset($mailInfo['app_id']) ? $mailInfo['app_id'] : null;
        $activity['is_display_in_activity'] = config('b2c_common.IS_DISPLAY_ACTIVITY');

        $activtyData = self::addActivityLog(18, trans('activity_messages.email_sent_to_customer'), $activity);
        $sendEmailId = self::addSendEmailLog($mailInfo['mail_content'], $mailInfo['subject'], NULL, $activity);
        if($activtyData){
            self::addActivityEmail($activtyData, $sendEmailId);
        }
    }

    /**
     * Event fire on modify case status
     *
     * @param type $statusInfo
     */
    public function onModifyStatus($statusInfo)
    {
        $statusInfo = unserialize($statusInfo);

        if (isset($statusInfo['status_id'])) {
            if ($statusInfo['status_id'] == config('b2c_common.APPROVED')) {
                $email_content = EmailTemplate::getEmailTemplate('Loan Decisions - Approval, Decline, Counteroffer');
            } elseif ($statusInfo['status_id'] == config('b2c_common.COUNTEROFFER')) {
                $email_content = EmailTemplate::getEmailTemplate('Decision Counteroffer');
            } elseif ($statusInfo['status_id'] == config('b2c_common.DECLINED')) {
                $email_content = EmailTemplate::getEmailTemplate('Decision Denied');
            }

            if(!empty($email_content)){

                $userDetail = Appointment::select('appt.appt_phone','users.prefered_comm_mode','users.is_sms_notification')
                    ->leftjoin('users','users.id','=','appt.user_id')->where('appt.user_id',(int)$statusInfo['user_id'])->first();

                if($userDetail['is_sms_notification'] == '1'){
                    if($userDetail['prefered_comm_mode']==config('b2c_common.PREFERED_COMM_SPA')){
                        $msgBody = $email_content->es_sms_body;
                    }else{
                        $msgBody = $email_content->en_sms_body;
                    }
                    $smsPhoneNo = (!empty($this->default_receiver_phoneno)?$this->default_receiver_phoneno:$userDetail['appt_phone']);
                    try{
                        if(!empty($smsPhoneNo) && !empty($msgBody)){
                            $smsPhoneNo = $this->phoneno_prefix.$smsPhoneNo;
                            //Twilio::message($smsPhoneNo, $msgBody);
                            Helpers::sendTextMessage($smsPhoneNo, $msgBody);
                            self::addActivityLog(72, trans('activity_messages.sms_on_status_update'), $statusInfo);
                        }
                    }catch(Exception $ex){
                        self::addSMSLog($email_content->id,$smsPhoneNo,$ex->getCode() . '--' . $ex->getMessage(),1);
                    }
                }
            }
        }


        self::addActivityLog(15, trans('activity_messages.status_modified_to').' '.$statusInfo['status_name'], $statusInfo);
    }


    /**
     * Email On create of access link for guarantor /owner
     *
     * @param object $application application data
     */
    public function onCreateEmailAccessGuarantorForm($application)
    {
        $application = unserialize($application);
        $mail_body = "";
        $mail_subject = "";
        $activity = [];
        //Send mail to Case Manager
        $email_content = EmailTemplate::getEmailTemplate("Communication to Owners, Guarantors, Borrowers");
        if ($email_content)
        {
            if (isset($application['selected_language']) && $application['selected_language'] == 'es') {
                $email_body = $email_content->es_mail_body;
                $email_subject = $email_content->es_mail_subject;
            } else {
                $email_body = $email_content->en_mail_body;
                $email_subject = $email_content->en_mail_subject;
            }
            $mail_subject = str_replace('%Applicant Name',ucwords($application['user_first_name']),$email_subject);
            $mail_body = str_replace(
                ['%Owner/Borrower/Guarantor Name', '%AppID', '%Applicant Name', '%urllink', '%number', '%Agentname', '%message'],
                [ucwords(e($application['first_name'])), $application['app_no'] , $application['user_first_name'], $application['access_link'], '787-620-7963', 'Agent Name', $application['message']],
                $email_body
            );
            Mail::send('email', ['varContent' => $mail_body, 'to' => $application["email"]
                ], function ($message) use ($application, $mail_subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($application["email"], $application["first_name"])->subject($mail_subject);
            });

            $activity['user_id'] = $application['user_id'];
            $activity['app_id'] = $application['app_id'];
            $activity['is_display_in_activity'] = $application['is_display_in_activity'];
            $activtyData = self::addActivityLog(79, trans('activity_messages.email_guarantor_information_sent', ['guarantors' => $application["email"]]), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $mail_subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        } else {
            $activity['user_id'] = $application['user_id'];
            $activity['app_id'] = $application['app_id'];
            $activity['is_display_in_activity'] = $application['is_display_in_activity'];
            $activtyData = self::addActivityLog(79, trans('activity_messages.email_guarantor_information_sent', ['guarantors' => $application["email"]]), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $mail_subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        }

    }


    /**
     * Email On create of access link for guarantor /owner
     *
     * @param object $current_data application data
     */
      public function onCompleteAccessGuarantorForm($current_data)
    {
        $details = unserialize($current_data);
        $activity = [];
        $activity['app_user_id'] = $details['user_id'];
        $activity['app_id'] = $details['app_id'];
        $activity['is_display_in_activity'] = $details['is_display_in_activity'];
        $main_guarantor = $this->application->getMainGurantorByAppid((int) $details['app_id']);
        //$email_content = EmailTemplate::getEmailTemplate("Link Share To Email Guarantor");
        //Send mail to Case Manager
        if(isset($details['action']) && $details['action'] == config('b2c_common.DECLINE_ACTION')) {
            $email_content = EmailTemplate::getEmailTemplate("Declined Link Share To Email Guarantor");
        } else {
            $email_content = EmailTemplate::getEmailTemplate("Link Share To Email Guarantor");
        }
        
        if ($email_content)
        {
            $email_body = $email_content->en_mail_body;
            $email_subject = $email_content->en_mail_subject;
            
            $mail_body = str_replace(
                ['%name', '%tollnumber', '%Ownername', '%siteUrl'],
                [ucwords($details['additional_owner']), config('b2c_common.TOLL_FREE_NUMBER2'), $main_guarantor['first_name'].' '.$main_guarantor['last_name'], Helpers::getLoginPath($details['app_locale'])],
                $email_body
            );

            Mail::send('email', ['varContent' => $mail_body, 'to' => $details["email"]
            ], function ($message) use ($details, $email_subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($details["email"], $details["first_name"])->subject($email_subject);
            });
            
            self::addActivityLog(78, trans('activity_messages.email_guarantor_information_saved', ['guarantors' => $details['guarantor_email']]), $activity);
        } else {
            self::addActivityLog(78, trans('activity_messages.email_guarantor_information_saved', ['guarantors' => $details['guarantor_email']]), $activity);
        }

    }
    

    /**
     * Track SSN view logs
     *
     * @param object $application application data
     */
    public function onTrackSsn($application)
    {
        $application = unserialize($application);
        self::addActivityLog(61, trans('activity_messages.show_ssn'), $application);
    }

    /**
     * Event that would be fired on profile update
     *
     * @param object $userdata
     *
     * @since 0.1
     *
     */
    public function onProfileUpdate($profile)
    {
        $profile = unserialize($profile);

        //Send mail to user
        $email_content = EmailTemplate::getEmailTemplate("Profile Update Mail");
        if ($email_content)
        {
            if (isset($profile['selected_language']) && $profile['selected_language'] == 'es') {
                $email_body = $email_content->es_mail_body;
                $email_subject = $email_content->es_mail_subject;
            } else {
                $email_body = $email_content->en_mail_body;
                $email_subject = $email_content->en_mail_subject;
            }

            $mail_body = str_replace(
                ['%name'],
                [ucwords($profile['first_name']) . ' ' . ucwords($profile['last_name'])],
                $email_body
            );

            Mail::send('email', ['varContent' => $mail_body, 'to' => $profile["email"]
                ],
                function ($message) use ($profile, $email_subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($profile["email"], ucfirst($profile["first_name"]) . ' ' . ucfirst($profile['last_name']))
                    ->subject($email_subject);
            });
        }



    }


    public function onPasswordUpdate($details)
    {
        $details = unserialize($details);
        $email_content = EmailTemplate::getEmailTemplate("Change of Password");

        if ($email_content)
        {
            if (isset($details['selected_language']) && $details['selected_language'] == 'es') {
                $email_body = $email_content->es_mail_body;
                $email_subject = $email_content->es_mail_subject;
            } else {
                $email_body = $email_content->en_mail_body;
                $email_subject = $email_content->en_mail_subject;
            }

            $mail_body = str_replace(
                ['%name'],
                [ucwords($details['name'])],
                $email_body
            );

            Mail::send('email', ['varContent' => $mail_body, 'to' => $details["email"]
                ],
                function ($message) use ($details, $email_subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($details["email"], ucfirst($details["name"]))
                    ->subject($email_subject);
            });
        }
    }


     /**
     * Event send email shared application
     *
     * @param array $data
     *
     * @since 0.1
     *
     */
    public function onEmailShareApplication($data)
    {
        $data = unserialize($data);
        $activity = [];
        $activity ['user_id'] = $data['user_id'];
        $activity ['app_id'] = $data['app_id'];
        $activity ['is_display_in_activity'] = config('b2c_common.IS_DISPLAY_ACTIVITY');
        $mail_body = "";
        $email_subject = "";
        $email_content = EmailTemplate::getEmailTemplate("Application shared with banker successfully");
        if($email_content)
        {
            if (isset($data['selected_language']) && $data['selected_language'] == config('b2c_common.PREFERED_COMM_SPA')) {
                $email_body = $email_content->es_mail_body;
                $email_subject = $email_content->es_mail_subject;
                $msgBody = $email_content->es_sms_body;
            } else {
                $email_body = $email_content->en_mail_body;
                $email_subject = $email_content->en_mail_subject;
                $msgBody = $email_content->en_sms_body;
            }


            $mail_body = str_replace(
                ['%name','%phone'],
                [ucwords($data['first_name'])." ".ucwords($data['last_name']),'787-620-7963'],
                $email_body
            );

            Mail::send('email', ['varContent' => $mail_body, 'to' => $data["email"]
                ],
                function ($message) use ($data, $email_subject) {
                  $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'),
                                 config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                  $message->to($data["email"], $data["first_name"])
                            ->subject($email_subject);
            });
            //sms sending start here
            if($data['is_sms_notification'] == '1'){
                $smsPhoneNo = (!empty($this->default_receiver_phoneno)?$this->default_receiver_phoneno:$data['appt_phone']);
                try{
                    if(!empty($smsPhoneNo) && !empty($msgBody)){
                        $smsPhoneNo = $this->phoneno_prefix.$smsPhoneNo;
//                        Twilio::message($smsPhoneNo, $msgBody);
                        Helpers::sendTextMessage($smsPhoneNo, $msgBody);
                        $smsactivity = [];
                        $smsactivity ['user_id'] = $data['user_id'];
                        $smsactivity ['app_id'] = $data['app_id'];
                        $smsactivity ['is_display_in_activity'] = config('b2c_common.IS_DISPLAY_ACTIVITY');
                        self::addActivityLog(72, trans('activity_messages.sms_on_app_shared'), $smsactivity);
                    }
                }catch(Exception $ex){
                    self::addSMSLog($email_content->id,$smsPhoneNo,$ex->getCode() . '--' . $ex->getMessage(),0);
                }
            }
            //sms sending end here

            $activtyData = self::addActivityLog(68, trans('activity_messages.application_shared_by_customer'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $email_subject, $email_content, $data);
            if($activtyData){
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        }
        else{
            $activtyData = self::addActivityLog(68, trans('activity_messages.application_shared_by_customer'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $email_subject, $email_content, $data);
            if($activtyData){
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        }
    }


     /**
     * Event save appointment
     *
     * @param type $appAppointIfno
     */
    public function onAppAppointmentActivity($appAppointIfno)
    {
        $appAppointIfno = unserialize($appAppointIfno);
        $activity = [];
        $activity['user_id'] = $appAppointIfno['user_id'];
        $activity['app_id'] = $appAppointIfno['app_id'];
        $activity['is_display_in_activity'] = $appAppointIfno['is_display_in_activity'];
        self::addActivityLog(69, trans('activity_messages.app_appointment_scheduled'), $activity);
    }
     /**
     * Event update appointment
     *
     * @param type $appUpAppointIfno
     */
    public function onAppointmentActivityUpdate($appUpAppointIfno)
    {
        $appUpAppointIfno = unserialize($appUpAppointIfno);
        self::addActivityLog(69, trans('activity_messages.appointment_update'), $appUpAppointIfno);
    }

     /**
     * Event update appointment
     *
     * @param type $appInfo
     */
    public function onApplicationIncomplete($appInfo)
    {
        $appInfo = unserialize($appInfo);
        $activityData = self::addActivityLog(76, trans('activity_messages.application_incomplete_notice', ['duration' => $appInfo['duration']]), $appInfo);
        $sendEmailId = self::addSendEmailLog($appInfo['email_body'], $appInfo['email_subject'], $appInfo['email_content'], $appInfo);
        if ($activityData) {
            self::addActivityEmail($activityData, $sendEmailId);
        }
    }

    /**
     * Event update appointment status
     *
     * @param type $appUpAppointIfno
     */
    public function onAppApptActivityStatus($appUpAppointStatusIfno)
    {
        $appUpAppointStatusIfno = unserialize($appUpAppointStatusIfno);
        self::addActivityLog(69, trans('activity_messages.appointment_status'), $appUpAppointStatusIfno);
    }
    /**
     * Event for add document activity log
     *
     * @param type $appUploadedDoc
     */
    public function onAppDocuments($appUploadedDoc)
    {
        $appUploadedDoc = unserialize($appUploadedDoc);
        $activity = [];
        $activity ['app_user_id'] = $appUploadedDoc['app_user_id'];
        $activity ['app_id'] = $appUploadedDoc['app_id'];
        //Send mail to user
        $email_content = EmailTemplate::getEmailTemplate("Mail to RM, CO that customer has uploaded missing documents");
        $activity_id = ($appUploadedDoc["email"] != "") ? 83 : 70;
        $mail_body = "";
        $email_subject = "";
        if ($email_content && ($appUploadedDoc["email"] != '')) {
            $email_body = $email_content->en_mail_body;
            $email_subject = $email_content->en_mail_subject;

            $mail_body = str_replace(
                ['%name', '%applicationId', '%siteurl'], [ucwords($appUploadedDoc['first_name']) . " " . ucwords($appUploadedDoc['last_name']), $appUploadedDoc['app_no'], link_to(Helpers::getBackendLoginPath(), Helpers::getBackendLoginPath())], $email_body
            );

            Mail::send('email', ['varContent' => $mail_body, 'to' => $appUploadedDoc["email"]
                ], function ($message) use ($appUploadedDoc, $email_subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME_EN'));
                $message->to($appUploadedDoc["email"], ucfirst($appUploadedDoc["first_name"]) . ' ' . ucfirst($appUploadedDoc['last_name']))
                    ->subject($email_subject);
            });
            $activtyData = self::addActivityLog($activity_id, $appUploadedDoc['MasteDocName'], $appUploadedDoc);
            $sendEmailId = self::addSendEmailLog($mail_body, $email_subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        } else {
            $activtyData = self::addActivityLog($activity_id, $appUploadedDoc['MasteDocName'], $appUploadedDoc);
            $sendEmailId = self::addSendEmailLog($mail_body, $email_subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        }

    }

    /**
     * Send SMS to Owner on hardpull report
     *
     * @param type $ownerData
     */
    public function onHardPullSMSToOwner($userData)
    {
        $userData = unserialize($userData);
        $email_content = EmailTemplate::getEmailTemplate('Mail to customer that application is complete and loan decision is in progress (based on Hardpull)');
        if (!empty($email_content)) {
            $userDetail = Appointment::select('appt.appt_phone', 'users.prefered_comm_mode', 'users.is_sms_notification')
                    ->leftjoin('users', 'users.id', '=', 'appt.user_id')->where('appt.user_id', (int) $userData['user_id'])->first();
            if ($userDetail['is_sms_notification'] == '1') {
                if ($userDetail['prefered_comm_mode'] == config('b2c_common.PREFERED_COMM_SPA')) {
                    $msgBody = $email_content->es_sms_body;
                } else {
                    $msgBody = $email_content->en_sms_body;
                }

                $smsPhoneNo = (!empty($this->default_receiver_phoneno) ? $this->default_receiver_phoneno : $userDetail['appt_phone']);
                try {
                    $isSend = ActivityLog::chkTuHardPullSmsSendById($userData['app_id']);
                    if (!empty($smsPhoneNo) && !empty($msgBody) && empty($isSend) && $userData['app_type']!=3) {
                        $smsPhoneNo = $this->phoneno_prefix . $smsPhoneNo;
                        Helpers::sendTextMessage($smsPhoneNo, $msgBody);
                        self::addActivityLog(72, trans('activity_messages.sms_on_hard_pull'), $userData);
                    } 
                } catch (Exception $ex) {
                    self::addSMSLog($email_content->id, $smsPhoneNo, $ex->getCode() . '--' . $ex->getMessage(), 1);
                }
            }
        }
    }

    /**
     * Send Email to all assigned parties when note added wrt application
     *
     * @param type $userData
     */
    public function onAddNoteEmail($userData){
        $assignedUserData = unserialize($userData);
        $activity = [];
        $activity ['user_id'] = $assignedUserData['user_id'];
        $activity ['app_id'] = $assignedUserData['app_id'];
        //Send mail to all assigned parties
        $email_content = EmailTemplate::getEmailTemplate("Email to all assigned parties when note is added on an application.");
        $mail_body = "";
        $email_subject = "";
        if ($email_content) {
            $email_body = $email_content->en_mail_body;
            $email_subject = $email_content->en_mail_subject;
            $mail_body = str_replace(
                ['%name', '%applicationId', '%siteurl'], [ucwords($assignedUserData['first_name']), $assignedUserData['app_no'], link_to(Helpers::getBackendLoginPath(), Helpers::getBackendLoginPath())], $email_body
            );
            Mail::send('email', ['varContent' => $mail_body, 'to' => $assignedUserData["email_id"]
                ], function ($message) use ($assignedUserData, $email_subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($assignedUserData['email_id'], $assignedUserData["first_name"])->subject($email_subject);
            });
            $activtyData = self::addActivityLog(73, trans('activity_messages.email_assigned_parties'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $email_subject, $email_content, $activity);
            if($activtyData){
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        } else {
            $activtyData = self::addActivityLog(73, trans('activity_messages.email_assigned_parties'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $email_subject, $email_content, $activity);
            if($activtyData){
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        }
    }
     /*
     * Event fire on modify case status
     *
     * @param type $statusInfo
     */
    public function onSendAppStatusUpdate($statusInfo)
    {
        $data = unserialize($statusInfo);
        $activity = [];
        $activity ['user_id'] = $data['user_id'];
        $activity ['app_id'] = $data['app_id'];
        $mail_body = "";
        $email_subject = "";
        $email_content = false;
        if($data['is_rm'] == config('b2c_common.YES')){
            if($data['status_id'] == config('b2c_common.APPROVED')){
            $email_content = EmailTemplate::getEmailTemplate('Application Status RM - Approved');
            }elseif($data['status_id'] == config('b2c_common.COUNTEROFFER')){
                $email_content = EmailTemplate::getEmailTemplate('Application Status RM - Decline, Counter Offer');
            }elseif($data['status_id'] == config('b2c_common.DECLINED')){
                $email_content = EmailTemplate::getEmailTemplate('Application Status RM - Decline, Counter Offer');
            }
        } else {
            if($data['status_id'] == config('b2c_common.APPROVED')){
            $email_content = EmailTemplate::getEmailTemplate('Application Status - Approved');
            }elseif($data['status_id'] == config('b2c_common.COUNTEROFFER')){
                $email_content = EmailTemplate::getEmailTemplate('Application Status - Declice, Counter Offer');
            }elseif($data['status_id'] == config('b2c_common.DECLINED')){
                $email_content = EmailTemplate::getEmailTemplate('Application Status - Declice, Counter Offer');
            }
        }


        if (!empty($email_content)) {
            if (isset($data['selected_language']) && $data['selected_language'] == config('b2c_common.PREFERED_COMM_SPA')) {
                $email_body = $email_content->es_mail_body;
                $email_subject = $email_content->es_mail_subject;
                $msgBody = $email_content->es_sms_body;
            } else {
                $email_body = $email_content->en_mail_body;
                $email_subject = $email_content->en_mail_subject;
                $msgBody = $email_content->en_sms_body;
            }

            $mail_body = str_replace(
                ['%name'], [ucwords($data['first_name']) . " " . ucwords($data['last_name'])], $email_body
            );

            Mail::send('email', ['varContent' => $mail_body, 'to' => $data["email"]
                ], function ($message) use ($data, $email_subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($data["email"], $data["first_name"])
                    ->subject($email_subject);
            });
            $activtyData = self::addActivityLog(75, trans('activity_messages.app_status_modified'), $data);
            $sendEmailId = self::addSendEmailLog($mail_body, $email_subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        } else {
            $activtyData = self::addActivityLog(75, trans('activity_messages.app_status_modified'), $data);
            $sendEmailId = self::addSendEmailLog($mail_body, $email_subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        }
    }
    /**
     * Send Mail to Owner on hardpull report
     *
     * @param array $usrDetailArray
     */
    public function onHardPullMailToOwner($usrDetailArray){
        $usrDetailArray = unserialize($usrDetailArray);
        $email_content = EmailTemplate::getEmailTemplate('Mail to customer that application is complete and loan decision is in progress (based on Hardpull)');
        $mail_body = "";
        $email_subject = "";
        if ($email_content) {
            if ($usrDetailArray['lang'] == config('b2c_common.PREFERED_COMM_ENG')){
                $email_body = $email_content->en_mail_body;
                $email_subject = $email_content->en_mail_subject;
            } else {
                $email_body = $email_content->es_mail_body;
                $email_subject = $email_content->es_mail_subject;
            }


            $mail_body = str_replace(
                ['%name', '%number', '%Agentname'],
                [ucwords($usrDetailArray['name']), '787-620-7963', 'Agent Name'],
                $email_body
            );
            
            $isSend = ActivityLog::chkTuHardPullEmilLogedByAppId($usrDetailArray["app_id"]);

            if(empty($isSend) && $usrDetailArray["app_type"]!=3){
                Mail::send('email', ['varContent' => $mail_body, 'to' => $usrDetailArray["email"]
                    ], function ($message) use ($usrDetailArray, $email_subject) {
                    $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                    $message->to($usrDetailArray["email"], $usrDetailArray["name"])
                        ->subject($email_subject);
                });
            }

            $activtyData = self::addActivityLog(76, trans('activity_messages.tu_hard_pulled_for_owner'), $usrDetailArray);
            if(empty($isSend) && $usrDetailArray["app_type"]!=3){
                $sendEmailId = self::addSendEmailLog($mail_body, $email_subject, $email_content, $usrDetailArray);
                if ($activtyData) {
                    self::addActivityEmail($activtyData, $sendEmailId);
                }
            }
        } else {
            $activtyData = self::addActivityLog(76, trans('activity_messages.tu_hard_pulled_for_owner'), $usrDetailArray);
        }
    }

    /**
     * Event System Generated App Statu
     *
     * @param type $appUpAppointIfno
     */
    public function onSystemGeneratedAppStatus($systemGenAppStatus)
    {
        $systemGenAppStatusInfo = unserialize($systemGenAppStatus);
        self::addActivityLog(76, $systemGenAppStatusInfo['activity_desc'], $systemGenAppStatusInfo);
    }
    
    /**
     * Function to Send SMS to Added Guarantor to verify his acceptance 
     * 
     * @param type $smsData
     */
    public function sendVerificationSMSToGuarantor($smsData)
    {

        $smsData = unserialize($smsData);
        $sms_content = EmailTemplate::getEmailTemplate('Guarantor Consent SMS');
        $userDetail = User::select('prefered_comm_mode')->find($smsData['user_id']);
        if (empty($sms_content)) {
            return;
        }
        if ($userDetail['prefered_comm_mode'] == config('b2c_common.PREFERED_COMM_SPA')) {
            $msgBody = $sms_content->es_sms_body;
            if (empty($msgBody)) {
                $msgBody = $sms_content->en_sms_body;
            }
        } else {
            $msgBody = $sms_content->en_sms_body;
        }
        
        $appno = str_replace('OBA', '', $smsData['app_no']);
        $msgBody = str_replace(
            ['%appno'], [$appno], $msgBody
        );

        $smsPhoneNo = (!empty($this->default_receiver_phoneno) ? $this->default_receiver_phoneno : str_replace('-', '', $smsData['phone_no']));
        try {

            if (!empty($smsPhoneNo) && !empty($msgBody)) {
                
                $smsPhoneNo = $this->phoneno_prefix . $smsPhoneNo;
                $callBackUrl = preg_replace('/http(s)?\:\/\//','https://oriental:7Ke4HQhPz7EmYKU8XJ3Q@',route('updateconsentstatus'));
                
                $apiresponse = Helpers::sendTextMessage($smsPhoneNo, $msgBody,$callBackUrl);
                $data = ['is_send' => 1, 'sms_template_id' => $sms_content->id, 'sms_content' => $msgBody, 'twilio_id' => $apiresponse->sid];
                ConsentSMS::updateDetail($smsData['log_id'], $data);
                
            }
        } catch (Exception $ex) {
            self::addSMSLog($sms_content->id, $smsPhoneNo, $ex->getCode() . '--' . $ex->getMessage(), $smsData['source']);
        }
    }
    
    /**
     * reminder mail to all users w.r.t last app_status updated_at
     *
     * @param object $current_data application data
     */
    public function onReminderMail($current_data)
    {
        $details = unserialize($current_data);
        $activity = [];
        $activity['app_user_id'] = $details['app_user_id'];
        $activity['app_id']      = $details['app_id'];
        $activity['email']       = $details['email'];

        //email template
        $email_content = EmailTemplate::getEmailTemplate($details['template']);
        if ($email_content) {
            $email_body = $email_content->en_mail_body;
            $email_subject = $email_content->en_mail_subject;
             $email_from = config('b2c_common.FRONTEND_FROM_EMAIL_NAME_EN');
             if(!empty($details['app_locale']) && $details['app_locale'] == 'fr')
            {
                  $email_from = config('b2c_common.FRONTEND_FROM_EMAIL_NAME_FR');
            $email_body = $email_content->fr_mail_body;
            $email_subject = $email_content->fr_mail_subject;
            }
            else{
               $details['app_locale'] = 'en'; 
            }
            $expName = explode("@", $details["email"]);
            $name = (isset($details['name']) && !empty($details['name'])) ? $details['name'] : $expName[0];
            $expiry_date = isset($details['expiry_date']) ? $details['expiry_date'] : null;
            $ower_name = isset($details['owner_name']) ? $details['owner_name'] : null;
//            if (isset($details['primary']) && $details['primary'] == 0) {
//                $tmp = $name;
//                $name = $ower_name;
//                $ower_name = $tmp;
//            }
            $access_link = isset($details['access_link']) && $details['access_link'] != null ? $details['access_link'] : Helpers::getLoginPath($details['app_locale']);
            $mail_body = str_replace(
                ['%name', '%expiry_date', '%siteUrl', '%ownername', '%tollnumber', '%privacylink'],
                [$name, $expiry_date, $access_link, $ower_name, config('b2c_common.TOLL_FREE_NUMBER2'), config('b2c_common.HSBC_PRIVACY')],
                $email_body
            );
            Mail::send('email', ['varContent' => $mail_body, 'to' => $details["email"], 'is_cron_mail' => true
            ], function ($message) use ($details, $email_subject, $email_from) {
                
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), $email_from);
                $message->to($details["email"])->subject($email_subject);
            });
            
            self::addActivityLog(85, $email_subject, $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $email_subject, $email_content, $activity);
            
        } else {
            self::addActivityLog(86, trans('activity_messages.remainder_not_send'), $activity);
        }

    }
    /**
     * reminder mail to all users w.r.t last app_status updated_at
     *
     * @param object $current_data application data
     */
     public function onNewCustReminderMail($current_data)
    {
        
        $details = unserialize($current_data);
        $activity = [];
        $activity['app_user_id'] = $details['app_user_id'];
        $activity['app_id']      = $details['app_id'];
        $activity['email']       = $details['email'];

        //email template
        $email_content = EmailTemplate::getEmailTemplate($details['template']);
        
        if ($email_content) {
            $email_body = '';
             if(!empty($details['app_locale']) && $details['app_locale'] == 'fr')
            {
            $email_from = config('b2c_common.FRONTEND_FROM_EMAIL_NAME_FR');
            $email_body = $email_content->fr_mail_body;
            $email_subject = $email_content->fr_mail_subject;
            }
            else{
             $email_body = $email_content->en_mail_body;
             $email_subject = $email_content->en_mail_subject;
             $email_from = config('b2c_common.FRONTEND_FROM_EMAIL_NAME_EN');
             $details['app_locale'] = 'en';
            }
           
            $name = isset($details['name']) ? $details['name'] : null;
            $expiry_date = isset($details['expiry_date']) ? $details['expiry_date'] : null;
            $access_link = isset($details['access_link']) && $details['access_link'] != null ? $details['access_link'] : Helpers::getLoginPath($details['app_locale']);
            if ($details['template'] ==  "Application start reminder after 1 days") {
                $mail_body = str_replace(
                ['%name', '%siteUrl', '%tollnumber'],
                [ucwords($name), $access_link, config('b2c_common.TOLL_FREE_NUMBER2')],
                $email_body
                );
            } else {
                $mail_body = str_replace(
                ['%name', '%tollnumber'],
                [ucwords($name), config('b2c_common.TOLL_FREE_NUMBER2')],
                $email_body
            );
            }
            Mail::send('email', ['varContent' => $mail_body, 'to' => $details["email"],'app_locale' => $details["app_locale"], 'is_cron_mail' => true
            ], function ($message) use ($details, $email_subject,$email_from) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), $email_from);
                $message->to($details["email"])->subject($email_subject);
            });
            
            self::addActivityLog(85, $email_subject, $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $email_subject, $email_content, $activity);
            
        } else {
            self::addActivityLog(86, trans('activity_messages.remainder_not_send'), $activity);
        }

    }
    
    /**
     * reminder mail to all users w.r.t last app_status updated_at
     *
     * @param object $current_data application data
     */
    public function onInProgressReminderMail($current_data)
    {
        
        $details = unserialize($current_data);
        $activity = [];
        $activity['app_user_id'] = $details['app_user_id'];
        $activity['app_id']      = $details['app_id'];
        $activity['email']       = $details['email'];

        //email template
        $email_content = EmailTemplate::getEmailTemplate($details['template']);
        if ($email_content) {
            $email_from = config('b2c_common.FRONTEND_FROM_EMAIL_NAME_EN');
            $email_body = $email_content->en_mail_body;
            $email_subject = $email_content->en_mail_subject;
             if(!empty($details['app_locale']) && $details['app_locale'] == 'fr')
            {
                 $email_from =  config('b2c_common.FRONTEND_FROM_EMAIL_NAME_FR');
            $email_body = $email_content->fr_mail_body;
            $email_subject = $email_content->fr_mail_subject;
            }
            else{
              $details['app_locale'] = 'en';  
            }
            $name = isset($details['name']) ? $details['name'] : '';
            $expiry_date = isset($details['expiry_date']) ? $details['expiry_date'] : null;
            $access_link = isset($details['access_link']) && $details['access_link'] != null ? $details['access_link'] : Helpers::getLoginPath($details['app_locale']);
            $mail_body = str_replace(
                ['%name', '%siteUrl', '%tollnumber', '%app_id', '%name'],
                [ucwords($name), $access_link, config('b2c_common.TOLL_FREE_NUMBER2'), $details['app_id'], $name],
                $email_body
            );
            
            $email_subject = str_replace(
                ['%app_id'],
                [$details['app_id']],
                $email_subject
            );
            Mail::send('email', ['varContent' => $mail_body,'app_locale' =>$details["app_locale"],'mail_expire'=>'expire', 'to' => $details["email"], 'is_cron_mail' => true
            ], function ($message) use ($details, $email_subject, $email_from) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), $email_from);
                $message->to($details["email"])->subject($email_subject);
            });
            
            self::addActivityLog(85, $email_subject, $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $email_subject, $email_content, $activity);
            
        } else {
            self::addActivityLog(86, trans('activity_messages.remainder_not_send'), $activity);
        }

    }
    /**
     * send mail to application submission
     *
     * @param object $current_data application data
     */
    public function onAppSubmitMail($current_data)
    {
        $details = unserialize($current_data);
        $activity = [];
        $activity['app_user_id'] = $details['app_user_id'];
        $activity['app_id']      = $details['app_id'];
        $activity['email']       = $details['lead_email'];

        //email template
        $email_content = EmailTemplate::getEmailTemplate('Application Submitted');
        if ($email_content) {
            $email_body = $email_content->en_mail_body;
            $email_subject = $email_content->en_mail_subject;
            $name = isset($details['lead_fname']) ? $details['lead_fname'] .' '. $details['lead_lname'] : '';
//            $expiry_date = isset($details['expiry_date']) ? $details['expiry_date'] : null;
//            $access_link = isset($details['access_link']) && $details['access_link'] != null ? link_to($details['access_link']) : link_to(Helpers::getLoginPath());
            $mail_body = str_replace(
                ['%name', '%tollnumber'],
                [ucwords($name), config('b2c_common.TOLL_FREE_NUMBER2')],
                $email_body
            );
            
            
            Mail::send('email', ['varContent' => $mail_body, 'to' => $details["lead_email"]
            ], function ($message) use ($details, $email_subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($details["lead_email"])->subject($email_subject);
            });
            
            self::addActivityLog(85, $email_subject, $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $email_subject, $email_content, $activity);
            
        } else {
            self::addActivityLog(86, trans('activity_messages.remainder_not_send'), $activity);
        }

    }
    
    /**
     * Event that would be fired on save offer
     *
     * @param object $application application data
     *
     * @since 0.1
     *
     */
    public function onSendOfferToCustomer($application)
    {   
        $application = unserialize($application);
        $app_id = isset($application['app_id'])?(int) $application['app_id']:null;
        //Send mail to cutomer
        $email_content = EmailTemplate::getEmailTemplate("Application Status - Offer Send to Customer");
        if($application['app_locale'] == config('b2c_common.FRENCH_LOCALE')){
           $email_body = $email_content->fr_mail_body;
           $email_subject = $email_content->fr_mail_subject;
           $click_here = "Cliquez sur le lien";
           $application['app_locale']='fr';
        }else{
           $email_body = $email_content->en_mail_body; 
           $email_subject = $email_content->en_mail_subject; 
           $click_here = "Click here";
           $application['app_locale']='en';
        }
       $mail_body     = str_replace(
            ['%name', '%loginlink'],
            [htmlentities(ucwords($application['first_name']." ".$application['last_name'])), link_to(Helpers::getLoginPath($application['app_locale']), $click_here)],
            $email_body
        );
        
        $to         = $application['email'];
        //$subject    = str_replace(['%app_id'], [$application['app_id']], $email_content->en_mail_subject);
        $subject     = $email_subject;
        $cc         = $email_content->reciepient_cc;
        $bcc        = $email_content->reciepient_bcc;
        $attachment = null;
        Mail::send('email', ['varContent' => $mail_body, 'to' => $application["email"],'app_locale' => $application['app_locale'],'app_id'=>$app_id
            ], function ($message) use ($application, $email_content, $subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), trans('headings.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($application["email"])->subject($subject);
            });
        //Helpers::sendEmail($to, $subject, $mail_body, $attachment, $cc, $bcc); 
        
        /**
        * Call Activity Event
        */
       $activity = [];
       $activity['app_user_id'] = (int) $application['id'];
       $activity['app_id']      = (int) $application['app_id'];
       $activity['email']       = $application['email'];
       $activtyData = self::addActivityLog(92, trans('activity_messages.offer_information_sent_to_customer'), $activity);
       $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
       if ($activtyData) {
           self::addActivityEmail($activtyData, $sendEmailId);
       }
    }
     
    /**
     * Event that would be fired when a offer notification sent to RM
     *
     * @param object $caseArray Case object on Share
     *
     * @since 0.1
     */
    public function onSendOfferToRM($mailBodyInfo)
    {
        try {
        $arrData       = unserialize($mailBodyInfo);
        /**
         * Get Email Template from Database
         */
        $email_content = EmailTemplate::getEmailTemplate("Application Status - Offer Send to RM");
        $mail_body     = str_replace(
            ['%rm_name', '%custmer_name', '%app_id', '%sanction_amt', '%intrest_rate', '%term'],
            [ucwords($arrData['rm_name']), ucwords($arrData['custmer_name']), $arrData['app_id'], Helpers::changeCurrencyFormat($arrData['sanction_amt']),
            $arrData['intrest_rate'], $arrData['term']],
            $email_content->en_mail_body
        );
        
            $to         = $arrData['rm_email'];
            $subject    = str_replace(['%app_id'], [$arrData['app_id']], $email_content->en_mail_subject);
            $cc         = $email_content->reciepient_cc;
            $bcc        = $email_content->reciepient_bcc;
            $attachment = null;
            Helpers::sendEmail($to, $subject, $mail_body, $attachment, $cc, $bcc);
            /**
             * Call Activity Event
             */
            $activity = [];
            $activity['app_user_id'] = (int) $arrData['app_user_id'];
            $activity['app_id']      = (int) $arrData['app_id'];
            $activity['email']       = $arrData['rm_email'];
            $activtyData = self::addActivityLog(91, trans('activity_messages.offer_information_sent_to_rm'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        } catch (Exception $ex) {
            if (method_exists($ex, 'getRequest')) {
                return response($ex->getRequest());
            }
        } 
    }
    
    
     /**
     * Event that would be fired when a offer accepted by customer
     *
     * @param object $application Case object on Share
     *
     * @since 0.1
     */
    public function onOfferAcceptedByCustomer($application)
    {
        try {
        $arrData       = unserialize($application);
        /**
         * Get Email Template from Database
         */
        $email_from = config('b2c_common.FRONTEND_FROM_EMAIL_NAME_EN');
        $email_content = EmailTemplate::getEmailTemplate("Application Process- Offer Accepted By Customer");
        $mail_body     = str_replace(
            ['%app_id', '%sanction_amt', '%interest_rate', '%term'],
            [ucwords($arrData['app_id']), Helpers::changeCurrencyFormat($arrData['sanction_amt']), $arrData['interest_rate'], $arrData['term']],
            $email_content->en_mail_body
        );
            $subject     = str_replace(['%app_id'], [$arrData['app_id']], $email_content->en_mail_subject);
            $to         = $arrData['rm_email'];
            $cc         = $email_content->reciepient_cc;
            $bcc        = $email_content->reciepient_bcc;
            $attachment = null;
            Helpers::sendEmail($to, $subject, $mail_body, $attachment, $cc, $bcc, $email_from);
            
            /*$to         = $arrData['co_email'];
            $attachment = null;
            Helpers::sendEmail($to, $subject, $mail_body, $attachment, $cc, $bcc);*/
            
            /**
             * Call Activity Event
             */
            $activity = [];
            $activity['app_user_id'] = (int) $arrData['app_user_id'];
            $activity['app_id']      = (int) $arrData['app_id'];
            $activity['email']       = $arrData['rm_email'];
            $activtyData = self::addActivityLog(93, trans('activity_messages.offer_accepeted_by_customer'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
            
           /* $activity = [];
            $activity['app_user_id'] = (int) $arrData['app_user_id'];
            $activity['app_id']      = (int) $arrData['app_id'];
            $activity['email']       = $arrData['co_email'];
            $activtyData = self::addActivityLog(93, trans('activity_messages.offer_accepeted_by_customer'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
            */
        } catch (Exception $ex) {
            if (method_exists($ex, 'getRequest')) {
                return response($ex->getRequest());
            }
        } 
    }
    
    
     /**
     * Event that would be fired when a offer declined by customer
     *
     * @param object $application Case object on Share
     *
     * @since 0.1
     */
    public function onOfferDeclinedByCustomer($application)
    {
        try {
        $arrData       = unserialize($application);
        /**
         * Get Email Template from Database
         */
        $email_from = config('b2c_common.FRONTEND_FROM_EMAIL_NAME_EN');
        $email_content = EmailTemplate::getEmailTemplate("Application Process- Offer Declined By Customer");
        $mail_body     = str_replace(
            ['%app_id', '%sanction_amt', '%interest_rate', '%term', '%note'],
            [ucwords($arrData['app_id']), Helpers::changeCurrencyFormat($arrData['sanction_amt']), $arrData['interest_rate'], $arrData['term'], $arrData['note']],
            $email_content->en_mail_body
        );
        
            $subject     = str_replace(['%app_id'], [$arrData['app_id']], $email_content->en_mail_subject);
        
            $to         = $arrData['rm_email'];
            $cc         = $email_content->reciepient_cc;
            $bcc        = $email_content->reciepient_bcc;
            $attachment = null;
            Helpers::sendEmail($to, $subject, $mail_body, $attachment, $cc, $bcc,$email_from);
            
            /*$to         = $arrData['co_email'];
            $cc         = $email_content->reciepient_cc;
            $bcc        = $email_content->reciepient_bcc;
            $attachment = null;
            Helpers::sendEmail($to, $subject, $mail_body, $attachment, $cc, $bcc);*/
            
            /**
             * Call Activity Event
             */
            $activity = [];
            $activity['app_user_id'] = (int) $arrData['app_user_id'];
            $activity['app_id']      = (int) $arrData['app_id'];
            $activity['email']       = $arrData['rm_email'];
            $activtyData = self::addActivityLog(94, trans('activity_messages.offer_declined_by_customer'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
            
           /* 
            $activity = [];
            $activity['app_user_id'] = (int) $arrData['app_user_id'];
            $activity['app_id']      = (int) $arrData['app_id'];
            $activity['email']       = $arrData['co_email'];
            $activtyData = self::addActivityLog(94, trans('activity_messages.offer_declined_by_customer'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }*/
        } catch (Exception $ex) {
            if (method_exists($ex, 'getRequest')) {
                return response($ex->getRequest());
            }
        } 
    }
    
     /**
     * Event that would be fired when a application declined by co
     *
     * @param object $application Case object on Share
     *
     * @since 0.1
     */
    public function onApplicationDeclined($application)
    {
        try {
        $arrData       = unserialize($application);
        /**
         * Get Email Template from Database
         */
        $email_from = config('b2c_common.FRONTEND_FROM_EMAIL_NAME_EN');
        $email_content = EmailTemplate::getEmailTemplate("Application Process- Application Declined By CO");
        $mail_body     = str_replace(
            ['%loan_amt', '%app_id', '%role', '%co_name', '%cust_name', '%business_name', '%comment'],
            [ucwords($arrData['loan_amt']), ucwords($arrData['app_id']), $arrData['role'], $arrData['co_name'], $arrData['cust_name'], $arrData['business_name'], $arrData['comment']],
            $email_content->en_mail_body
        );
        
            $subject     = str_replace(['%app_id'], [$arrData['app_id']], $email_content->en_mail_subject);
        
            $to         = $arrData['rm_email'];
            $cc         = $email_content->reciepient_cc;
            $bcc        = $email_content->reciepient_bcc;
            $attachment = null;
            Helpers::sendEmail($to, $subject, $mail_body, $attachment, $cc, $bcc,$email_from);
            
            /*$to         = $arrData['co_email'];
            $cc         = $email_content->reciepient_cc;
            $bcc        = $email_content->reciepient_bcc;
            $attachment = null;
            Helpers::sendEmail($to, $subject, $mail_body, $attachment, $cc, $bcc);*/
            
            /**
             * Call Activity Event
             */
            $activity = [];
            $activity['app_user_id'] = (int) $arrData['app_user_id'];
            $activity['app_id']      = (int) $arrData['app_id'];
            $activity['email']       = $arrData['rm_email'];
            $activtyData = self::addActivityLog(95, trans('activity_messages.application_declined'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
         } catch (Exception $ex) {
            if (method_exists($ex, 'getRequest')) {
                return response($ex->getRequest());
            }
        } 
    }
     /**
     * Event that would be fired when a application is funded
     *
     * @param object $application Case object on Share
     *
     * @since 0.1
     */
    public function onAppFundedMail($application)
    {
        try {
        $arrData       = unserialize($application);
        /**
         * Get Email Template from Database
         */
        $email_content = EmailTemplate::getEmailTemplate("Application Funded");
        if($arrData['app_locale'] == config('b2c_common.FRENCH_LOCALE')){
            $email_body = $email_content->fr_mail_body;
            $email_subject = $email_content->fr_mail_subject;
        }else{
            $email_body = $email_content->en_mail_body; 
            $email_subject = $email_content->en_mail_subject; 
        }
        $mail_body     = str_replace(
            ['%name', '%tollnumber'],
            [ucwords($arrData['lead_fname']), config('b2c_common.TOLL_FREE_NUMBER2')],
            $email_body
        );
        
            Mail::send('email', ['varContent' => $mail_body, 'to' => $arrData["lead_email"],'app_locale' => $arrData['app_locale']
            ], function ($message) use ($arrData, $email_subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), trans('headings.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($arrData["lead_email"])->subject($email_subject);
            });
                       
            /**
             * Call Activity Event
             */
            $activity = [];
            $activity['app_user_id'] = (int) $arrData['app_user_id'];
            $activity['app_id']      = (int) $arrData['app_id'];
            $activity['email']       = $arrData['lead_email'];
            $activtyData = self::addActivityLog(95, trans('activity_messages.application_declined'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
         } catch (Exception $ex) {
            if (method_exists($ex, 'getRequest')) {
                return response($ex->getRequest());
            }
        } 
    }
    
    
         /**
     * Event that would be fired when a application approved by co
     *
     * @param object $application Case object on Share
     *
     * @since 0.1
     */
    public function onApplicationApproved($application)
    {
        try {
        $arrData       = unserialize($application);
        /**
         * Get Email Template from Database
         */
        
        $email_from = config('b2c_common.FRONTEND_FROM_EMAIL_NAME_EN');
        $email_content = EmailTemplate::getEmailTemplate("Application Process- Application Approved By CO");
        $mail_body     = str_replace(
            ['%loan_amt', '%app_id', '%role', '%co_name', '%cust_name', '%business_name', '%approved_amt', '%interest_rate', '%term'],
            [ucwords($arrData['loan_amt']), ucwords($arrData['app_id']), $arrData['role'], $arrData['co_name'], $arrData['cust_name'], $arrData['business_name'], $arrData['approved_amt'], $arrData['intrest_rate'], $arrData['term']],
            $email_content->en_mail_body
        );
            $subject     = str_replace(['%app_id'], [$arrData['app_id']], $email_content->en_mail_subject);
        
            $to         = $arrData['rm_email'];
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
            $activity['email']       = $arrData['rm_email'];
            $activtyData = self::addActivityLog(96, trans('activity_messages.application_approved'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
         } catch (Exception $ex) {
            if (method_exists($ex, 'getRequest')) {
                return response($ex->getRequest());
            }
        } 
    }
    
    
    /**
     * Mail shoot for business info
     *
     * @param $content holds the mail info
     *
     * @since 0.1
     */
    public function shootEmail($user)
      {
        $user = unserialize($user);
        if(!empty($user['email_template_for']) && $user['email_template_for']== config('b2c_common.CASL')){
            $biz_info_temp = EmailTemplate::getEmailTemplate("casl consent approval mail from customer");
        } else if (!empty($user['email_template_for']) && $user['email_template_for']== config('b2c_common.BUSINESS_CONSENT_TEMPLATE')){
            $biz_info_temp = EmailTemplate::getEmailTemplate("business consent approval mail from customer");
        }else{
            $biz_info_temp = EmailTemplate::getEmailTemplate("consent approval mail from customer");
        }
        $name          =   $user['first_name'].' '.$user['last_name'];
        $app_id        =   !empty($user['app_id']) ? $user['app_id'] : '';
        $customer_name =   !empty($user['customer_name']) ? $user['customer_name'] : '';
        $mail_body = str_replace(
           ['%name','%app_id','%customer_name'], [ucwords($name), $app_id,ucwords($customer_name)], $biz_info_temp->en_mail_body
        );
        
        if(!empty($biz_info_temp)) {
        $sent = Mail::send('email', ['varContent' => $mail_body, 'to' => $user["email"]
                  ], function ($message) use ($user, $biz_info_temp) {
                   $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME_EN'));
                          $message->to($user["email"])->subject($biz_info_temp->en_mail_subject);
              });
        if ($sent) {
             $activtyData = self::addActivityLog(97, trans('activity_messages.email_brm_for_doc'), $activity);
             $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
             return true;
         }
        }
      }

      
       /**
     * Mail shoot for business info
     *
     * @param $content holds the mail info
     *
     * @since 0.1
     */
    public function onElectronicDocSubmit($userData)
      {
        $user = unserialize($userData);
        $biz_info_temp = EmailTemplate::getEmailTemplate("Electronic Doc Consent - Document sucessfully uploaded");
        if ($biz_info_temp) {
        $mail_body = str_replace(
        ['%name','%email','%app_id','%case_id','%customer_name','%siteurl'], [$user['brm_name'],ucwords($user['email']), $user['app_id'],$user['app_user_id'],$user['customer_name'],link_to(Helpers::getBackendLoginPath(), Helpers::getBackendLoginPath())], $biz_info_temp->en_mail_body
        );
        
       $activity = [];
       $activity['app_user_id'] = (int) $user['app_user_id'];
       $activity['app_id']      = (int) $user['app_id'];
       $activity['email']       = $user['email'];
            
       $sent = Mail::send('email', ['varContent' => $mail_body,  'to' => $user["email"]
                      ], function ($message) use ($user, $biz_info_temp) {
                          $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME_EN'));
                          $message->to($user["email"])->subject($biz_info_temp->en_mail_subject);
                      });
        if ($sent) {
            $activtyData = self::addActivityLog(97, trans('activity_messages.email_brm_for_doc'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $subject, $email_content, $activity);
        return true;
        }
        return false;
        } else {
        }
      }
      
       /**
     * Email On share lead
     *
     * @param object $application application data
     */
  public function onShareApprovalForBackend($application)
    {
        $application = unserialize($application);
        $primmaryOwner = User::getUserData(['id'=> (int) $application['user_id']]);
        $mail_body = "";
        $mail_subject = "";
        $activity = [];
        $cust_email = $application['owner_detail']['email'];
        $app_id = $application['owner_detail']['app_id'];
        $app_user_id = $application['owner_detail']['app_user_id'];
        $primary_onr = Helpers::getPrimaryOnrName($app_id);
        $new_primary_owner = $primary_onr['first_name'].' '.$primary_onr['last_name'];
        $primmaryOwnerName = $primmaryOwner['first_name'].' '.$primmaryOwner['last_name'];
        $name = $application['owner_detail']['first_name'].' '.$application['owner_detail']['last_name'];
        $email_content = EmailTemplate::getEmailTemplate("Request for your information and consent co-owner");
	$fr_email_body = $email_content->fr_mail_body;
	$fr_mail_body = str_replace(
                ['%name', '%urllink', '%tollnumber', '%privacylink','%primaryOwner'],
                [ucwords($name), $application['access_link'].'&ln=fr', config('b2c_common.TOLL_FREE_NUMBER2'), config('b2c_common.HSBC_PRIVACY'),ucwords($new_primary_owner)],
                $fr_email_body
            );
	$mail_title = "Request for your information and consent co-owner";
        if(!empty($primmaryOwner['cust_email']) && $primmaryOwner['cust_email'] == $cust_email)
        {
           // $name = $primmaryOwner['first_name'].' '.$primmaryOwner['last_name'];
            $email_content = EmailTemplate::getEmailTemplate("Request for your information and consent");
	    $fr_email_body = $email_content->fr_mail_body;
	    $fr_mail_body = str_replace(
                ['%name', '%urllink', '%tollnumber', '%privacylink','%primaryOwner'],
                [ucwords($name), $application['access_link'].'&ln=fr', config('b2c_common.TOLL_FREE_NUMBER2'), config('b2c_common.HSBC_PRIVACY'),ucwords($primmaryOwnerName)],
                $fr_email_body
            );
	    $mail_title = "Request for your information and consent";
        }
        //Send mail to Case Manager
        if ($email_content)
        {
           if($application['app_locale'] == config('b2c_common.FRENCH_LOCALE')){
                $email_body = $email_content->fr_mail_body;
                $email_subject = $email_content->fr_mail_subject;
           }else{
                $email_body = $email_content->en_mail_body; 
                $email_subject = $email_content->en_mail_subject; 
           }
            $mail_subject = str_replace('%Applicant Name',ucwords($name),$email_subject);
            $mail_body = str_replace(
                ['%name', '%urllink', '%tollnumber', '%privacylink','%primaryOwner'],
                [ucwords($name), $application['access_link'].'&ln=en', config('b2c_common.TOLL_FREE_NUMBER2'), config('b2c_common.HSBC_PRIVACY'),ucwords($new_primary_owner)],
                $email_body
            );
            Mail::send('email', ['varContent' => $mail_body, 'to' => $application["email"],'app_locale' => $application['app_locale'],'fr_mail_body' => $fr_mail_body,'mail_title' => $mail_title
                ], function ($message) use ($application, $mail_subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($application["email"],$application['owner_detail']['first_name'])->subject("Request for your business financial information/ Demande de renseignements financiers sur votre entreprise");
            });

            $activity['user_name'] = '';            
            $activity['app_user_id'] = $application['user_id'];
            $activity['app_id'] = $application['app_id'];
            $activity['is_display_in_activity'] = $application['is_display_in_activity'];
            $activtyData = self::addActivityLog(79, trans('activity_messages.email_guarantor_information_sent', ['guarantors' => $application["email"]]), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $mail_subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        } else {            
            $activity['user_name'] = '';
            $activity['app_user_id'] = $application['user_id'];
            $activity['app_id'] = $application['app_id'];
            $activity['is_display_in_activity'] = $application['is_display_in_activity'];
            $activtyData = self::addActivityLog(79, trans('activity_messages.email_guarantor_information_sent', ['guarantors' => $application["email"]]), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $mail_subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        }
    }
    
    /**
     * email on consent approval
     *
     * @param object $application application data
     */
    public function onConsentApproval($application)
    {
        $application = unserialize($application);
        $mail_body = '';
        $mail_subject = '';
        $activity = [];
        //Send mail to primary user for business information
        $email_content = EmailTemplate::getEmailTemplate("Request for your information and consent");
        $name = isset($application['userData']['first_name']) ? $application['userData']['first_name'].' '.$application['userData']['last_name'] : null;
        if ($email_content) {
            $email_body = $email_content->en_mail_body;
            $fr_email_body = $email_content->fr_mail_body;
            $email_subject = $email_content->en_mail_subject;
            $mail_subject = str_replace('%Applicant Name',ucwords($name), $email_subject);
            $mail_body = str_replace(
                ['%name', '%urllink', '%tollnumber', '%privacylink'],
                [ucwords($name), $application['access_link'].'&ln=en', config('b2c_common.TOLL_FREE_NUMBER2'), config('b2c_common.HSBC_PRIVACY')],
                $email_body
            );
            $fr_mail_body = str_replace(
                ['%name', '%urllink', '%tollnumber', '%privacylink'],
                [ucwords($name), $application['access_link'].'&ln=fr', config('b2c_common.TOLL_FREE_NUMBER2'), config('b2c_common.HSBC_PRIVACY')],
                $fr_email_body
            );
            Mail::send('email', ['varContent' => $mail_body, 'to' => $application['email'],'mail_title'=> 'Request for your information and consent','fr_mail_body' => $fr_mail_body
                ], function ($message) use ($application, $mail_subject) {
                $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), config('b2c_common.FRONTEND_FROM_EMAIL_NAME'));
                $message->to($application['email'])->subject('Request for your business financial information/ Demande de renseignements financiers sur votre entreprise');
            });
            $activity['user_name'] = '';            
            $activity['app_user_id'] = $application['app_user_id'];
            $activity['app_id'] = $application['app_id'];
            $activity['is_display_in_activity'] = $application['is_display_in_activity'];
            $activity['email'] = $application['email'];
            $activtyData = self::addActivityLog(79, trans('activity_messages.email_business_info'), $activity);
            $sendEmailId = self::addSendEmailLog($mail_body, $mail_subject, $email_content, $activity);
            if ($activtyData) {
                self::addActivityEmail($activtyData, $sendEmailId);
            }
        }
    }
    
     /**
     * Mail shoot after canceled application
     *
     *@param array $data application data
     * 
     * @since 0.1
     */
    public function mailAfterCanceledApplication($data)
    {
        $user = unserialize($data);
        $info_temp = EmailTemplate::getEmailTemplate("Your business credit application has been cancelled");
        $name = $user['name'];
        $arrSubject = [];
        if($user['app_locale'] == config('b2c_common.FRENCH_LOCALE')){ 
           $mail_body = str_replace(
            ['%name'], [ucwords($name)], $info_temp->fr_mail_body
            );
            $arrSubject['subject'] = $info_temp->fr_mail_subject;
            $arrSubject['from'] = 'Services aux petites entreprises de la HSBC';
        }else{ 
           $mail_body = str_replace(
            ['%name'], [ucwords($name)], $info_temp->en_mail_body
            );
            $arrSubject['subject'] = $info_temp->en_mail_subject;
            $arrSubject['from'] = 'HSBC Small Business Banking';
        }
        $sent = Mail::send('email', ['varContent' => $mail_body, 'to' => $user["email"],'app_locale' => $user['app_locale']
                  ], function ($message) use ($user, $arrSubject) {
                   $message->from(config('b2c_common.FRONTEND_FROM_EMAIL'), $arrSubject['from']);
                          $message->to($user["email"])->subject($arrSubject['subject']);
              });
       if ($sent) {  
            $activity['app_user_id'] = $user['app_user_id'];
            $activity['app_id']      = $user['app_id']; 
            $activity['email']       = $user['email'];
            self::addActivityLog(97, trans('activity_messages.canceled_application'), $activity);
            self::addSendEmailLog($mail_body, $arrSubject['subject'], $info_temp, $activity);
            return true;
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
            'co_owner_approval.send',
            'App\Repositories\Events\ApplicationEventsListener@onShareApproval'
        );
        
        $events->listen(
            'emailguarantor_owner.create',
            'App\Repositories\Events\ApplicationEventsListener@onCompleteAccessGuarantorForm'
        );

        $events->listen(
            'case.shared',
            'App\Repositories\Events\ApplicationEventsListener@onShareCase'
        );

        $events->listen(
            'application.start',
            'App\Repositories\Events\ApplicationEventsListener@onStartApplication'
        );
                
        $events->listen(
            'application.audit',
            'App\Repositories\Events\ApplicationEventsListener@onApplicationAudit'
        );

        $events->listen(
            'application.edit',
            'App\Repositories\Events\ApplicationEventsListener@onApplicationEdit'
        );
        $events->listen(
            'application.newcreatesharetosalesteam',
            'App\Repositories\Events\ApplicationEventsListener@onApplicationCreateNotifyCST'
        );
        $events->listen(
            'application.emailapplicationtobanker', 'App\Repositories\Events\ApplicationEventsListener@onShareApplicationToBT'
        );
        $events->listen(
            'application.view',
            'App\Repositories\Events\ApplicationEventsListener@onApplicationView'
        );
        $events->listen(
            'lead.view',
            'App\Repositories\Events\ApplicationEventsListener@onLeadView'
        );

        $events->listen(
            'note.view',
            'App\Repositories\Events\ApplicationEventsListener@onNotesAndActivityView'
        );
        $events->listen(
            'note.save',
            'App\Repositories\Events\ApplicationEventsListener@onNoteSave'
        );
        $events->listen(
            'mail.send',
            'App\Repositories\Events\ApplicationEventsListener@onMailSend'
        );
        $events->listen(
            'emailguarantor.create',
            'App\Repositories\Events\ApplicationEventsListener@onCreateEmailAccessGuarantorForm'
        );
        $events->listen(
            'application.assign_backend_crt', 'App\Repositories\Events\ApplicationEventsListener@onAssignBackendCRT'
        );
        $events->listen(
            'emailtemplate.save', 'App\Repositories\Events\ApplicationEventsListener@onEmailTemplateUpdate'
        );
        $events->listen(
            'smstemplate.save', 'App\Repositories\Events\ApplicationEventsListener@onSmsTemplateUpdate'
        );
        $events->listen(
            'case.letterincompleteness',
            'App\Repositories\Events\ApplicationEventsListener@onLetterIncompleteness'
        );
        $events->listen(
            'email_additional_docs.send', 'App\Repositories\Events\ApplicationEventsListener@onEmailAdditionalDocs'
        );
        $events->listen(
            'send.commitment_letter', 'App\Repositories\Events\ApplicationEventsListener@onSendCommitmentLetter'
        );
        $events->listen(
            'send.commitment_letter_cc', 'App\Repositories\Events\ApplicationEventsListener@onSendCommitmentLetterToCustomer'
        );
        $events->listen(
            'ssn.track', 'App\Repositories\Events\ApplicationEventsListener@onTrackSsn'
        );
        $events->listen(
            'send.commitee_information', 'App\Repositories\Events\ApplicationEventsListener@onCommiteeInformation'
        );
        $events->listen(
            'send.mail_to_co_supervisor', 'App\Repositories\Events\ApplicationEventsListener@onVoteAndShare'
        );
        $events->listen(

            'shared_application.send', 'App\Repositories\Events\ApplicationEventsListener@onEmailShareApplication'
        );

         $events->listen(
            'send.counter_offer', 'App\Repositories\Events\ApplicationEventsListener@onCounterOffer'
        );

        $events->listen(
            'send.decline_offer', 'App\Repositories\Events\ApplicationEventsListener@onDeclineOffer'
        );
        $events->listen(
            'send.offer_acceptance', 'App\Repositories\Events\ApplicationEventsListener@onAcceptOffer'
        );
        $events->listen(
            'send.mail_update_profile', 'App\Repositories\Events\ApplicationEventsListener@onProfileUpdate'
        );
        $events->listen(
            'send.mail_update_password', 'App\Repositories\Events\ApplicationEventsListener@onPasswordUpdate'
         );
        $events->listen(
        'appointment.add',
        'App\Repositories\Events\ApplicationEventsListener@onAppAppointmentActivity'
         );
        $events->listen(
            'appointment.update',
            'App\Repositories\Events\ApplicationEventsListener@onAppointmentActivityUpdate'
        );
        $events->listen(
            'appointment.update_status',
            'App\Repositories\Events\ApplicationEventsListener@onAppApptActivityStatus'
        );
        $events->listen(
            'case_status.modify',
            'App\Repositories\Events\ApplicationEventsListener@onModifyStatus'
        );
        $events->listen(
            'case_share_to_commitee',
            'App\Repositories\Events\ApplicationEventsListener@onCommiteeShare'
        );
        $events->listen(
            'commitment_letter_status.modify',
            'App\Repositories\Events\ApplicationEventsListener@onCommitmentLetterSent'
        );
        $events->listen(
            'update.commitment_letter_status',
            'App\Repositories\Events\ApplicationEventsListener@onCommitmentLetterReceived'
        );
        $events->listen(
            'document.added',
            'App\Repositories\Events\ApplicationEventsListener@onAppDocuments'
        );
        $events->listen(
            'sendsms.onshare_application', 'App\Repositories\Events\ApplicationEventsListener@onShareSendSMSToUser'
        );

        $events->listen(
            'sendsms.appcomplete.onhardpull', 'App\Repositories\Events\ApplicationEventsListener@onHardPullSMSToOwner'
        );
        $events->listen(
            'send.commitee_acceptance', 'App\Repositories\Events\ApplicationEventsListener@onCommiteeAcceptance'
        );

        $events->listen(
            'note.send_email_to_assigned_parties',
            'App\Repositories\Events\ApplicationEventsListener@onAddNoteEmail'
        );

        $events->listen(
            'send.update_status', 'App\Repositories\Events\ApplicationEventsListener@onSendAppStatusUpdate'
        );

        $events->listen(
            'sendmail.appcomplete.onhardpull', 'App\Repositories\Events\ApplicationEventsListener@onHardPullMailToOwner'
        );

        $events->listen(
            'application.systemstatus', 'App\Repositories\Events\ApplicationEventsListener@onSystemGeneratedAppStatus'
        );

        $events->listen(
            'application.application_remains_incomplete',
            'App\Repositories\Events\ApplicationEventsListener@onApplicationIncomplete'
        );
        $events->listen(
            'application.application_renewal',
            'App\Repositories\Events\ApplicationEventsListener@onApplicationRenewal'
        );
        $events->listen(
            'application.send_verify_sms_guarantor',
            'App\Repositories\Events\ApplicationEventsListener@sendVerificationSMSToGuarantor'
        );
        
        $events->listen(
            'reminder.send',
            'App\Repositories\Events\ApplicationEventsListener@onReminderMail'
        );
        
        $events->listen(
            'application.send_offer_to_customer',
            'App\Repositories\Events\ApplicationEventsListener@onSendOfferToCustomer'
        );
        
        $events->listen(
            'application.send_offer_to_rm',
            'App\Repositories\Events\ApplicationEventsListener@onSendOfferToRM'
        );
        
        $events->listen(
            'application.offer_accepted_by_customer',
            'App\Repositories\Events\ApplicationEventsListener@onOfferAcceptedByCustomer'
        );

        $events->listen(
            'application.offer_declined_by_customer',
            'App\Repositories\Events\ApplicationEventsListener@onOfferDeclinedByCustomer'
        );

        $events->listen(
            'application.on_application_declined',
            'App\Repositories\Events\ApplicationEventsListener@onApplicationDeclined'
        );
        
        $events->listen(
            'application.on_application_approve',
            'App\Repositories\Events\ApplicationEventsListener@onApplicationApproved'
        );
        
        $events->listen(
            'reminder.new_customer_send',
            'App\Repositories\Events\ApplicationEventsListener@onNewCustReminderMail'
        );
        
        $events->listen(
            'reminder.inprogress_send',
            'App\Repositories\Events\ApplicationEventsListener@onInProgressReminderMail'
        );
        
        $events->listen(
            'application.appsubmitemail',
            'App\Repositories\Events\ApplicationEventsListener@onAppSubmitMail'
        );
        
        $events->listen(
            'application.appfunded',
            'App\Repositories\Events\ApplicationEventsListener@onAppFundedMail'
        );
        
        $events->listen(
            'businessInfo.send',
            'App\Repositories\Events\ApplicationEventsListener@shootEmail'
        );
        
        $events->listen(
            'primary_owner_approval.send',
            'App\Repositories\Events\ApplicationEventsListener@onBackendShareApproval'
        );
        
        $events->listen(
            'electronic_email_docupload.sendemailfordocupload_send',
            'App\Repositories\Events\ApplicationEventsListener@onElectronicDocApproval'
        );
        
        $events->listen(
            'doc_confirm_brm.send',
            'App\Repositories\Events\ApplicationEventsListener@onElectronicDocSubmit'
        );
        $events->listen(
            'coOwner.send',
            'App\Repositories\Events\ApplicationEventsListener@onShareApprovalForBackend'
        );
        $events->listen(
            'consent_approval.send',
            'App\Repositories\Events\ApplicationEventsListener@onConsentApproval'
        );
        $events->listen(
            'application_canceled.sendEmail',
            'App\Repositories\Events\ApplicationEventsListener@mailAfterCanceledApplication'
        );
    }
}
