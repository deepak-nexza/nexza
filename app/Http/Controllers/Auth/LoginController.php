<?php

namespace App\Http\Controllers\Auth;

use Auth;
use Event;
use Session;
use Helpers;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Nexza\Otp\Repositories\Otp\OtpInterface;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Repositories\Contracts\Traits\UserSessionTrait; 
use App\Repositories\User\UserInterface  as B2cUserRepoInterface;

class LoginController extends Controller {

    use AuthenticatesUsers,
        UserSessionTrait;

    /**
     * User repository
     *
     * @var object
     */
    protected $userRepo;

    /**
     * OTP Repository.
     *
     * @var \Biz2Credit\Otp\Repositories\Otp\OtpInterface
     */
    protected $otpRepo;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * The number of seconds to delay further login attempts
     *
     * @see \Illuminate\Foundation\Auth\ThrottlesLogins
     * @var integer
     */
    protected $lockoutTime = 3600;

    /**
     * The number of minutes to keep user credential in cache
     *
     * @var integer
     */
    protected $decayMinutes = 3600;

    /**
     * Amount of bad attempts user can make
     *
     * @see \Illuminate\Foundation\Auth\ThrottlesLogins
     * @var integer
     */
    protected $maxAttempts = 4;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(B2cUserRepoInterface $user, OtpInterface $otpRepo) {
        $this->middleware('guest')->except('logout', 'checkLoggedInStatus');
        $this->middleware('guest')->except('logout');
        $this->userRepo = $user;
        $this->otpRepo = $otpRepo;
    }

    /**
     * Log user login activity
     *
     * @param object $user_data
     */
    protected function logUserLoginEvent() {
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

    /**
     * Show Login Page
     * 
     * @param Request $request
     * @return mixed
     */
    public function getLogin(Request $request) {
        /**
         * Track user login event
         */
        $token = $request->get('token');
        if ($token && ($user = $this->userRepo->getUserByTokenOnce($token))) {
            /**
             * Automatically logs in the user
             */
            Auth::loginUsingId($user->id, false);

            /**
             * Track user login event
             */
            $this->logUserLoginEvent();
            $defaultRedirect = route('front_dashboard');

            return redirect($defaultRedirect);
        }
        //Destroy OTP releated session
        if (Session::has('SendOTP')) {
            Session::forget('SendOTP');
        }
        //Destroy OTP releated session
        if (Session::has('otpUserId')) {
            Session::forget('otpUserId');
        }
        if (Session::has('OtpOnResend')) {
            Session::forget('OtpOnResend');
        }
        if (Session::has('OtpOnSubmit')) {
            Session::forget('OtpOnSubmit');
        }
        if (Session::has('RegLink')) {
            Session::forget('RegLink');
        }
        if (Session::has('resendOtp')) {
            Session::forget('resendOtp');
        }
        if (Session::has('WrongOtpCount')) {
            Session::forget('WrongOtpCount');
        }
        return view('auth.login');
    }

    /**
     * Set the timezone in the session which user has logged in with
     *
     * @param Request $request
     */
    public function setClientTimeZone(Request $request) {
        // Get client timezone string from the post data
        //dd($request->request->get('ctz'));
        $clientTz = trim($request->request->get('ctz'));

        // If the value is not one of the member of the supported list, set the application's timezone
        if (!in_array($clientTz, timezone_identifiers_list())) {
            $clientTz = date_default_timezone_get();
        }

        // Set the timezone in the session
        $request->session()->put('backend.user.timezone', $clientTz);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function postLogin(Request $request)
    {
        try {
            $rules = ['email' => 'required|email|isvalidchar', 'password' => 'required|isvalidchar'];
            $messages = [
                'email.required' => trans('error_message.login.valid_email_req'),
                'email.email' => trans('error_message.login.valid_email_format'),
                'password.required' => trans('error_message.login.password_required'),
            ];
            if (Session::has('error')) {
                Session::forget('error');
            }
            $input = Input::all();
            $validator = Validator::make($input, $rules, $messages);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator);
            }

            // the login attempts for this application. We'll key this by the username and
            // the IP address of the client making these requests into this application.
            if ($this->hasTooManyLoginAttempts($request)) {
                Event::fire("user.login.failed", serialize(['email' => Input::get('email')]));
                $this->fireLockoutEvent($request);
                return $this->sendLockoutResponse($request);
            }
            $credentials = $request->only('email', 'password');
            $user = $this->userRepo->getUserByEmail($credentials['email']);
            if ($this->isAccountBlocked($request, $user)) {
                return $this->redirectWithError($request, $credentials['email']);
            }
            // Check whether the email supplied is a valid user and has role of Customer
            $user = $this->userRepo->checkUserDetails($credentials["email"], $credentials["password"]);
            /* Confirm user registered otp code or not */
                if (!empty($user) && $this->attemptLogin($request) == true) {
                    $message = "Login Successfully";
//                    Helpers::trackUserActivity($message, $userData->id, $app_id = null);
                    $this->sendLoginResponse($request);
                    return redirect(route('home', ['user_id' => $user->id]));
                }else {
                        // Reflash the session data in case we are in the middle of a redirect 
                        Session::reflash('redirect');
                        // Redirect to the login page.
                        return redirect()->route('login')->withErrors(['Email' => 'Email','password' => 'Password invalid'])->withInput(Input::except('password'));
                    }
        } catch (\Exception $e) {
            dd($e->getMessage());
            if (empty($e->getMessage()) && $e->getStatusCode() == 400) {
                throw $e;
            } else {
                return redirect()->back()->withErrors(\Helpers::getExceptionMessage($e))->withInput();
            }
        }
    }

    /**
     * Get the path to the login route.
     *
     * @return string
     */
    public function loginPath() {
        return property_exists($this, 'loginPath') ? $this->loginPath : route('login');
    }

    /**
     * Return logged in front-end user status
     *
     * @param Request $request
     * @return json
     */
    public function checkLoggedInStatus(Request $request) {
        $result = $this->isValidRequest($request);
        if ($result === true) {
            return response()->json(
                            auth()->user() ?: 0, 200
            );
        }

        return $result;
    }

    /**
     * Validating Request
     *
     * @param Request $request
     */
    protected function isValidRequest(Request $request) {
        // Get client code
        $clientCode = $request->header('Client');

        if (empty($clientCode)) {
            
        }

        $clientToken = $request->header('Token');

        if (empty($clientToken)) {
            
        }

        try {
            //get api credential
            $arrCredential = $this->apiRepo->getClientApiCredential($clientCode);

            // If the client code was not in our database
            if (!$arrCredential) {
                
            }
        } catch (App\B2c\Repositories\Entities\Application\Exceptions\BlankDataExceptions $ex) {
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            
        }

        // Get client signature
        $sig_client = $request->header('Authorize');

        if (empty($sig_client)) {
            
        }

        // send disallow response (403)
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request) {
        $this->guard()->logout();

        //$request->session()->invalidate();

        return redirect(route('login'));
    }

    /**
     * After verify OTP login authenticate
     * @param Request $request
     * @param \App\Http\Controllers\DocumentInterface $document
     * @return type
     */
    public function loginAuthenticated(Request $request) {

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        $throttles = $this->isUsingThrottlesLoginsTrait();

        $userId = Session::get('otpUserId');
        $userData = $this->userRepo->getUserDetail($userId);

        $credentials = [
            "email" => $userData->email,
            "password" => Session::get('password')
        ];

        $user = $this->userRepo->checkUserDetails($credentials["email"], $credentials["password"]);
        // Proceed for next step
        $proceedNext = $user &&
                $this->userRepo->isFrontendUser($user) &&
                !$this->userRepo->isBlocked($user);

        $loggedInStatus = false;
        if ($proceedNext && Auth::attempt($credentials, false)) {
            $loggedInStatus = true;
        } elseif ($this->userRepo->isLegacyUser($credentials)) {
            $loggedInStatus = true;
            Auth::loginUsingId($user->id, false);
        }
        if ($loggedInStatus) {

            /**
             * Remove token_once if it was there. Just in case :)
             */
            $this->userRepo->removeTokenOnce($user->id);

            /**
             * Track user login event
             */
            $this->logUserLoginEvent();

            /**
             * Remove existing token
             */
            Session::put('is_resetpassword_authenticate', $user->is_resetpassword_authenticate);
            Session::forget('_token');
            Session::forget('password');
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
            if (Session::has('resendOtp')) {
                Session::forget('resendOtp');
            }
            if (Session::has('OTPScreen')) {
                Session::forget('OTPScreen');
            }

            /* After succesful otp status update. */
            /* Change OTP status */
            $this->otpRepo->deactivateOtp($user->id);


            /* User OTP verify */
            $arrOtpStatus = array("is_otp_authenticate" => 1);
            $this->userRepo->updateUser(Auth::user()->id, $arrOtpStatus);

            return $this->handleUserWasAuthenticated($request, $throttles);
        }


        /**
         * Create activity log for user login failed
         */
        Event::fire("user.login.failed", serialize(['email' => $credentials["email"]]));

        return redirect($this->loginPath())
                        ->withInput((array) $credentials["email"])
                        ->withErrors([
                            'email' => $this->getFailedLoginMessage(),
        ]);
    }

    /**
     * check whether account is blocked
     *
     * @param type $request
     * @param type $user
     * @return boolean
     */
    public function isAccountBlocked($request, $user) {
        if (!empty($user) && $user->block_status == 1) {
            return true;
        }
        return false;
    }

    /**
     * redirect with error
     *
     * @param type $request
     * @param type $loginUsername
     * @return type
     */
    public function redirectWithError($request, $loginUsername) {
        return redirect($this->loginPath())
                        ->withInput($request->only('email'))
                        ->withErrors([
                            //'email' => [trans('auth.blocked')],
                            'email' => [trans('auth.failed')],
        ]);
    }
    

}