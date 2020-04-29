<?php

namespace App\B2c\Repositories\Factory\Events;
use Auth;
use Request;
use App\Events\Event;
use App\B2c\Repositories\Models\UserLog;
use App\B2c\Repositories\Models\EmailSend;
use App\B2c\Repositories\Models\ActivityLog;
use App\B2c\Repositories\Models\ActivityEmail;
use App\B2c\Repositories\Models\ApplicationTrack;
use App\B2c\Repositories\Models\ApplicationAuditLog;
use App\B2c\Repositories\Models\ApplicationAuditLogData;
use App\B2c\Repositories\Models\SmsLog;

abstract class BaseEvent extends Event
{

    /**
     * Save activity log
     *
     * @param integer $activity_type_id
     * @param string $activity_desc
     * @param array $arrActivity
     * @return bool
     * @since 0.1
     */
    public static function addActivityLog($activity_type_id, $activity_desc, $arrActivity)
    {
        $arrActivity['session_id']       = \Session::get('uuid') ? \Session::get('uuid') : null;
        $arrActivity['activity_type_id'] = $activity_type_id;
        $arrActivity['activity_desc']    = $activity_desc.(isset($arrActivity['auto_logout']) ? ' (timed out)' : '');
        $arrActivity['status']           = 1;

        if (!isset($arrActivity['ip_address'])) {
            $arrActivity['ip_address'] = Request::getClientIp();
        }

        $arrActivity['source']       = Request::server('HTTP_REFERER');
        $arrActivity['browser_info'] = Request::server('HTTP_USER_AGENT');  
        $arrActivity['route_name']   = (!empty(Request::route()) ? Request::route()->getName() : null);

        if (isset(Auth::user()->id)) {
            $arrActivity['created_by'] = Auth::user()->id;
            $arrActivity['updated_by'] = Auth::user()->id;
        } else {
            if (isset($arrActivity['app_user_id'])) {
                $arrActivity['created_by'] = $arrActivity['app_user_id'];
                $arrActivity['updated_by'] = $arrActivity['app_user_id'];
                $arrActivity['app_user_id'] =$arrActivity['app_user_id'];
            } else {
                $arrActivity['created_by'] = null;
                $arrActivity['updated_by'] = null;
            }
        }
        $activityData = ActivityLog::create($arrActivity);
        return ($activityData ? : false);
    }

    /**
     * Save application audit data
     *
     * @param array $arrActivity
     * @return bool
     */
    public static function addApplicationAuditLog($arrActivity)
    {
        $ojbAppLog = new ApplicationAuditLog($arrActivity);
        $saved     = $ojbAppLog->create($arrActivity);
        if ($saved->id) {
            $arrLogData                 = [];
            $arrLogData['audit_log_id'] = $saved->id;
            $arrLogData['request_data'] = $arrActivity['request_data'];

            $ojbAppLogData = new ApplicationAuditLogData($arrLogData);
            $savedLogData  = $ojbAppLogData->save();

            return $savedLogData;
        }
    }

    /**
     * Save user log
     *
     * @param string $activity_type
     * @param array $user_array
     * @return bool
     * @since 0.1
     */
    public static function addUserLog($activity_type, $user_array)
    {
        $user_array["created_at"]  = \Helpers::getCurrentDateTime();
        $user_array["created_by"]  = isset(\Auth::user()->id) ? (int) \Auth::user()->id : null;
        $user_array['action_name'] = $activity_type;
        $objActivity               = new UserLog($user_array);
        $saved                     = $objActivity->save();

        return $saved;
    }

    /**
     * Save send email log
     *
     * @param array $mailInfo
     * @return bool
     */
    public static function addSendEmailLog($mailBody, $mailSubject, $mailContentArr, $mailInfo)
    {
        
        $mailInfo['email_category_id'] = isset($mailContentArr->email_cat_id) ? $mailContentArr->email_cat_id : null;
        $mailInfo['subject'] = $mailSubject;
        $mailInfo['user_id'] = $mailInfo['app_user_id'];
        $mailInfo['sender_id'] = $mailInfo['app_user_id'];
        $mailInfo['receiver_id'] = $mailInfo['app_user_id'];
        $mailInfo['attached_file'] = '';
        $mailInfo['mail_content'] = $mailBody;
        $mailInfo['mail_to'] = isset($mailInfo['email']) ? $mailInfo['email'] : null;
        $mailInfo['email_template'] = isset($mailContentArr->email_cat_id) ? $mailContentArr->email_cat_id : null;
        $mailInfo['template_type'] = isset($mailContentArr->template_type) ? $mailContentArr->template_type : null;
        $saved = EmailSend::create($mailInfo);
        return ($saved ? $saved->send_email_id : false);
    }
    
    /**
     * Save send activity email
     *
     * @param array $mailInfo
     * @return bool
     */
    public static function addActivityEmail($activty, $sendEmailId=null)
    {
        $arrActivity['activity_log_id'] = $activty['id'];
        $arrActivity['activity_type_id'] = $activty['activity_type_id'];
        $arrActivity['send_email_id'] = $sendEmailId;       
        $objActivity               = new ActivityEmail($arrActivity);
        $saved                     = $objActivity->save();       
        return $saved;
    }
    
    /**
     * Log Error in SMS sending
     * 
     * @param type $activity
     * @param type $phoneno
     * @param type $error
     * @param type $from
     */
    
    Public static function addSMSLog($activity,$phoneno,$error,$from){
        
        $arrData['activity_id'] = $activity;
        $arrData['phone_no'] = $phoneno;
        $arrData['error'] = $error;
        $arrData['send_from'] = $from;
        $arrData['created_at'] = \Helpers::getCurrentDateTime();
        $arrData['created_by'] = (int) (isset(\Auth::user()->id)?\Auth::user()->id:0);
        $arrData['ip_address'] = Request::getClientIp();
        
        SmsLog::saveSMSLog($arrData);
    }
}
