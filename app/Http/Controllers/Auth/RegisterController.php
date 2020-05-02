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
use App\Http\Requests\RegistrationFormRequest;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Biz2Credit\Otp\Repositories\Otp\OtpInterface;
use App\Http\Controllers\Contracts\Traits\LoginTrait;
use App\B2c\Repositories\Contracts\Traits\CaptchaTrait;
use App\B2c\Repositories\Contracts\ApplicationInterface;
use App\B2c\Repositories\Contracts\Traits\UserSessionTrait;
use App\B2c\Repositories\Contracts\UserInterface as B2cUserRepoInterface;
use App\B2c\Repositories\Contracts\GuestInterface as B2cGuestRepoInterface;


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
    CaptchaTrait,
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
    protected $userRepo;

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

    public function __construct(Request $request, B2cUserRepoInterface $user, OtpInterface $otpRepo, ApplicationInterface $application, B2cGuestRepoInterface $guestRepo) {
        $this->middleware('guest');
        $userSession = Auth::user();
        if(!isset($userSession['id'])){
            redirect('/home');
        }
        $this->application = $application;
        $this->request = $request;
        $this->userRepo = $user;
        $this->otpRepo = $otpRepo;
        $this->guestRepo= $guestRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        if(\Session::has('is_knockout')){
            \Session::forget('is_knockout');
        }
         $promoCodeStatus = $this->userRepo->getSwitchControlByModule('promocode');
         /*if(config('b2cin.IS_PROMOCODE_REQUIRED') == true) {
             return redirect(route('promo_code'));
         } else{
             return redirect(route('qualifying_question'));
         }*/
         if($promoCodeStatus['customer'] == 1) {
             return redirect(route('promo_code'));
         } else{
             return redirect(route('qualifying_question'));
         }
        //return view('auth.signup');
        // return view('frontend.guest.promo_code');
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    public function createProfile()
    {   $promoCodeStatus = $this->userRepo->getSwitchControlByModule('promocode');
        if(Session::has('skipPromoCode')){
            $skipPromoCode = 1;
        }
        /*if(config('b2cin.IS_PROMOCODE_REQUIRED') == true) {
            if (Session::has('checkSuccRedirect') === false) {
                return redirect(route('promo_code'));
            }
        }*/
        if( $promoCodeStatus['customer'] == 1 && empty($skipPromoCode)) {
            if (Session::has('checkSuccRedirect') === false) {
                return redirect(route('promo_code'));
            }
        }
        if (Session::has('PreQualifiedQues') === false) {
            return redirect(route('qualifying_question'));
        }
        if (Session::get('industry')[0]['industry'] === false) {
            return redirect(route('industry_type'));
        }
        
        if (Session::has('entity') === false) {
            return redirect(route('business_structure'));
        }
        //Destroy OTP releated session
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
        return view('auth.create_profile');
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    public function saveProfile(RegistrationFormRequest $request)
    {
        try {
            $request = request();
            $business_name = $request->get('biz_name');
            $checkBusiness = $this->userRepo->checkBusinessName($business_name);
            if($checkBusiness) {
                return Helpers::ajaxResponse(false, trans('messages.business_name_exits_both'));
            }
            if ($this->captchaCheck($request) == false) {
                return Helpers::ajaxResponse(false, trans('messages.error.invalid_captcha'), null, null, null);
            }
            $email = $request->get('email');
            $pass = $request->get('password');
            $userData = $this->userRepo->getUserData(['user_level_id' => config('b2c_common.RM_USERLEVEL'), 'is_admin' => config('b2c_common.IS_BACKEND_ADMIN')], ['id', 'first_name', 'last_name', 'email']);
            $user = $this->createUser($request, $email, $pass, $userData);
            
            //application start
            $session_id = session()->getId(); 
            $userId = $user->id;
            $request->session()->put('password', $pass);
            $arrData = ['app_user_id'=>$userId];
            $this->guestRepo->updateTrackUser($session_id,$arrData);
             
            $app_id = $this->saveApplicationInfo($request, $user, $userData);
            $locale = app()->getLocale();
            if($locale != 'en' && $locale != 'fr') {
                $locale = 'en';
            }
            $this->application->updateApplication($app_id, ['app_locale' => $locale]);
            //Save Question 
            $preQualifiedQues = $request->session()->get('PreQualifiedQues')['PreQualifiedQues'];
            $this->saveQuestionsData($preQualifiedQues, $user, $app_id);
            if (Session::has('promo_code')) {
                $promo_code = $request->session()->get('promo_code');
                $this->userRepo->updateUser($userId, ['promo_code' => $promo_code['promo_code']]);
                $this->guestRepo->saveUserPromoCode($userId, $app_id, ['app_user_id'=>$userId, 'promo_code' => $promo_code['promo_code'],'app_id'=>$app_id,'created_by'=>$userId,'updated_by'=>$userId]);
            }

            /******Save Share Lead Info*****/
            $this->saveShareLeadInfo($user, $isAssginRM = false, $userData);
            $this->saveShareLeadInfo($user, $isAssginRM = true, $userData);
            /******Save Share Case Info*****/
            
            $this->saveShareCaseInfo($app_id, $user, $isAssginRM = false, $userData);
            
            $this->saveShareCaseInfo($app_id, $user, $isAssginRM = true, $userData);
            /******Save User Role*****/
            $this->saveUserRole($user);

            //Destroy previous otp session
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
            if (Session::has('ChangePasswordScreen')) {
                Session::forget('ChangePasswordScreen');
            }
            if (Session::has('promo_code')) {
                Session::forget('promo_code');
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
            $arrUser["ip_address"] = $request->getClientIp();
            $arrUser["otp_blocked"] = config('b2c_common.NO');
            $arrUser["block_status"] = config('b2c_common.NO');
            $arrUser["is_otp_authenticate"] = 0;
            $arrUser["login_attempted"] = 0;
            $arrUser["lead_owner_id"] = config('b2c_common.OWNER_ID');
            $arrUser["owner_assign_at"] = Helpers::getCurrentDateTime();
            $arrUser['biz_name']= !empty($request->get('biz_name')) ? $request->get('biz_name') : null ;
            $arrUser['contact_number']= !empty($request->get('contact_number')) ? str_replace('-', "", $request->get('contact_number')) : "";
            $user = $this->userRepo->save($arrUser);
            $message = "Registration Successfully";

            //Helpers::trackRegistrationActivity($message, $user->id, $app_id=null);
            /*             * ********Save Lead Owner Log detasils********** */
            $this->saveLeadOwnerInfo($user->id, $userData);
            //Event for create profile

            return $user;
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
        }
    }

    /**
     * Create Application
     * 
     * @param Object $user
     * @return type
     */
    public function saveApplicationInfo($request, $user, $userData = null)
    {
        try {
            $industry = (int) $request->session()->get('industry')[0]['industry'];
            $division = (\Session::has('division')) ? (int) \Session::get('division')['0'] : null;
            $sub_division = (\Session::has('sub_division')) ? (int) \Session::get('sub_division')['0'] : null;

            $entity = (int) $request->session()->get('entity')[0]['entity'];
            $arrApp = [];
            $arrAppStatus = [];
            $arrApp["app_user_id"] = $user->id;
            $arrApp["legal_entity_id"] = $entity;
            $arrApp["industry_id"] = $industry;
            $arrApp["division"] = $division;
            $arrApp["sub_division"] = $sub_division;
            $arrApp["current_assignee"] = config('b2c_common.RM_CURRENT_ASSIGNEE');
            $arrApp["app_status"] = config('b2c_common.APP_AUTHENTICATION_PENDING');
            $arrApp["current_status"] = config('b2c_common.APP_CUR_AUTHENTICATION_PENDING');
            $arrApp["status_modify_date"] = Helpers::getCurrentDateTime();
            $arrApp["user_privacy_consent"] = 1;
            $arrApp["biz_privacy_consent"] = 1;
            $arrApp["version"] = 2;
            if($userData != null && isset($userData) && $userData->id > 0) {
                $arrApp["case_owner_id"] = (int) $userData->id;
                $arrApp["owner_assign_at"] = Helpers::getCurrentDateTime();
                $arrApp["current_rm"] = (int) $userData->id;
                $arrApp["rm_assign_at"] = Helpers::getCurrentDateTime();
            }
            $arrApp["created_at"] = Helpers::getCurrentDateTime();
            $arrApp["created_by"] = $user->id;
            $arrApp["updated_at"] = Helpers::getCurrentDateTime();
            $arrApp["updated_by"] = $user->id;
            
             $arrApp["created_at"] = Helpers::getCurrentDateTime();
             $arrApp["created_by"] = $user->id;
             $arrApp["updated_at"] = Helpers::getCurrentDateTime();
             $arrApp["updated_by"] = $user->id;
            //Save Application 
            $app_id = $this->application->saveApplication($arrApp);
            $this->saveCaseOwnerInfo($app_id, $user->id, (int) $userData->id);
            if (!empty($app_id)) {
                /*                 * ********Save application status log********** */
                $arrAppStatus['status_id'] = config('b2c_common.APP_CUR_AUTHENTICATION_PENDING');
                $arrAppStatus['app_id'] = $app_id;
                $arrAppStatus['app_user_id'] = $user->id;
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
     * Save Pre-Qualifying question
     * 
     * @param array $preQualifiedQues
     */
    public function saveQuestionsData($preQualifiedQues, $user, $app_id) {
        try {
            //Save Question 
            $arrQues = [];
            if (!empty($preQualifiedQues)) {
                $arrQuesMulti = [];
                foreach ($preQualifiedQues as $key => $val) {
                    $arrQues["app_user_id"] = $user->id;
                    $arrQues["app_biz_id"] = null;
                    $arrQues["app_id"] = $app_id;
                    $arrQues["quest_id"] = $key;
                    $arrQues["answer"] = $val;
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
     * Create lead owner log
     * 
     * @param int $user_id
     * @return type
     */
    public function saveLeadOwnerInfo($user_id, $userData) {
        try {
            $leadOwnerInfo = [
                'lead_id' => $user_id,
                'owner_id' => $userData->id,
                'role_level' => config('b2c_common.RM_ROLE_LEVEL'),
                'created_at' => Helpers::getCurrentDateTime(),
                'created_by' => $user_id,
            ];
            $lead_owner_id = $this->application->saveLeadOwner($leadOwnerInfo);
            return $lead_owner_id;
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
    public function saveCaseOwnerInfo($app_id, $user_id, $adminRM = null) {
        try {
            $caseOwnerInfo = [
                'lead_id' => $user_id,
                'case_id' => $app_id,
                'owner_id' => $adminRM,
                'role_level' => config('b2c_common.RM_ROLE_LEVEL'),
                'created_at' => Helpers::getCurrentDateTime(),
                'created_by' => $user_id,
            ];
            $lead_owner_id = $this->application->saveCaseOwner($caseOwnerInfo);
             
            return $lead_owner_id;
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
    public function saveShareCaseInfo($app_id, $user, $isAssginRM = false, $userData = null) {
        try {
            $shareCaseInfo = [
                'from_id' => $user->id,
                'to_id' => config('b2c_common.OWNER_ID'),
                'app_user_id' => $user->id,
                'app_id' => $app_id,
                'assign_status' => 1,
            ];
            
            if($isAssginRM == true && $userData != null && isset($userData) && $userData->id > 0) {
                $shareCaseInfo['role_level'] = config('b2c_common.RM_ROLE_LEVEL');
                $shareCaseInfo['to_id'] = $userData->id;
            }
            
            $shareapp_id = $this->application->saveShareCaseInfo($shareCaseInfo);
            
            if($shareapp_id > 0 && $isAssginRM == true && $userData != null && isset($userData) && $userData->id > 0) {
                $customerData = $this->userRepo->find((int) $user->id);
                $arrData['app_id'] = $app_id;
                $arrData['app_user_id'] = $user->id;               
                $arrData['customer_email'] = $customerData->email;               
                $arrData['to_email'] = $userData->email;              
                $arrData['comment'] = "Auto assignment of new application";               
                Event::fire("case.autoassigned", serialize($arrData));
            }
            return $shareapp_id;
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
        }
    }
    
    
    /**
     * Save share lead
     * 
     * @param int $user_id
     * @return type 
     */
    public function saveShareLeadInfo($user, $isAssginRM = false, $userData = null) {
        try {
            $shareLeadInfo = [
                'from_id' => $user->id,
                'to_id' => config('b2c_common.OWNER_ID'),
                'app_user_id' => $user->id,
                'assign_status' => 1,
            ];
            
            if($isAssginRM == true && $userData != null && isset($userData) && $userData->id > 0) {
                $shareLeadInfo['role_level'] = config('b2c_common.RM_ROLE_LEVEL');
                $shareLeadInfo['to_id'] = $userData->id;
            }
            
            $sharelead_id = $this->application->saveShareLeadInfo($shareLeadInfo);
            return $sharelead_id;
        } catch (\Exception $ex) {
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
            //$otp = $this->otpRepo->getNewOtp();
            $otp = '654321';
            $arrUser["otp"] = $otp;
            $arrUser["otp_status"] = "Signup OTP";
            $arrUser["user_id"] = $user->id;
            $arrUser["email"] = $email;
            $arrUser["password"] = bcrypt($pass);
            if (!empty($otp)) {
                /* temp purpose user value store to session */
                $user_authentication = $this->userRepo->checkAuthenticateUser($user->id);
                if($user_authentication==false){
                $this->otpRepo->deactivateOtp($user->id);
                $otpUser = array();
                $otpUser["user_id"] = $user->id;
                $otpUser["otp"] = $otp;
                $otpUser["is_active"] = 1;
                $otpUser["created_at"] = Helpers::getCurrentDateTime();
                $otpUser["updated_at"] = Helpers::getCurrentDateTime();
                $this->otpRepo->insertOtp($otpUser);
                $request->session()->put('otpUserId', $user->id);
                $request->session()->put('loginStatus', 5);  // 5 use for registration
                $request->session()->put('tempPassword', $pass);
                $request->session()->put('OTPScreen', time());

                /* Save password history */
                $userinfo = [];
                $userinfo["user_id"] = $user->id;
                $userinfo["password"] = $arrUser["password"];
                $userinfo["created_by"] = $user->id;
                $userinfo["updated_by"] = $user->id;
                $this->userRepo->savePassword($userinfo);
                //Event for sending code
                Event::fire("otp.sendotp", serialize($arrUser));
                }

            } else {
                return redirect()->route('login');
            }
        } catch (\Exception $ex) {
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
