<?php 
namespace App\Helpers; 
use App;
use Session;
use Carbon\Carbon;
class Helper 
{ 
    /***
     * All Countries
     * 
     */
    public static  function countryList()
    {
         return App\Repositories\Models\Master\Country::getallCountryList();
    }
    
    /**
     * All states
     * 
     * @return type
     * 
     */
    public static  function stateList()
    {
         return App\Repositories\Models\Master\State::getAllStates();
    }
    
    /**
     * All states
     * 
     * @return type
     * 
     */
    public static  function getAllEvent()
    {
         return App\Repositories\Models\Master\Eventype::getAllEvent();
    }
    
    /**
     * All states
     * 
     * @return type
     * 
     */
    public static  function formatEditorData($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
    /**
     * All states
     * 
     * @return type
     * 
     */
    public static  function calculateMoney($amt)
    {
        $getConfigNexamt = config('common.nexzoa_per');
        $getConfigGateamt = config('common.nexzoa_Gateway_fee');
        $nexzaAmt = ($amt * $getConfigNexamt)  / 100;
        $gateAmt = ($amt * $getConfigGateamt)  / 100;
        $customerAmt = $amt - $nexzaAmt -  $gateAmt;
        $data = ['nexza_amt'=>$nexzaAmt,'gatway_amt'=>$getConfigGateamt,'customer_total'=>$customerAmt];
        return $data;
    }
    
 /**
     * Get current date and time
     *
     * @return string
     */
    public static function getCurrentDateTime()
    {
        return Carbon::now()->toDateTimeString();
    }
    
     /**
     * return the response of ajax request
     *
     * @param  boolean $success
     * @param  type    $message
     * @param  type    $exception
     * @param  type    $redirect
     * @return type
     */
     public static function ajaxResponse($success, $message, $exception = null, $redirect = null, $modal = null, $status_code = 200, $redirectStop = false)
    {
        return response()->json(
                [
                'success' => $success,
                'message' => $message,
                'exception' => $exception,
                'redirect' => $redirect,
                'modal' => $modal,
                ], $status_code
        );
    }
    
     /**
     * Get exception message w.r.t. application environment
     *
     * @param  Exception $exception
     * @return string
     */
    public static function getExceptionMessage($exception)
    {	 
        $exMessage = trans('messages.generic.failure');
        $actualException = 'Error: ' . $exception->getMessage() . ' . File: ' . $exception->getFile() . ' . Line#: ' . $exception->getLine();
        if (config('app.debug') === true) {
            return $actualException;
        } else {
            self::shootDebugEmail($exception);
            return $exMessage;
        }
    }
    
    /**
     * Send exception emails
     *
     * @param Exception $exception
     * @param string    $exMessage
     * @param boolean   $handler
     */
    public static function shootDebugEmail($exception, $handler = false)
    {
        $request = request();
        $data['page_url'] = $request->url();
        $data['loggedin_userid'] = (auth()->guest() ? 0 : auth()->user()->id);
        $data['ip_address'] = $request->getClientIp();
        $data['browser'] = $request->server('HTTP_USER_AGENT');
        $data['method'] = $request->method();
        $data['message'] = $exception->getMessage();
        $data['class'] = get_class($exception);
        $data['request'] = $request->except('password');
        $data['file'] = $exception->getFile();
        $data['line'] = $exception->getLine();
        $data['trace'] = $exception->getTraceAsString();

        $subject = 'HSBC (' . app()->environment() . ') ' . ($handler ? '' : 'EXCEPTION') . ' Error at ' . date('Y-m-d D H:i:s T');
        config(['mail.driver' => 'mail']);
         Mail::raw(
            print_r($data, true),
            function ($message) use ($subject) {
                $message->to(config('errorgroup.error_notification_group'))
                    ->from(
                        config('errorgroup.error_notification_email'),
                        config('errorgroup.error_notification_from')
                    )
                    ->subject($subject);
            }
        );
    }
    
    
     /**
     * Function to check OTP capping
     * 
     * @param array $requsetType
     * @param int $maxCount
     * @return array
     */
    public static function isValidSessionOTP($requsetType, $maxCount)
    {
        $count = 1;
        if (Session::has($requsetType)) {
            $count = Session::get($requsetType.".count");
            if ($count >=$maxCount) {
                return ['count' => $count, 'status' => false];
            }
            $sessionArr = ['count'=> ++$count];
            Session::put($requsetType, $sessionArr);
        } else {
            $sessionArr = ['count'=> $count];
            Session::put($requsetType, $sessionArr);
        }
        return ['count' => $count, 'status' => true];
    }
    
      /**
     * Get user details
     * 
     * @param array $whereArr
     * @param array $select
     * @return type
     */
    public static function getUserDetails($whereArr = [], $select=[])
    {   
        return App\Repositories\Models\User::getUserDetails($whereArr, $select);
    }
    
    public static function sendSms($number, $message_body){   
        $fields = array(
            "sender_id" => "FSTSMS",
            "message" => $message_body,
            "language" => "english",
            "route" => "p",
            "numbers" => $number,
        );
        $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => config('event.sms_url'),
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_SSL_VERIFYHOST => 0,
              CURLOPT_SSL_VERIFYPEER => 0,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => json_encode($fields),
              CURLOPT_HTTPHEADER => array(
                "authorization: ".config('event.sms_key'),
                "accept: */*",
                "cache-control: no-cache",
                "content-type: application/json"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
              return $err;
            } else {
              return $response;
            }                       // Close CURL
    }
    
     /**
     * Get user details
     * 
     * @param array $whereArr
     * @param array $select
     * @return type
     */
    public static function getEventCount($flag, $user_id)
    {   
        return App\Repositories\Models\Venue::getAllEvent($flag, $user_id);
    }
    
     /**
     * get all program type
     * 
     * @param array $attributes
     * @param array $select
     * @return array
     */
    public static function getCandidateDetails($formID) 
    {
        $data = \App\Repositories\Models\Candidate::getCandidateDetails(null);
        foreach($data as $key=>$val){
            $dataWithID = \App\Repositories\Models\Candidate::getCandidateDetails($formID.'-'.$val['id']);
            if(!empty($dataWithID) && count($dataWithID)>0){
                return $dataWithID[0];
            }
        }
        return [];
    }
    
}