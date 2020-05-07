<?php

namespace App\Http\Controllers\Contracts\Traits;

use Auth;
use Event;
use Crypt;
use Helpers;
use Session;
use Carbon\Carbon;

trait ApplicationTraits {
    
    /**
     * Saving Business Information
     *
     * @param repository $application
     * @param request $request
     * @param integer $user_id
     * @param integer $app_id
     */
    public function saveBusinessBasicInfo($userRepo, $application, $request, $user_id, $app_id = null, $esc_biz_id = null) {
        
        // insert business information data
        $log_b2c_app['app_user_id'] = $user_id;
        $log_b2c_app['app_id'] = $app_id;        
        $log_b2c_app['biz_name'] = $request->get('biz_name');
        $log_b2c_app['email'] = $request->get('biz_email');
        //$log_b2c_app['postal_code'] = $request->get('postal_code');
        //$log_b2c_app['city_name'] = $request->get('city_name');
        $log_b2c_app['postal_code'] = $request->get('postal_code');
        $log_b2c_app['city_name'] = $request->get('city_name');
        $log_b2c_app['state_name'] = $request->get('state_name');
        $log_b2c_app['state_key'] = $request->get('state_key');
        if(empty($request->get('manual_consent'))){
        $log_b2c_app['city_id'] = $request->get('city_id');
        $log_b2c_app['state_id'] = $request->get('state_id');
        }
        $log_b2c_app['biz_phone'] = ($request->get('biz_phone') !== "") ? str_replace('-', "", $request->request->get('biz_phone')) : "";        
        if(app()->getLocale() == config('b2c_common.FRENCH_LOCALE'))
            $log_b2c_app['date_established'] = !empty($request->get('date_established')) ? Helpers::getDateTimeInClientTz($request->get('date_established'), 'd-m-Y', 'Y-m-d') : null;
        else
            $log_b2c_app['date_established'] = !empty($request->get('date_established')) ? Helpers::getDateTimeInClientTz($request->get('date_established'), 'm-d-Y', 'Y-m-d') : null;
        $log_b2c_app['is_active'] = config('b2c_common.ACTIVE');
        $log_b2c_app['res_data_id'] = $esc_biz_id;
        $log_b2c_app['biz_number'] = ($request->get('biz_number') !== "") ? str_replace('-', "", $request->request->get('biz_number')) : ""; 
        $log_b2c_app['token_status'] = 1; 
//        if(!empty($log_b2c_app['email'])){
//        $userRepo->update(['email'=>$log_b2c_app['email']] , $user_id);}
        return $application->saveBusinessBasicInfo($log_b2c_app, $user_id, $app_id);
        
    }
    
    /**
     * Saving Owner Information with this method
     *
     * @param repository $application
     * @param request $request
     * @param integer $user_id
     * @param integer $app_id
     */
    public function updateOwnerInfo($userRepo, $application, $request, $user_id, $app_id = null, $owner_id = false)
    {      
        $log_b2c_app_owner['own_phone_no'] = "";
        $log_b2c_app_owner['email'] = $request->get('email');
        $log_b2c_app_owner['is_approval_requested'] = config('b2c_common.APPROVAL_REQUESTED');
        //$log_b2c_app_owner['is_manual_consent'] = null;
        return $application->saveOwnerInfo($log_b2c_app_owner, $owner_id);
    }
    
    /**
     * Saving Owner Information with this method for primary user
     *
     * @param repository $application
     * @param request $request
     * @param integer $user_id
     * @param integer $app_id
     */
    public function updatePrimaryOwnerInfo($userRepo, $application, $request, $user_id, $app_id = null)
    {      
        //$log_b2c_app_owner['email'] = $request->get('email');
        $log_b2c_app_owner['is_manual_consent'] = null;
        $log_b2c_app_owner['is_approval_requested'] = config('b2c_common.APPROVAL_REQUESTED');
        return $userRepo->savePrimaryOwnerInfo($user_id , $log_b2c_app_owner );
    }
    
    /**
     * Saving Owner Information with this method
     *
     * @param repository $application
     * @param request $request
     * @param integer $user_id
     * @param integer $app_id
     */
    public function updateIndividualOwnerInfo($userRepo, $application, $request, $user_id, $app_id = null, $owner_id = false)
    {  
        $is_primary_owner = (int) $request->get('is_primary_owner');
        $appData = $application->find($app_id);
        $log_b2c_app_owner['is_manual_consent'] = 1;
        $log_b2c_app_owner['addr_line_one'] = !empty($request->get('addr_line_one')) ? $request->get('addr_line_one') : null;
        $log_b2c_app_owner['addr_line_two'] = !empty($request->get('addr_line_two')) ? $request->get('addr_line_two') : null;
        $log_b2c_app_owner['zip_code'] = !empty($request->get('postal_code')) ? $request->get('postal_code') : null;
        $log_b2c_app_owner['state_id'] =  null;
        $log_b2c_app_owner['city_id'] =  null;
        $log_b2c_app_owner['state_name'] = !empty($request->get('state_name')) ? $request->get('state_name') : null;
        $log_b2c_app_owner['city_name'] = !empty($request->get('city_name')) ? $request->get('city_name') : null;
        $log_b2c_app_owner['apartment_no'] = !empty($request->get('apartment_no')) ? $request->get('apartment_no') : null;
        $log_b2c_app_owner['street_name'] = !empty($request->get('street_name')) ? $request->get('street_name') : null ;
        $log_b2c_app_owner['is_credit_bureau_skip'] = !empty($request->request->get('skip_bureau')) ? $request->request->get('skip_bureau') : null;
        $is_auto_bureau_skip = !empty($request->request->get('is_auto_bureau_skip')) ? $request->request->get('is_auto_bureau_skip') : null;
        $log_b2c_app_owner['is_email_consent_knocked_out'] = null;
        //check manual skip or auto skip
        if($log_b2c_app_owner['is_credit_bureau_skip'] == config('b2c_common.YES')) {
            if($is_auto_bureau_skip == config('b2c_common.YES')) {
                $log_b2c_app_owner['is_credit_bureau_skip'] = config('b2c_common.AUTO_SKIP');
            } else {
                $log_b2c_app_owner['is_credit_bureau_skip'] = config('b2c_common.MANUAL_SKIP');
            }
        }
        if($is_primary_owner){
             if(app()->getLocale() == config('b2c_common.FRENCH_LOCALE'))
                $log_b2c_app_owner['dob'] = ($request->get('dob')!=null) ? Helpers::getDateTimeInClientTz($request->get('dob'), 'd-m-Y', 'Y-m-d') : null;
            else
                $log_b2c_app_owner['dob'] = ($request->get('dob')!=null) ? Helpers::getDateTimeInClientTz($request->get('dob'), 'm-d-Y', 'Y-m-d') : null;
            $log_b2c_app_owner['own_phone_no'] = ($request->get('own_phone_no') !== "") ? str_replace('-', "", $request->request->get('own_phone_no')) : "";                
            $log_b2c_app_owner['email'] = $request->get('email');
            $log_b2c_app_owner['own_percentage'] = $request->get('own_percentage');
            $log_b2c_app_owner['is_guarantor'] = 1;
            $log_b2c_app_owner['status'] = 1;
        } else {
            $log_b2c_app_owner['first_name'] = $request->get('first_name');
            $log_b2c_app_owner['last_name'] = $request->get('last_name');
            $log_b2c_app_owner['role'] = $request->get('role');
             if(app()->getLocale() == config('b2c_common.FRENCH_LOCALE'))
                $log_b2c_app_owner['dob'] = ($request->get('dob')!=null) ? Helpers::getDateTimeInClientTz($request->get('dob'), 'd-m-Y', 'Y-m-d') : null;
            else
                $log_b2c_app_owner['dob'] = ($request->get('dob')!=null) ? Helpers::getDateTimeInClientTz($request->get('dob'), 'm-d-Y', 'Y-m-d') : null;
            $log_b2c_app_owner['own_phone_no'] = ($request->get('own_phone_no') !== "") ? str_replace('-', "", $request->request->get('own_phone_no')) : "";                
            $log_b2c_app_owner['email'] = $request->get('email');
            $log_b2c_app_owner['own_percentage'] = $request->get('own_percentage');
        }
            $log_b2c_app_owner['ip_address'] = $request->getClientIp();
            // expired the email link
            $accessFormData = $application->getLatestEmailAccessForm($owner_id,$linkType=null);
            if($accessFormData)
            {
                $expire_data['status'] = 2;
                $expire_data['completed_at'] = Helpers::getCurrentDateTime();    
                $application->UpdateByOwnerId( $owner_id , $expire_data);
            }
            if(\Auth::user()->user_level_id == config('b2c_common.RM_USERLEVEL')) {
                $log_b2c_app_owner['activity_by'] = 2;
            }
            return $application->saveOwnerInfo($log_b2c_app_owner, $owner_id);
    }
    
    /**
     * Saving Owner Information with this method
     *
     * @param repository $application
     * @param request $request
     * @param integer $user_id
     * @param integer $app_id
     */
    public function saveOwnerInfo($userRepo, $application, $request, $user_id, $app_id = null,$biz_id, $owner_id = false)
    {               
        // insert owner information data
        $log_b2c_app_owner['app_user_id'] = $user_id;
        $log_b2c_app_owner['app_biz_id'] = $biz_id;
        $log_b2c_app_owner['app_id'] = $app_id;
        $log_b2c_app_owner['first_name'] = $request->get('first_name');
        $log_b2c_app_owner['last_name'] = $request->get('last_name');
        $log_b2c_app_owner['apartment_no'] = !empty($request->get('apartment_no')) ? $request->get('apartment_no') : null;
        $log_b2c_app_owner['street_name'] = $request->get('street_name');
        $log_b2c_app_owner['role'] = $request->get('role');
        $log_b2c_app_owner['dob'] = ($request->get('dob')!=null) ? Helpers::getDateTimeInClientTz($request->get('dob'), 'm-d-Y', 'Y-m-d') : null;
        $log_b2c_app_owner['addr_line_one'] = $request->get('addr_line_one');
        $log_b2c_app_owner['addr_line_two'] = $request->get('addr_line_two');
        $log_b2c_app_owner['email'] = $request->get('email');
        $log_b2c_app_owner['own_percentage'] = $request->get('own_percentage');
        $log_b2c_app_owner['zip_code'] = $request->get('postal_code');
        $log_b2c_app_owner['state_id'] = $request->get('state_id');
        $log_b2c_app_owner['city_id'] = $request->get('city_id');
        $log_b2c_app_owner['own_phone_no'] = ($request->get('own_phone_no') !== "") ? str_replace('-', "", $request->request->get('own_phone_no')) : "";
        $log_b2c_app_owner['is_active'] = config('b2c_common.ACTIVE');
        $log_b2c_app_owner['is_guarantor'] = 0;
        $log_b2c_app_owner['status'] = 0;
        $log_b2c_app_owner['ip_address'] = $request->getClientIp();
        $log_b2c_app_owner['is_credit_bureau_skip'] = !empty($request->get('skip_bureau')) ? $request->get('skip_bureau') : null;
        $is_auto_bureau_skip = !empty($request->request->get('is_auto_bureau_skip')) ? $request->request->get('is_auto_bureau_skip') : null;
        //check manual skip or auto skip
        if($log_b2c_app_owner['is_credit_bureau_skip'] == config('b2c_common.YES')) {
            if($is_auto_bureau_skip == config('b2c_common.YES')) {
                $log_b2c_app_owner['is_credit_bureau_skip'] = config('b2c_common.AUTO_SKIP');
            } else {
                $log_b2c_app_owner['is_credit_bureau_skip'] = config('b2c_common.MANUAL_SKIP');
            }
        }
        $log_b2c_app_owner['activity_by'] = (\Auth::user()->user_level_id == config('b2c_common.RM_USERLEVEL')) ? 2 : 1;
        return $application->saveOwnerInfo($log_b2c_app_owner);
        
    }
    
    /**
     * 
     * @param repository $userRepo
     * @param repository $application
     * @param request $request
     * @param integer $user_id
     * @param integer $app_id
     * @param integer $biz_id
     * @param integer $owner_id
     * 
     * @return boolean
     */
    public function savePrimaryOwnerInfo($userRepo, $application, $request, $user_id, $app_id, $biz_id, $owner_id = false)
    {
        // insert owner information data
        $log_b2c_app_owner['app_user_id'] = $user_id;
        $log_b2c_app_owner['app_biz_id'] = $biz_id;
        $log_b2c_app_owner['app_id'] = $app_id;
        $log_b2c_app_owner['first_name'] = $request->get('first_name');
        $log_b2c_app_owner['last_name'] = $request->get('last_name');
        $log_b2c_app_owner['apartment_no'] = $request->get('apartment_no');
        $log_b2c_app_owner['street_name'] = $request->get('street_name');
        $log_b2c_app_owner['role'] = $request->get('role');
        $log_b2c_app_owner['sin'] = $request->get('sin');
        if(app()->getLocale() == config('b2c_common.FRENCH_LOCALE'))
            $log_b2c_app_owner['dob'] = ($request->get('dob')!=null) ? Helpers::getDateTimeInClientTz($request->get('dob'), 'd-m-Y', 'Y-m-d') : null;
        else
           $log_b2c_app_owner['dob'] = ($request->get('dob')!=null) ? Helpers::getDateTimeInClientTz($request->get('dob'), 'm-d-Y', 'Y-m-d') : null;
        $log_b2c_app_owner['addr_line_one'] = $request->get('addr_line_one');
        $log_b2c_app_owner['addr_line_two'] = $request->get('addr_line_two');
        $log_b2c_app_owner['email'] = $request->get('email');
        $log_b2c_app_owner['own_percentage'] = $request->get('own_percentage');
        $log_b2c_app_owner['zip_code'] = $request->get('postal_code');
        // $log_b2c_app_owner['state_id'] = $request->get('state_id');
       // $log_b2c_app_owner['city_id'] = $request->get('city_id');
        $log_b2c_app_owner['state_name'] = $request->get('state_name');
        $log_b2c_app_owner['state_key'] = $request->get('state_key');
        $log_b2c_app_owner['city_name'] = $request->get('city_name');
        $log_b2c_app_owner['own_phone_no'] = ($request->get('own_phone_no') !== "") ? str_replace('-', "", $request->request->get('own_phone_no')) : "";
        $log_b2c_app_owner['is_active'] = config('b2c_common.ACTIVE');
        $log_b2c_app_owner['is_guarantor'] = 1;
        $log_b2c_app_owner['is_terms_checked'] = !empty($request->request->get('agree_check')) ? $request->request->get('agree_check') : null;
        $log_b2c_app_owner['status'] = 0;
        $log_b2c_app_owner['is_email_consent_knocked_out'] = null;
        $log_b2c_app_owner['ip_address'] = $request->getClientIp();
        $log_b2c_app_owner['is_credit_bureau_skip'] = !empty($request->request->get('skip_bureau')) ? $request->request->get('skip_bureau') : null;
        $auto_bureau_skip = !empty($request->request->get('is_auto_bureau_skip')) ? $request->request->get('is_auto_bureau_skip') : null;
        $agree_check = !empty($request->request->get('termcheck')) ? $request->request->get('termcheck') : null;
        if($agree_check==1){
               $log_b2c_app_owner['is_approval_requested'] = config('b2c_common.APPROVAL_ACCECTED');
            }
        //check manual skip or auto skip
        if($log_b2c_app_owner['is_credit_bureau_skip'] == config('b2c_common.YES')) {
            $log_b2c_app_owner['prev_equifax_res_id'] = !empty(\Session::get('prev_equifax_res_id') ) ? \Session::pull('prev_equifax_res_id') : null;
            \session()->forget('prev_equifax_res_id');
            if($auto_bureau_skip == config('b2c_common.YES')) {
                $log_b2c_app_owner['is_credit_bureau_skip'] = config('b2c_common.AUTO_SKIP');
            } else {
                $log_b2c_app_owner['is_credit_bureau_skip'] = config('b2c_common.MANUAL_SKIP');
            }
        }
        return $application->saveOwnerInfo($log_b2c_app_owner, $owner_id);
    }
   
    
    /**
     * Saving Owner Information with this method
     *
     * @param repository $application
     * @param request $request
     * @param integer $user_id
     * @param integer $app_id
     */
    public function saveOwnerInfoPrevious($userRepo, $application, $request, $user_id, $app_id = null, $owner_id = false)
    {               
        // insert owner information data
        $log_b2c_app_owner['app_user_id'] = $user_id;
        $log_b2c_app_owner['app_biz_id'] = $user_id;
        $log_b2c_app_owner['app_id'] = $app_id;
        $log_b2c_app_owner['first_name'] = $request->get('first_name');
        $log_b2c_app_owner['last_name'] = $request->get('last_name');
        $log_b2c_app_owner['role'] = $request->get('role');
        $log_b2c_app_owner['dob'] = ($request->get('dob')!=null) ? Helpers::getDateTimeInClientTz($request->get('dob'), 'm-d-Y', 'Y-m-d') : null;
        $log_b2c_app_owner['addr_line_one'] = $request->get('addr_line_one');
        $log_b2c_app_owner['addr_line_two'] = $request->get('addr_line_two');
        $log_b2c_app_owner['email'] = $request->get('email');
        $log_b2c_app_owner['own_percentage'] = $request->get('own_percentage');
        $log_b2c_app_owner['zip_code'] = $request->get('postal_code');
        $log_b2c_app_owner['state_id'] = $request->get('state_id');
        $log_b2c_app_owner['city_id'] = $request->get('city_id');
        $log_b2c_app_owner['own_phone_no'] = ($request->get('own_phone_no') !== "") ? str_replace('-', "", $request->request->get('own_phone_no')) : "";
        $log_b2c_app_owner['is_active'] = config('b2c_common.ACTIVE');
        $log_b2c_app_owner['is_guarantor'] = 1;
        $log_b2c_app_owner['status'] = 0;
        $log_b2c_app_owner['sin'] = ($request->get('sin') !== "") ? str_replace('-', "", $request->request->get('sin')) : null;
        return $application->saveOwnerInfo($log_b2c_app_owner,$app_id);
        
    }
    
    /**
     * Create and send email for email type guarantor access form
     *
     * @param array $current_data
     * @param int $app_id
     * @param int $guarantor_id
     * @return int
     */
    protected function createEmailAccessLink($current_data, $user_id, $app_id, $owner_id, $userRepo, $appRepo)
    {   
        $accessFormData = $appRepo->getLatestEmailAccessForm($owner_id,$app_id);
        if (isset($accessFormData)) {
            $expire_data['status'] = 2;
            $appRepo->UpdateEmailAccessForm($expire_data, $accessFormData['id']);
            $this->sendEmailLink($current_data, $user_id, $app_id, $owner_id, $userRepo, $appRepo);
        } else {
            $this->sendEmailLink($current_data, $user_id, $app_id, $owner_id, $userRepo, $appRepo);
        }
        
    }
    
    /**
     * Create and send email for primary user
     *
     * @param array $current_data
     * @param int $app_id
     * @param int $guarantor_id
     * @return int
     */
    protected function createEmailAccessforPrimaryLink($current_data, $user_id, $app_id, $userRepo, $appRepo, $linkType)
    {   
        $accessFormData = $appRepo->getLatestEmailAccessbyuserID($user_id,$linkType);
        if (isset($accessFormData)) {
            $expire_data['status'] = 2;
            $appRepo->UpdateEmailAccessForm($expire_data, $accessFormData['id']);
            $this->sendEmailLinkForPrimaryUser($current_data, $user_id, $app_id, $userRepo, $appRepo,$linkType);
        } else {
            $this->sendEmailLinkForPrimaryUser($current_data, $user_id, $app_id, $userRepo, $appRepo,$linkType);
        }
        
    }
    
    /**
     * Create and send email for primary user
     *
     * @param array $current_data
     * @param int $app_id
     * @param int $guarantor_id
     * @return int
     */
    protected function createEmailAccessforDocUpload($current_data, $user_id, $app_id, $userRepo, $appRepo , $linkType)
    {   
        $accessFormData = $appRepo->getLatestEmailAccessbyuserID($user_id,$linkType);
        if (isset($accessFormData)) {
            $expire_data['status'] = 2;
            $expire_data['consent_type'] = $linkType;
            $appRepo->UpdateEmailAccessForm($expire_data, $accessFormData['id'],$linkType);
            $this->sendEmailLinkForDocUpload($current_data, $user_id, $app_id, $userRepo, $appRepo,$linkType);
        } else {
            $this->sendEmailLinkForDocUpload($current_data, $user_id, $app_id, $userRepo, $appRepo,$linkType);
        }
        
    }
    
     /**
     * 
     * @param type $current_data
     * @param type $user__id
     * @param type $app_id
     * @param type $owner_id
     * @param type $userRepo
     * @param type $appRepo
     */
    protected function sendEmailLinkForDocUpload($current_data, $user_id, $app_id, $userRepo, $appRepo,$linkType)
    {
        $emailFormrequest = [];
        $emailFormrequest['app_user_id'] = $user_id;
        $emailFormrequest['app_owner_id'] = null;
        $emailFormrequest['app_id'] = $app_id;
        $emailFormrequest['status'] = 0;
        $emailFormrequest['consent_type'] = $linkType;
        $form_access_id = $this->application->createEmailAccessForm($emailFormrequest);
        $param = ['app_id' => $app_id,
            'access_id' => $form_access_id,
            'app_user_id' => $user_id,
            'ln' => !empty( \Session::get('locale') ) ?\Session::get('locale') :'en',
        ];
        $emailFormrequestUpdate['access_link'] = $this->getAccessLink($param,"customer_doc_upload");
        // create a new access link
        $appArray = $appRepo->find((int) $app_id);
        $current_data['access_link'] = $emailFormrequestUpdate['access_link'];
        $current_data['app_id'] = $app_id;
        $current_data['user_id'] = $user_id;
        $current_data['is_display_in_activity'] = config('b2c_common.IS_DISPLAY_ACTIVITY');
        // update access link to database
        $updated = $this->application->updateEmailAccessForm($emailFormrequestUpdate, $form_access_id);
        if ($updated && !empty($current_data['owner_detail']['cust_email'])) {
            Event::fire(
                "electronic_email_docupload.sendemailfordocupload_send", serialize($current_data)
            );
        }
        return $updated;
    }
    
    /**
     * 
     * @param type $current_data
     * @param type $user__id
     * @param type $app_id
     * @param type $owner_id
     * @param type $userRepo
     * @param type $appRepo
     */
    protected function sendEmailLinkForPrimaryUser($current_data, $user_id, $app_id, $userRepo, $appRepo,$linkType)
    {
        $emailFormrequest = [];
        $emailFormrequest['app_user_id'] = $user_id;
        $emailFormrequest['app_owner_id'] = null;
        $emailFormrequest['app_id'] = $app_id;
        $emailFormrequest['status'] = 0;
        $emailFormrequest['consent_type'] = $linkType;
        $emailFormrequest['esc_id'] =  isset($current_data['esc_biz_id']) ? (int) $current_data['esc_biz_id'] : "";
        $form_access_id = $this->application->createEmailAccessForm($emailFormrequest);
        $param = ['app_id' => $app_id,
            'user_id' => $user_id,
            'access_id' => $form_access_id,
            'ln' => !empty( \Session::get('locale') ) ?\Session::get('locale') :'en',
        ];
        
        // create a new access link
        $emailFormrequestUpdate['access_link'] = $this->getAccessLink($param,"business_email_link");
        $appArray = $appRepo->find((int) $app_id);
        $current_data['access_link'] = $emailFormrequestUpdate['access_link'];
        $current_data['app_id'] = $app_id;
        $current_data['user_id'] = $user_id;
        $current_data['is_display_in_activity'] = config('b2c_common.IS_DISPLAY_ACTIVITY');
        // update access link to database
        $updated = $this->application->updateEmailAccessForm($emailFormrequestUpdate, $form_access_id);
        if ($updated) {
            Event::fire(
                "primary_owner_approval.send", serialize($current_data)
            );
        }
        return $updated;
    }
    
    
    /**
     * 
     * @param type $current_data
     * @param type $user__id
     * @param type $app_id
     * @param type $owner_id
     * @param type $userRepo
     * @param type $appRepo
     */
    protected function sendEmailLink($current_data, $user_id, $app_id, $owner_id, $userRepo, $appRepo)
    {
        $emailFormrequest = [];
        $emailFormrequest['app_owner_id'] = $owner_id;
        $emailFormrequest['app_id'] = $app_id;
        $emailFormrequest['status'] = 0;
        $is_backend_app = null;
        $user_level_id = isset(Auth::user()->user_level_id) ? Auth::user()->user_level_id : null;
        if(isset($user_level_id) && $user_level_id == 7 || $user_level_id == 3) {
            $is_backend_app = 1;
        }
        $form_access_id = $this->application->createEmailAccessForm($emailFormrequest);
        $param = ['app_id' => $app_id,
            'owner_id' => $owner_id,
            'access_id' => $form_access_id,
            'ln' => !empty( \Session::get('locale') ) ?\Session::get('locale') :'en',
            'is_backend_app' => $is_backend_app,
        ];
            // create a new access link
        $emailFormrequestUpdate['access_link'] = $this->getAccessLink($param);
        $emailFormrequestUpdate['owner_message'] = isset($current_data['message']) ? $current_data['message'] :null;
        //dd($emailFormrequestUpdate);
        $appArray = $appRepo->find((int) $app_id);
        $current_data['access_link'] = $emailFormrequestUpdate['access_link']; 
        dd($current_data);
        $current_data['owner_id'] = $owner_id;
        $current_data['app_id'] = $app_id;
        $current_data['user_id'] = $user_id;
        $current_data['is_display_in_activity'] = config('b2c_common.IS_DISPLAY_ACTIVITY');
        $current_data['app_locale'] = !empty( \Session::get('locale') ) ?\Session::get('locale') :'en';
        // update access link to database
        $updated = $this->application->updateEmailAccessForm($emailFormrequestUpdate, $form_access_id);
        
        if (!empty($current_data['email']) && $is_backend_app == 1) {
            Event::fire(
                "coOwner.send", serialize($current_data)
            );
        }
        else
        {
              Event::fire(
                "co_owner_approval.send", serialize($current_data)
            );   
        }
        return $updated;
    }


    /**
     * Create guarantor request for sent by email.
     *
     * @param array $request
     * @param integer $key
     * @param integer $app_id
     * @return array
     */
    protected function createGuarantorEmailInfoRequest($request, $key, $app_id) 
    {        
        $arr = [
            'app_id' => (int) $app_id,
            'guarantor_type' => isset($request['guarantor_type'][$key]) ? $request['guarantor_type'][$key] : 2,
            'org_type' => isset($request['add_org_type'][$key]) ? $request['add_org_type'][$key] : null,
            'ownship_percent' => isset($request['add_ownship_percent'][$key]) ? $request['add_ownship_percent'][$key] : null,
            'first_name' => isset($request['add_first_name'][$key]) ? $request['add_first_name'][$key] : null,
            'email' => isset($request['add_email'][$key]) ? $request['add_email'][$key] : null,
            'guarantor_type' => 2,
            'email_sent' => 0,
            'updated_by' => null
        ];
        if ($arr['ownship_percent'] == '') {
            $arr['ownship_percent'] = null;
        }
        return $arr;
    }
    
    /**
     * Create encrypted access link to Guarantor Form
     *
     * @param type $param
     * @return type
     */
    protected function getAccessLink($param,$routeName=null) {
        $route = route('email_access_form');
        if(!empty($routeName)){
        $route = route($routeName);
        }
        $encrypted_param = Crypt::encrypt($param);
        return $route . '?_token=' . $encrypted_param;
    }
    
    /**
     * Save individual email type guarantor
     *
     * @param Object $appRepo
     * @param Request $request
     * @return type
     */
    public function saveEmailIndividual($appRepo, $request, $userRepo)
    {
        $statusArray = [];
        $decline = config('b2c_common.RULE_DEFAULT_VALUE'); 
        $user_id = (int) $request->request->get('user_id');
        $app_id = (int) $request->request->get('app_id');        
        $appArray = $appRepo->find((int) $app_id);
        $userArray = $userRepo->find((int) $appArray->app_user_id);
        $owner_id = (int) $request->request->get('owner_id');
        $owner = $appRepo->getOwnerDetailById($owner_id); 
        $access_id = (int) $request->request->get('access_id');
        $action =  !empty($request->request->get('action')) ? $request->request->get('action') : null;
        $form_access_details = $appRepo->getEmailAccessFormById($access_id);
        $currentTime = Helpers::getCurrentDateTime();
        $timeDiffInHours = Carbon::parse($currentTime)->diffInHours(Carbon::parse($form_access_details->created_at));
        $appData = $this->application->find($app_id);
        $is_backend_app = !empty($request->request->get('is_backend_app')) ? $request->request->get('is_backend_app') : null;
        if ($appData->current_status == config('b2c_common.KNOCKED_OUT')) {
            return false;
        }
        if (
                empty($form_access_details) ||
                !isset($form_access_details['status']) ||
                $form_access_details['status'] > 0 ||
                $timeDiffInHours > config('b2c_common.ADDITIONAL_OWNER_LINK_EXPIRE_HOURS')
        ) {            
            return false;
        }
        $requestVar = $request->request->all();
        
        $current_data=['guarantor_email'=>$owner->email ,'email'=>$userArray->email, 'first_name' => $userArray->first_name, 'user_id'=>$appArray->user_id, 'app_id'=>$app_id, 'is_display_in_activity'=> config('b2c_common.IS_DISPLAY_ACTIVITY'), 'action'=>$action];
        $current_data['additional_owner'] = (isset($requestVar['first_name'])) ? $requestVar['first_name'].' '.$requestVar['last_name'] : null;
        $current_data['app_locale'] = app()->getLocale();
        $additional_owner_id = isset($requestVar['owner_id']) ? $requestVar['owner_id'] : null;
        $sin = !empty($request->request->get('sin')) ? str_replace('-', "", $request->request->get('sin')) : null;
        $arrIndividualGuarantor['is_manual_consent'] = null;
        
        if(isset($action) && $action != config('b2c_common.DECLINE_ACTION')) {
            // save form email type guarantor
            
            $arrIndividualGuarantor = $this->createOwnerIndividualRequest($requestVar, $app_id, $additional_owner_id);
            $arrIndividualGuarantor['ip_address'] = $request->getClientIp();
            $this->encryptAndSave($sin, $this->application, $owner_id);
            
            $appDetail = $this->application->getAppDetail(['app_id' => (int) $app_id], '*');
            $userData = $this->userRepo->getUserDetail((int) $appDetail['current_rm']);
            
            $userData['customer_name']      =  (isset($requestVar['first_name'])) ? $requestVar['first_name'].' '.$requestVar['last_name'] : null;
            $userData['app_id']             = $appDetail['app_id'];
            $user_level_id = isset(Auth::user()->user_level_id) ? Auth::user()->user_level_id : null;
            if(!empty($userData))
            {
            $locale = app()->getLocale();
             if($locale != 'fr') {
                Event::fire("businessInfo.send", serialize($userData));
             }
            }
        } else {
            $decline = 1;
            //update declined owner flag in application
            if($is_backend_app != 1) {
                $arrAppData['current_status'] = config('b2c_common.KNOCKED_OUT');
                $arrAppData['is_declined_by_partner'] = config('b2c_common.YES');
                $arrAppData['updated_by']    = isset($additional_owner_id) ? $additional_owner_id : null;
                $this->application->updateApplication($app_id, $arrAppData);

                //update status log
                $appStatusLogArr                  = [];
                $appStatusLogArr['status_id']     = config('b2c_common.KNOCKED_OUT');
                $appStatusLogArr['app_id']        = $app_id;
                $appStatusLogArr['app_user_id']   = $user_id;
                $appStatusLogArr['created_by']    = isset($additional_owner_id) ? $additional_owner_id : null;
                $appStatusLogArr['updated_by']    = isset($additional_owner_id) ? $additional_owner_id : null;
                $appStatusLogArr['is_created_by'] = 1;
                $appStatusLogArr['created_at']    = Helpers::getCurrentDateTime();
                $appStatusLogArr['updated_at']    = Helpers::getCurrentDateTime();
                Helpers::saveApplicationStatus($appStatusLogArr);
                
                $knockout_id = null;
                $this->application->saveKnockoutReference($knockout_id, ['app_id' => $app_id, 'app_user_id' => $user_id, 'knockout_reference' => config('b2c_common.DECLINE_OWNER_REF'), 'knockout_desc' => config('b2c_common.DECLINE_OWNER_DESCRIPTION')]);
            }
            
            
            $arrIndividualGuarantor['status'] = 1;
            $arrIndividualGuarantor['is_terms_checked'] = 1;
            $arrIndividualGuarantor['updated_by'] = isset($additional_owner_id) ? $additional_owner_id : null;
            $arrIndividualGuarantor['ip_address'] = $request->getClientIp();
            $arrIndividualGuarantor['is_approval_requested'] = config('b2c_common.APPROVAL_DECLINED');
        }
        $arrIndividualGuarantor['activity_by'] = 1;
        // save form email type guarantor
        $createIndividual = $this->application->saveGuarantorInfo($arrIndividualGuarantor, $owner_id);
        if(isset($action) && $action != config('b2c_common.DECLINE_ACTION')) {
            $ownerData = $this->application->getAllConditionalOwnerData(['app_user_id'=>$user_id, 'app_id'=>$app_id, 'is_guarantor'=>0, 'is_approval_requested'=>config('b2c_common.APPROVAL_REQUESTED')])->toArray();
            if(empty($ownerData)) {
                //update Application status
                Helpers::updateAppStatus(['app_user_id' => (int) $user_id, 'app_id' => (int) $app_id, 'additional_owner_id' => $additional_owner_id], ['current_status' => config('b2c_common.APP_CUR_OWNER_APPROVAL_RECIEVED')]);
            }
        }
        
        if($is_backend_app != 1 && $decline!=1 ) {
            $skipWarning = $request->get('skipWarning');
            $skipWarning = isset($skipWarning) ? $skipWarning : null;
            //rule engine api call
            $result = $this->ruleEngineDataPrepare(['sin' => $sin[0], 'decline' => $decline]);
            $result = json_decode($result['status']);
            if(isset($result->decision) && $result->decision == 'Decline') {
                $knockout_code = trans('/messages.terms_knockout_code');
                    if($skipWarning == 1){
                        //if(isset($result['title']) && $result['title'] == 'SIN starting with 9') {
                        $knockout_code = isset($result->code) ? $result->code : null;
                        $knockout_desc = isset($result->text) ? $result->text : null;
                        $this->saveKnockoutRefCode(['app_user_id' => $user_id, 'app_id' => $app_id, 'knockout_code' => $knockout_code, 'knockout_desc' => $knockout_desc]);
                        // Update status by 1 token expired or completed time
                        $StausData['status'] = 1;
                        $StausData['completed_at'] = Helpers::getCurrentDateTime();        
                        $this->application->UpdateEmailAccessForm($StausData, $access_id);
                        return ['status'=>true];
                    }
                    return ['status'=>true,'show_warning_modal'=>1,'knockout_code'=>$knockout_code];
            }
        }
        
        // Update status by 1 token expired or completed time
        $StausData['status'] = 1;
        $StausData['completed_at'] = Helpers::getCurrentDateTime();        
        $this->application->UpdateEmailAccessForm($StausData, $access_id);
        if($is_backend_app != 1){
            Event::fire( "emailguarantor_owner.create", serialize($current_data));
        }

        if($is_backend_app == 1) {
            //assisted rule engine api call
            $resultData = $this->assistedRuleEngineDataPrepare(['sin' => $sin[0]]);
            if ($resultData['status']) {
                $result = json_decode($resultData['status']);
                if(isset($result->decision) && $result->decision == 'Decline') {
                    $knockout_code = isset($result->code) ? $result->code : null;
                    $knockout_desc = isset($result->text) ? $result->text : null;
                    $knockout_reason = isset($result->reason) ? $result->reason : null;
                    $knockoutData['app_id']= $app_id;
                    $knockoutData['is_created_from'] = 1;
                    $knockoutData['app_user_id'] = $user_id;
                    $knockoutData['knockout_desc'] = $knockout_reason;
                    $knockoutData['knockout_reference'] = $knockout_code;
                    $knockoutData['route_name'] = 'email_access_form';
                    $this->saveBackendKnockoutLog($knockoutData);
                    $this->application->saveGuarantorInfo(['is_email_consent_knocked_out' => 1], $owner_id);
                    return ['status'=>true];
                }
            }
        }
        // redirect to thank you page
        return ['status'=>true];
    }
    
    /**
     * Create Owner Request
     * 
     * @param array $request
     * @param integer $app_id
     * @return array
     */
    protected function createOwnerIndividualRequest($request, $app_id, $additional_owner_id)
    {
        return [            
            'dob' => (isset($request['dob']) && !empty($request['dob'])) ? Helpers::getDateTimeInClientTz($request['dob'], 'm-d-Y', 'Y-m-d') : null,
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'addr_line_one' => $request['addr_line_one'],
            'addr_line_two' => $request['addr_line_two'],
            'role' => ($request['role'] !== "") ? $request['role'] : null,
            //'city_id' => ($request['city_id'] !== "") ? $request['city_id'] : null,
            //'state_id' => ($request['state_id'] !== "") ? $request['state_id'] : null,
            'city_name' => ($request['city_name'] !== "") ? $request['city_name'] : null,
            'state_name' => ($request['state_name'] !== "") ? $request['state_name'] : null,
            'state_key' => ($request['state_key'] !== "") ? $request['state_key'] : null,
            'apartment_no' => ($request['apartment_no'] !== "") ? $request['apartment_no'] : null, 
            'street_name' => ($request['street_name'] !== "") ? $request['street_name'] : null,
            'zip_code' => $request['postal_code'],
            'own_phone_no' => ($request['own_phone_no'] !== "") ? str_replace('-', "", $request['own_phone_no']) : "",
            'own_percentage' => $request['own_percentage'],
            'status' => 1,
            'is_approval_requested' => config('b2c_common.APPROVAL_ACCECTED'),
            'is_terms_checked' => 1,
            'updated_by' => $additional_owner_id,
        ];
    }
    
    
    /**
     * Create Application
     * 
     * @param Object $user
     * @return type
     */
    protected function saveApplicationInfo($appDetail, $user) {
        try {
            $arrApp = [];
            $arrAppStatus=[];
            $arrApp["app_user_id"] = $user->id;
            $arrApp["legal_entity_id"] = (int) $appDetail->legal_entity_id;
            $arrApp["industry_id"] = (int) $appDetail->industry_id;;
            $arrApp["current_status"] = config('b2c_common.START_APP');
            $arrApp["status_modify_date"] = Helpers::getCurrentDateTime();
            $arrApp["case_owner_id"] = config('b2c_common.OWNER_ID');
            $arrApp["owner_assign_at"] = Helpers::getCurrentDateTime();
            $arrApp["app_status"] = config('b2c_common.APP_IN_PROGRESS_APPLICTAION');
            //Save Application 
            $app_id = $this->application->saveApplication($arrApp);
            
            //Create app owner for the application
            $this->saveCaseOwnerInfo($app_id, $user->id);
            
            // share the application with owner
            $this->saveShareCaseInfo($app_id, $user);
            
            // Save question data
            $this->saveQuestionsData($appDetail->getAppQuestions, $user, $app_id);
            if(!empty($app_id)){
                /**********Save application status log***********/
                $arrAppStatus['status_id']=config('b2c_common.START_APP');
                $arrAppStatus['app_user_id'] =$user->id;
                $arrAppStatus['app_id']     =$app_id;
                $arrAppStatus["created_at"] = Helpers::getCurrentDateTime();
                $arrAppStatus["created_by"] = $user->id;
                $arrAppStatus["updated_at"] = Helpers::getCurrentDateTime();
                $arrAppStatus["updated_by"] = $user->id;
                Helpers::saveApplicationStatus($arrAppStatus);
            }
            return $app_id;
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
        }
    }
    
    
    /**
     * Create case owner log
     * 
     * @param int $user_id
     * @return type
     */
    public function saveCaseOwnerInfo($app_id, $user_id) {
        try {
            $caseOwnerInfo = [
                'lead_id' => $user_id,
                'case_id' => $app_id,
                'owner_id' => config('b2c_common.OWNER_ID'),
                'created_at' => Helpers::getCurrentDateTime(),
                'created_by' => $user_id,
            ];
            $case_owner_id = $this->application->saveCaseOwner($caseOwnerInfo);
            return $case_owner_id;
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
        }
    }
    
    
     /**
     * Save Pre-Qualifying question
     * 
     * @param array $preQualifiedQues
     */
    public function saveQuestionsData($preQualifiedQues, $user, $app_id) {
        try {
            //Save Question 
            $arrQues = [];
            if (count($preQualifiedQues) > 0) {
                $arrQuesMulti = [];
                foreach ($preQualifiedQues as $key => $val) {
                    $arrQues["app_user_id"] = $user->id;
                    $arrQues["app_biz_id"] = null;
                    $arrQues["app_id"] = $app_id;
                    $arrQues["quest_id"] = (int) $val->quest_id;
                    $arrQues["answer"] = (int) $val->answer;
                    $arrQues["created_at"] = Helpers::getCurrentDateTime();
                    $arrQues["created_by"] = $user->id;
                    $arrQues["updated_at"] = Helpers::getCurrentDateTime();
                    $arrQues["updated_by"] = $user->id;
                    $arrQuesMulti[] = $arrQues;
                }                
                $this->application->saveQuestionsmapping($arrQuesMulti);
            }
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
        }
    }
    
    
    /**
     * Save Share case info
     * 
     * @param int $user_id & $app_id
     * @return type
     */
    public function saveShareCaseInfo($app_id, $user) {
        try {
            $shareCaseInfo = [
                'from_id' => $user->id,
                'to_id' => config('b2c_common.OWNER_ID'),
                'app_user_id' => $user->id,
                'app_id' => $app_id,
                'assign_status' => 1,
            ];
            $shareapp_id = $this->application->saveShareCaseInfo($shareCaseInfo);
            return $shareapp_id;
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
        }
    }
    
    /**
     * Save Required Document 
     * against application
     * 
     * @param repository $documentRepo
     * @param integer $app_id
     * @param integer $doc_type
     */
    protected function saveRequiredDocs($documentRepo, $app_id, $doc_type)
    {
        // Save required document            
        $allDoc = $documentRepo->getDocListByDocType($doc_type);
        if (count($allDoc) > 0) {
            $arrDocs = [];
            foreach ($allDoc as $rowDoc) {
                // Check If exit document
                $exitDoc = $documentRepo->checkExitRequestDocs($app_id, $doc_type, $rowDoc->id);
                if (count($exitDoc) == 0) {
                    $arrDocs['app_id'] = $app_id;
                    $arrDocs['doc_id'] = $rowDoc->id;
                    $arrDocs['is_stip_doc'] = $doc_type;
                    $arrDocs['is_active'] = config('b2c_common.ACTIVE');
                    $documentRepo->saveRequestedDoc($arrDocs);
                }
            }
        }
    }
    
    /**
     * Save Required Document 
     * against application with condition
     * 
     * @param repository $documentRepo
     * @param integer $app_id
     * @param integer $doc_type
     */
    protected function saveRequiredDocswithCondition($documentRepo, $app_id, $doc_type, $editcase=false)
    {
        if($editcase== true){
        $documentRepo->deleteDocReqByDocType($app_id,$doc_type);
        }
        // Save required document            
        $allDoc = $documentRepo->getDocListWithOverdraftProduct($doc_type);
        if (count($allDoc) > 0) {
            $arrDocs = [];
            foreach ($allDoc as $rowDoc) {
                // Check If exit document
                $exitDoc = $documentRepo->checkExitRequestDocs($app_id, $doc_type, $rowDoc->id);
                if (count($exitDoc) == 0) {
                    $arrDocs['app_id'] = $app_id;
                    $arrDocs['doc_id'] = $rowDoc->id;
                    $arrDocs['is_stip_doc'] = $doc_type;
                    $arrDocs['is_active'] = config('b2c_common.ACTIVE');
                    $documentRepo->saveRequestedDoc($arrDocs);
                }
            }
        }
    }
    
    /**
     * Create and send email for email type guarantor access form
     *
     * @param array $current_data
     * @param int $app_id
     * @param int $guarantor_id
     * @return int
     */
    protected function createEmailAccessLinkForApproval($attributes)
    {
        $app_id = isset($attributes['app_id']) ? $attributes['app_id'] : null;
        $app_user_id = isset($attributes['app_user_id']) ? $attributes['app_user_id'] : null;
        $consent_type = isset($attributes['consent_type']) ? $attributes['consent_type'] : null;
        
        $accessFormData = $this->application->getApprovalEmailAccessData(['app_user_id' => $app_user_id, 'app_id' => $app_id, 'consent_type' => $consent_type, 'status' => 0])->toArray();

        if (isset($accessFormData[0]['id'])) {
            $expire_data['status'] = 2;
            $this->application->UpdateEmailAccessForm($expire_data, $accessFormData[0]['id']);
            $this->sendApprovalEmailLink(['app_user_id' => $app_user_id, 'app_id' => $app_id, 'consent_type' => $consent_type]);
        } else {
            $this->sendApprovalEmailLink(['app_user_id' => $app_user_id, 'app_id' => $app_id, 'consent_type' => $consent_type]);
        }
        
    }
    
    /**
     * 
     * @param type $current_data
     * @param type $user__id
     * @param type $app_id
     * @param type $owner_id
     * @param type $userRepo
     * @param type $appRepo
     */
    protected function sendApprovalEmailLink($attributes)
    {
        $emailData = [];
        $app_id         = isset($attributes['app_id']) ? $attributes['app_id'] : null;
        $app_user_id    = isset($attributes['app_user_id']) ? $attributes['app_user_id'] : null;
        $consent_type   = isset($attributes['consent_type']) ? $attributes['consent_type'] : null;
        $emailData['status'] = 0;
        $emailData['app_id'] = $app_id;
        $emailData['app_user_id'] = $app_user_id;
        $emailData['consent_type'] = $consent_type;

        $form_access_id = $this->application->createEmailAccessForm($emailData);
        $userData = $this->userRepo->find((int) $app_user_id);

        //create a new access link
        $param = ['app_id' => $app_id, 'app_user_id' => $app_user_id, 'access_id' => $form_access_id];
        $route = null;
        if($consent_type == config('b2c_common.LINKTYPE.BIZ_STRUCTURE_LINK')) {
            $route = 'email_consent_approval_form';
        }
        $emailFormrequestUpdate['access_link'] = $this->getAccessLink($param, $route);

        $current_data['app_id']      = $app_id;
        $current_data['app_user_id'] = $app_user_id;
        $current_data['consent_type']= $consent_type;
        $current_data['email']       = $userData['email'];
        $current_data['access_link'] = $emailFormrequestUpdate['access_link'];
        $current_data['is_display_in_activity'] = config('b2c_common.IS_DISPLAY_ACTIVITY');
        $current_data['userData'] = $userData;
        // update access link to database
        $resultData = $this->application->updateEmailAccessForm($emailFormrequestUpdate, $form_access_id);
        
        if (!empty($userData['email'])) {
            Event::fire(
                'consent_approval.send', serialize($current_data)
            );
        }
        return $resultData;
    }
    
    /**
     * 
     * @param type $app_user_id
     * @param type $app_id
     * @param type $offer_id
     */
    protected function sendOfferToCustomer($app_user_id, $app_id, $offer_id, $offer_status, $appDataArr, $data_arr)
    {
        $arrUsers = $this->userRepo->find($app_user_id);
        $appData = $this->application->find($app_id);
        $rmDetails = $this->userRepo->find($appData->current_rm);
        $arrUsers->app_id = $app_id;
        $arrUsers->app_locale = $appData['app_locale'];
        $offerDetail = $this->application->getOfferDetail(['app_id' => $app_id, 'offer_id' => $offer_id], ['sanc_amount', 'interest_rate_type']);

        $arrAppData = [];

        $arrAppDataOfferSent['tot_offer_sent'] = 1;
        $arrAppData['current_status'] = config('b2c_common.OFFER_STATUS_SENT');
        $arrAppData['interest_rate'] = isset($data_arr['interest_rate']) ? $data_arr['interest_rate'] : null;
        $appDataArr['intrest_rate'] = $arrAppData['interest_rate'];
        
        $arrAppStatus['status_id']   = config('b2c_common.OFFER_ADDED');
        $arrAppStatus['app_id']      = $app_id;
        $arrAppStatus['app_user_id'] = $app_user_id;
        $arrAppStatus["created_at"]  = Helpers::getCurrentDateTime();
        $arrAppStatus["created_by"]  = \Auth::user()->id;
        $arrAppStatus["updated_at"]  = Helpers::getCurrentDateTime();
        $arrAppStatus["updated_by"]  = \Auth::user()->id;
        Helpers::saveApplicationStatus($arrAppStatus);
        Helpers::updateAppStatus(['app_user_id' => $app_user_id, 'app_id' => $app_id], $arrAppData); 
        $this->application->update(['uw_first_offer_sent' =>1],$app_id);
       // Event::fire("application.on_application_approve", serialize($appDataArr));
        Event::fire("application.send_offer_to_customer", serialize($arrUsers)); 
    }
}