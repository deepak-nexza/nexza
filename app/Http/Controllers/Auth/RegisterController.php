<?php

namespace App\Http\Controllers\Auth;

//use Otp;
use Auth;
//use Crypt;
use Event;
use Session;
use Helpers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Http\Requests\ProfileRequest;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Nexza\Otp\Repositories\Otp\OtpInterface;
use App\Http\Controllers\Contracts\Traits\LoginTrait;
use App\Repositories\Contracts\Traits\UserSessionTrait;
use App\Repositories\User\UserInterface as UserRepoInterface;


class RegisterController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Register Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles the registration of new users as well as their
      | validation and creation. By default this controller uses a trait to
      | provide this functionality without requiring any additional code.
      |
     */

use RegistersUsers,
    ThrottlesLogins,    
    LoginTrait;

    /**
     * Request
     *
     * @var Illuminate\Http\Request;
     */
    protected $request;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * User Repository.
     *
     * @var \App\B2c\Repositories\Contracts\UserInterface
     */
    protected $user;

    /**
     * OTP Repository.
     *
     * @var \Biz2Credit\Otp\Repositories\Otp\OtpInterface
     */
    protected $otpRepo;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    /**
     * Application repository
     *
     * @var App\B2c\Repositories\Contracts\ApplicationInterface
     */
    protected $application;

    public function __construct(Request $request, UserRepoInterface $user, OtpInterface $otpRepo) {
        $this->request = $request;
        $this->user = $user;
        $this->otpRepo = $otpRepo;
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    public function createProfile()
            
    {   
        if (Session::has('SendOTP')) {
            Session::forget('SendOTP');
        }
        if (Session::has('OtpOnResend')) {
            Session::forget('OtpOnResend');
        }
        if (Session::has('OtpOnSubmit')) {
            Session::forget('OtpOnSubmit');
        }
        if (Session::has('WrongOtpCount')) {
            Session::forget('WrongOtpCount');
        }
        return view('auth.register');
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    public function saveProfile(ProfileRequest $request)
    {
        try {
            $request = request();
            $email = $request->get('email');
            $pass = $request->get('password');
            $user = $this->createUser($request, $email, $pass, $userData=null);
            //application start
            $session_id = session()->getId(); 
            $userId = $user->id;
            $request->session()->put('password', $pass);
            $arrData = ['user_id'=>$userId];
             
            $locale = app()->getLocale();
            //Save Question 
            //Destroy previous otp session
            if (Session::has('SendOTP')) {
                Session::forget('SendOTP');
            }
            if (Session::has('OTPScreen')) {
                Session::forget('OTPScreen');
            }
           
            /* Returns forward OTP */
            $this->createOtpProcess($request, $user, $email, $pass);
            return Helpers::ajaxResponse(true, null, null, null, null);
        } catch (\Exception $ex) {
             return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
        }
    }

    /**
     * Create User
     * 
     * @param string $email
     * @param string $pass
     * @return type
     */
    public function createUser($request, $email, $pass, $userData = null)
    {
        try {
            $arrUser = [];
            $arrUser["email"] = $email;
            $arrUser["password"] = bcrypt($pass);
            $arrUser["contact_number"] = $request->get('phone');
            $arrUser["ip_address"] = $request->getClientIp();
            $arrUser["otp_blocked"] = 0;
            $arrUser["block_status"] = 0;
            $arrUser["is_otp_authenticate"] = 0;
            $arrUser["login_attempted"] = 0;
            $user = $this->user->saveUser($arrUser,null);
            $message = "Registration Successfully";
            return $user;
        } catch (\Exception $ex) {
            dd($ex->getMessage());
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
        }
    }


    
    /*
     * Save User Role
     * @param $user
     */
    public function saveUserRole($user) {
        try {
            $userData = [
                'user_id' => $user->id,
                'role_id' => config('b2c_common.USER_ROLE_ID'),
            ];
            $this->application->saveUserRole($userData);
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
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
    public function createOtpProcess($request, $user, $email, $pass) {
        try {
            $otp = $this->otpRepo->getNewOtp();
            $arrUser["otp"] = $otp;
            $arrUser["otp_status"] = "Signup OTP";
            $arrUser["user_id"] = $user->id;
            $arrUser["email"] = $email;
            $arrUser["password"] = bcrypt($pass);
            if (!empty($otp)) {
                /* temp purpose user value store to session */
                $user_authentication = $this->user->checkAuthenticateUser($user->id);
                if($user_authentication==false){
                $this->otpRepo->deactivateOtp($user->id);
                $otpUser = array();
                $otpUser["user_id"] = $user->id;
                $otpUser["otp"] = $otp;
                $otpUser["is_active"] = 1;
               // $otpUser["created_at"] = Helpers::getCurrentDateTime();
               // $otpUser["updated_at"] = Helpers::getCurrentDateTime();
                $this->otpRepo->insertOtp($otpUser);
                $request->session()->put('otpUserId', $user->id);
                $request->session()->put('loginStatus', 5);  // 5 use for registration
                $request->session()->put('tempPassword', $pass);
                $request->session()->put('OTPScreen', time());
                $messageBody = 'Your otp code from nexzoa: '.$otp;
                $resMsg = Helpers::sendSms($user->contact_number,$messageBody);
                $s = $this->otpRepo->updateOtpStatus($user->id,$otp,$resMsg);
                }
                return true;
            } else {
                return redirect()->route('login');
            }
        } catch (\Exception $ex) {
            dd("sad------".$ex->getMessage());
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
        }
    }

    /**
     * Verified OTP then save user other information
     *
     * @return Response
     */
    public function saveAuthUser() {
        $uId = Session::get('otpUserId');

        /* Change OTP status */
        $this->otpRepo->deactivateOtp($uId);
        //Save User Log
        $arrUser["action_name"] = 'create';
        $arrUser["userlevel_id"] = config('b2c_common.FRONTEND_USERLEVEL');
        $arrUser["created_by"] = $uId;
        $arrUser["updated_by"] = $uId;
        //$arrUser["role_id"] = $role->id;
        $arrUser["role_id"] = 0;
        // $this->userRepo->saveUserLog($arrUser);
        $currentUserData = $this->userRepo->getUserDetail($uId);
        $varPassword = Session::get('tempPassword');
        Session::forget('loginStatus');
        if (Session::has('SendOTP')) {
            Session::forget('SendOTP');
        }
        if (Session::has('OtpOnSubmit')) {
            Session::forget('OtpOnSubmit');
        }
        if (Session::has('RegLink')) {
            Session::forget('RegLink');
        }
        if (Session::has('OTPScreen')) {
            Session::forget('OTPScreen');
        }

        $credentials = ['email' => $currentUserData->email, 'password' => $varPassword];


        if ($credentials) {

            /* Complete process change user otp status */
            $arrOtpStatus = ["is_otp_authenticate" => 1,'updated_at'=>Helpers::getCurrentDateTime(),'updated_by'=>$uId];
            $updateOTP = $this->userRepo->updateUser($uId, $arrOtpStatus);

            if (Auth::attempt($credentials, false)) {                
                /**
                 * Remove existing token
                 */
                Session::forget('_token');
                if (Session::has('otpUserId')) {
                    Session::forget('otpUserId');
                }
                Session::flash('auto_login_message', trans('messages.success.msg'));
                return redirect(route('purpose_of_loan', ['app_user_id' => $currentUserData['id'], 'app_id' => $currentUserData['app_id']]));
            }
        } else {
            // Somehow user creation was failed
            $messages = new MessageBag;
            $messages->add('user_creation_failed', trans('error_message.user_creation_failed'));
            return redirect($this->registerPath())->withErrors($messages)->withInput();
        }
//        } else {
//            // Handle the situation where there is no "Customer" role in the database
//            // Registration is temprarily off
//            $messages = new MessageBag;
//            $messages->add('no_reg_role', trans('error_message.no_reg_role'));
//            return redirect($this->registerPath())->withErrors($messages)->withInput();
//        }
    }

    /**
     * Log user login activity
     *
     * @param object $user_data
     */
    protected function logUserLoginEvent()
    {
        /**
         * Create activity log for user login
         */
        $user_data = $this->userRepo->getAuthUserData();

        if (!$user_data) {
            return redirect('/');
        }

        Session::put('uuid', Helpers::createUuid());

        $this->setClientTimeZone(request());

        Event::fire("user.login.success", serialize(
                [
                    'user_id' => $user_data->id,
                    'by_whom_id' => $user_data->id,
                    'email' => $user_data->email
                ]
        ));
    }
}
