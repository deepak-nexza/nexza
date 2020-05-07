<?php

namespace Nexza\Otp\Http\Controllers;

use Event;
use Session;
use Helpers;
use Auth;
use Exception;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use App\Libraries\IovationApi;
use Nexza\Otp\Repositories\Otp\OtpInterface;
use App\Repositories\Contracts\ApplicationInterface;
use App\Repositories\User\UserInterface as B2cUserRepoInterface;
//use App\Repositories\Contracts\GuestInterface as B2cGuestRepoInterface;

class OtpController extends Controller {

    /**
     * OTP Repository.
     * @var \Nexza\Otp\Repositories\Otp\OtpInterface
     */
    private $otpRepo;

    /**
     * Guest repository
     *
     * @var App\Repositories\Contracts\GuestInterface
     */
    protected $guestRepo;

    /**
     * User repository.
     *
     * @var \App\Repositories\Contracts\UserInterface
     */
    protected $userRepo;
    
    /**
     * Application repository
     *
     * @var App\Repositories\Contracts\ApplicationInterface
     */
    protected $application;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(OtpInterface $otpRepo, B2cUserRepoInterface $user) {
        $this->otpRepo = $otpRepo;
        $this->userRepo = $user;
        if (Session::has('otpUserId')) {
            $this->user_id = Session::get('otpUserId');
        }
    }

    /**
     * Function for display view of otp.
     *
     * @return void
     */
    public function getUserOtp() {
        $isResendOTP = Session::get('resendOtp');
        if (isset($isResendOTP) && $isResendOTP == 1) {
            return redirect()->route('login_open');
        }
        try {
            $uId = Session::get('otpUserId');
            if (empty($uId)) {
                return redirect()->route('login_open');
            }
            // If user hold this screen for 5 min redirect to login page
            $OtpScreenTime = Session::get('OTPScreen');
            $activeTime = config('b2c_common.OTP_SCREEN_HOLD_TIME') * 60;
            if ((time() > ($OtpScreenTime + $activeTime))) {
                Session::flash('message', trans('error_message.session_timeout'));
                return redirect()->route('login_open');
            }
            $userData = $this->userRepo->getUserDetail($uId);
            if (isset($userData->is_otp_authenticate) && $userData->is_otp_authenticate === 1) {
                if (Session::has('OTPScreen')) {
                    Session::forget('OTPScreen');
                }
                $request->session()->put('ChangePasswordScreen', time());
                return redirect()->route('beforeLoginPassword');
            }
            $statusArray = [];
            $statusArray['email'] = Crypt::encrypt(2);
            $statusArray['otp'] = Crypt::encrypt(1);
            return view('otp::otp')->with('userData', $userData)->with('statusArray', $statusArray);
        } catch (Exception $e) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }

    /**
     * Function for OTP validate.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function getOtpValidate(Request $request) {
        try {
            $otp = $request->input('otpCode');
            $blackbox = $request->get('blackbox');            
            $uId = Session::get('otpUserId');
            $varPassword = Session::get('password');
            $skipPromoCode = null;
            if (Session::has('OtpOnSubmit')) {
                Session::forget('OtpOnSubmit');
            }
            $redirect_url = '';
            if (!empty($uId)) {
                $userData = $this->userRepo->getUserDetail($uId);
                $resend = 0;
                $code = $this->otpRepo->checkOtp($otp, $resend);
                if ($code == 3) {
                    $this->otpRepo->deactivateOtp($uId);
                    $currentUserData = $this->userRepo->getUserDetail($uId);
                    $code = Session::get('loginStatus');
                    $credentials = ['email' => $currentUserData->email, 'password' => $varPassword];
                    if ($credentials) {
                        /* Complete process change user otp status */
                        $currentUserData = $this->userRepo->getUserDetail($uId);
                        $arrOtpStatus = ["is_otp_authenticate" => 1];
                        $updateOTP = $this->userRepo->updateUser($uId, $arrOtpStatus);
                        
                }
                //Helpers::trackApplicationActivity($message, $currentUserData['id'], $currentUserData['app_id']);
                return json_encode(['status' => $code, 'redirect' => $redirect_url]);
            } else {
                 if ($code == 1) {
                     $message = trans('One-time verification code is inactive.');
                    Session::put('error', $message);
                }elseif ($code == 2) {
                    $message = trans('Otp Expired');
                    Session::put('error', $message);
                }
                Session::flash('message', trans('error_message.session_timeout'));
                return json_encode(['status' => 0]); //redirect to login
            }
            }
        } catch (Exception $e) {
            return Helpers::getExceptionMessage($e);
        }
    }

    /**
     * Function for display view of resend otp.
     *
     * @return void
     */
    public function resendOtp(Request $request) {
        try {

            $uId = Session::get('otpUserId');
            $user_data = '';
            if ($uId == '') {
                $user_data = $this->userRepo->getUserByEmail($request->get('email'));
                $uId = $user_data->id;
            }
            if (Session::has('WrongOtpCount')) {
                Session::forget('WrongOtpCount');
            }
//            dd(Session::get('OtpOnSubmit'),Session::get('WrongOtpCount'));
            // dd(Session::get('WrongOtpCount'));

            if (empty($uId)) {
                return redirect()->route('login');
            }
            $userData = $this->userRepo->getUserDetail($uId);
            $this->otpRepo->deactivateOtp($uId);
            $otp = $this->otpRepo->getNewOtp();
            $resend = 1;
            $code = $this->otpRepo->checkOtp($otp, $resend);
            /* Returns forward OTP */
//            $this->createOtpProcess($otp, $uId, $userData, $request);
            if ($code == 0) {
                $max_count = config('otp.submit_max_limit');
                $max_count = $max_count - 1;
                $isValidSession = Helpers::isValidSessionOTP('OtpOnSubmit', $max_count);
//                dd($isValidSession);
                    /* Returns forward OTP */
                $this->createOtpProcess($otp, $uId, $userData, $request);
                if (isset($isValidSession['status']) && $isValidSession['status'] == true) {
                    if (isset($isValidSession['count']) && $isValidSession['count'] == $max_count) {
                        return json_encode(['status' => 8]); //max submit attempt -1
                    }
                } else {
                    Session::flash('messageVerify', trans('otp::otp.otp_submit_max_limit'));
                    $isMaxOtpResendLimit = Session::get('isMaxOtpResendLimit');
                    if($isMaxOtpResendLimit == true && $isMaxOtpResendLimit != NULL) {
//                        dd("Otp reach to your max limit");
                        return json_encode(['status' => 9]);
                    }
                    Session::put('isMaxOtpResendLimit', true);
                    //Block user for 60 min
                    $attributes = [];
                    $attributes['otp_blocked'] = 1;
                    $attributes['method_type'] = 1;
                    $attributes['updated_at'] = Helpers::getCurrentDateTime();
                    $this->userRepo->updateUser($uId, $attributes);
                    return json_encode(['status' => 9]); //max submit attempt
                }
            }
            return json_encode(['status' => $code]);
        } catch (Exception $e) {
            dd($e->getMessage(),$e->getLine());
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }

    /**
     * Create Otp and send e-mial to user
     * 
     * @param object $user
     * @param string $email
     * @param string $pass
     * @return type
     */
    public function createOtpProcess($otp, $uId, $userData, $request) {
        try {
            /* Returns forward OTP */
            $arrUser["otp"] = $otp;
            $arrUser["otp_status"] = "Signup OTP";
            $arrUser["user_id"] = $uId;
            $arrUser["email"] = $userData->email;
            if (!empty($otp)) {
                /* temp purpose user value store to session */
                $user_authentication = $this->userRepo->checkAuthenticateUser($uId);
                if ($user_authentication == false) {
                    $this->otpRepo->deactivateOtp($uId);
                    $otpUser = array();
                    $otpUser["user_id"] = $uId;
                    $otpUser["otp"] = $otp;
                    $otpUser["is_active"] = 1;
                    $otpUser["created_at"] = Helpers::getCurrentDateTime();
                    $otpUser["updated_at"] = Helpers::getCurrentDateTime();
                    $this->otpRepo->insertOtp($otpUser);
                    $request->session()->put('otpUserId', $uId);
                    $request->session()->put('loginStatus', 5);  // 5 use for registration
                    $request->session()->put('tempPassword', $userData->password);
                    $request->session()->put('OTPScreen', time());
                    //Event for sending code

//                    Event::fire("otp.sendotp", serialize($arrUser));
                }
            }
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
        }
    }

    /**
     * Function for display view of resend otp.
     *
     * @return void
     */
    public function resendOtpUrl(Request $request) {

        try {
            if($request->session()->has('reset_email'))
            {
                $email_id = $request->session()->pull('reset_email');
            }else{
                abort(400);
            }
            if (!empty($email_id) && isset($email_id)) {
                $chkInfo = $this->userRepo->checkEmailExist($email_id);
                if(count($chkInfo)> 0)
                {
                     $uId = $chkInfo->id;
                }else{
                   return abort(400);
                }
            }
            if ($request->session()->has('otpUserId')) {
                $uId = $request->session()->get('otpUserId');
            }
            $userData = $this->userRepo->getUserDetail($uId);
            /* Returns forward OTP */
            $otp = $this->otpRepo->getNewOtp();
            $arrUser["otp"] = $otp;
            $arrUser["otp_status"] = "Signup OTP";
            $arrUser["user_id"] = $uId;
            $arrUser["email"] = $userData->email;
            if (!empty($otp)) {
                /* temp purpose user value store to session */
                $user_authentication = $this->userRepo->checkAuthenticateUser($uId);
                if ($user_authentication == false) {
                    $this->otpRepo->deactivateOtp($uId);
                    $otpUser = array();
                    $otpUser["user_id"] = $uId;
                    $otpUser["otp"] = $otp;
                    $otpUser["is_active"] = 1;
                    $otpUser["created_at"] = Helpers::getCurrentDateTime();
                    $otpUser["updated_at"] = Helpers::getCurrentDateTime();
                    $this->otpRepo->insertOtp($otpUser);
                    $request->session()->put('otpUserId', $uId);
                    $request->session()->put('loginStatus', 5);  // 5 use for registration
                    $request->session()->put('tempPassword', $userData->password);
                    $request->session()->put('OTPScreen', time());
                    Event::fire("otp.sendotp", serialize($arrUser));
                }
            }
            return view('otp::resend_otp');
        } catch (Exception $ex) {
             if (empty($ex->getMessage()) && $ex->getStatusCode() == 400) {
                throw $ex;
            } else {
                return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex))->withInput();
            }
        }
    }

    /**
     * Function for count otp max attempts.
     *
     * @return void
     */
    public function OtpMaxAttempts() {
        $max_count = config('otp.submit_max_limit');
        $max_count = $max_count - 1;
        $isValidSession = Helpers::isValidSessionOTP('OtpOnSubmit', $max_count);
        if (isset($isValidSession['status']) && $isValidSession['status'] == true) {
            if (isset($isValidSession['count']) && $isValidSession['count'] == $max_count) {
                return json_encode(['status' => 8]); //max submit attempt -1
            }
        } else {
            Session::flash('messageVerify', trans('otp::otp.otp_submit_max_limit'));
            return json_encode(['status' => 9]); //max submit attempt
        }
    }

    /**
     * Function for count otp max attempts.
     *
     * @return void
     */
    public function OtpCompleteProcess() {
        $max_count = config('otp.submit_max_limit');
        $max_count = $max_count - 1;
        $isValidSession = Helpers::isValidSessionOTP('OtpOnSubmit', $max_count);
        if (isset($isValidSession['status']) && $isValidSession['status'] == true) {
            if (isset($isValidSession['count']) && $isValidSession['count'] == $max_count) {
                return json_encode(['status' => 8]); //max submit attempt -1
            }
        } else {
            Session::flash('messageVerify', trans('otp::otp.otp_submit_max_limit'));
            return json_encode(['status' => 9]); //max submit attempt
        }
    }
    
    /**
     * To check iovation API response.
     *
     * @return string
     */
    public function checkIovationLogin($iovation_data)
    {
      $iovation_reference = $iovation_data['app_id'].'-'.$iovation_data['app_user_id'];
      $iovation = new IovationApi();
      $output = $iovation->getIovationResponse($iovation_reference, $iovation_data['black_box']);
      $final_request = json_encode($output[0]);
      $response = $output[1];
      $final_response = json_decode($response,false,512,JSON_BIGINT_AS_STRING);
      $created_at = Helpers::getCurrentDateTime();
      $updated_at = Helpers::getCurrentDateTime();
      if(isset($response)) {
        $result= isset($final_response->result) ? $final_response->result : '';
        $iovation_id = isset($final_response->id) ? $final_response->id : 0;
        $tracking_number = isset($final_response->trackingNumber) ? $final_response->trackingNumber : '';
        $score =  isset($final_response->details->ruleResults->score) ? $final_response->details->ruleResults->score : '';
        $this->application->saveIovationDetails(['app_user_id'=>$iovation_data['app_user_id'],'app_id'=>$iovation_data['app_id'],'request'=>$final_request,'response'=>$response,'iovation_id'=>$iovation_id,'result'=>$result,'tracking_number'=>$tracking_number,'score'=>$score,'created_by'=>$iovation_data['app_user_id'],'updated_by'=>$iovation_data['app_user_id']]);
        $this->application->updateApplication($iovation_data['app_id'],['fraud_score'=>$score]);
      }
      //return $result;
   }

}