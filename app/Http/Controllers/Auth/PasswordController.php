<?php

namespace App\Http\Controllers\Auth;

use Input;
use Event;
use Illuminate\Events\Dispatcher;
use Helpers;
use Validator;
use Password;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use App\Http\Controllers\Controller;
use Hash;
use Session;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Http\Controllers\Contracts\Traits\ThrottleLoginTraits;
use App\Repositories\Contracts\Traits\UserSessionTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Repositories\User\UserInterface as UserInterface;


class PasswordController extends Controller
{
     /**
     * User repopsitory
     *
     * @var \App\B2c\Repositories\Entities\User\UserRepository
     */
    protected $userRepo;
    
    /**
     * session Interface
     *
     * @var App\B2c\Repositories\Contracts\UserInterface
     */
    protected $session;
    
    /**
     * session Interface
     *
     * @var App\B2c\Repositories\Contracts\UserInterface
     */
    protected $tokens;
    
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails,
        ThrottleLoginTraits,
        UserSessionTrait;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Password $passwords, UserInterface $userRepo, Session $session)
    {
        $this->userRepo = $userRepo;
        $this->passwords = $passwords;
        $this->session = $session;
        $this->tokens =  Password::getRepository() ;
        //$this->middleware('guest');
    }
    
    /**
     * Redirect after login
     *
     * @return string
     */
    public function redirectPath()
    {
        //return property_exists($this, 'redirectTo') ? $this->redirectTo : route('front_dashboard');
        return property_exists($this, 'loginPath') ? $this->loginPath : route('login');
    }
    
    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetForm($token = null, $id = null)
    {        
        if (is_null($token)) {
            throw new NotFoundHttpException;
        }
        
        //$ids = (int) \Scramble::decrypt($id);
        $ids = (int) \Crypt::decrypt($id);
        $user = $this->userRepo->find($ids);
        if(isset($user->email)) {
            $email = $user->email ;
            $count = $this->userRepo->checkTokenValidForUser($email, null);
            if($count == 0) { abort(400); }
        }
     
        
//        if (Hash::check('plain-text', $hashedPassword)) {
//            // The passwords match...
//        }
//        
//		$tokenStatus = null;
//        $request = request();
//        $userData = Helpers::getUserDataByToken($token, 0);
//        
//        if ($userData !== false) {
//            $request->request->add([ 'email' => $userData->email, 'username' => $userData->username]);
//        } else {            
//            $tokenStatus = 0;
//        }
//        
//        // Check token expiration on page open
//        $this->userRepo->checkTokenExpiration($token);
//
//        $tokenStatus = Helpers::checkTokenValid($token);
//        //dd($tokenStatus);
//        if ($tokenStatus > 0) {
//            // Update token date, when ap page is render
//            Helpers::updateOpenTokenDate($token, Helpers::getCurrentDateTime());
            return view('auth.passwords.reset')->with(['token' => $token, 'user_id' => $id]);
            
//        } else {
//            
//            $messages = new MessageBag;
//            $messages->add('expired_token_failed', trans('error_message.token_expired'));
//            return redirect(route('login'))->withErrors($messages)->withInput();
//        }
    }
    
    
    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLinkRequestForm()
    {
        if (\Auth::guest() === false) {
           return redirect(route('home'));
        }
        return view('auth.passwords.email');
    }
    
    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        
        $rules = array('email' => 'required|email|isvalidchar');
        $input = $request->all();
        $validator = Validator::make($input, $rules);
                if ($this->isUserActive($validator)) {
                //$validator->errors()->add('invalid', trans('messages.error.invalid_user'));
                return redirect()->back()->withErrors(['email' => trans('messages.error.invalid_user')]);
            }            
        
        if (\Auth::guest() === false) {
           return redirect(route('home'));
        }
        
        $credentials = $request->only('email');
        $user = $this->userRepo->getUserByEmail($credentials['email']);
        if($user) {
            $token = $this->tokens->create($user);
            $data['user_id'] = $user->id;
            $data['first_name'] = $user->first_name;
            $data['last_name'] = $user->last_name;
            $data['email'] = $user->email;
            $data['token'] = $token;
            \Event::dispatch("user.passwordrequested", serialize($data));
        }
        return redirect()->back()->with('status', trans('passwords.sent_front'));
    }
    
    /**
     * Check if user is Backend user & Active
     *
     * @param type $validator
     * @return boolean
     */
    protected function isBackendUser($validator)
    {
        $data = $validator->getData();
        $userinfo = $this->userRepo->getUserByEmail($data['email']);
        if (!empty($userinfo)) {
            if ($userinfo['user_type'] != config('b2c_common.USER_FRONTEND')) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Check if user is active
     *
     * @param array $validator
     * @return boolean
     */
    private function isUserActive($validator)
    {
        $data = $validator->getData();
        $userinfo = $this->userRepo->getUserByEmail($data['email']);
        if (!empty($userinfo)) {
            if ($userinfo['block_status'] == 1) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {   
        dd(2);
        try {
            
            if ($this->captchaCheck($request) == false) {
		   return redirect()->back()->withErrors(['captcha' => trans('messages.error.invalid_captcha')]);
		}
                
            $message = [
                'password.required' => trans('error_message.req_new_pass'),
                'password_confirmation.required' => trans('error_message.req_password_confirm'),
                'password.regex' => trans('error_message.pwcheck'),
                'password_confirmation.regex' => trans('error_message.cpwcheck'),
                'email.exists' => trans('passwords.token'),
                'password_confirmation.same' => trans('error_message.error_change_pass_match'),
            ];
            
            $rules = [              
                'token' => ['required'],
                'email' => ['required','email','exists:users','email'],
                'password' => ['required','regex:/^(?!.*(.)\1\1)(?=.*[A-Z])(?=.*[\\\~\!\@\#\$\%\^\&\*\(\)\[\]\{\}\<\>\'\;\.\?\:\"\`\|\/\+\-\_\=\,])(?=.*[a-z])(?=.*[0-9])[A-Za-z0-9\\\~\!\@\#\$\%\^\&\*\(\)\[\]\{\}\<\>\'\;\.\?\:\"\`\|\/\+\-\_\=\,]{8,50}$/'],
                'password_confirmation' => ['required','same:password','regex:/^(?!.*(.)\1\1)(?=.*[A-Z])(?=.*[\\\~\!\@\#\$\%\^\&\*\(\)\[\]\{\}\<\>\'\;\.\?\:\"\`\|\/\+\-\_\=\,])(?=.*[a-z])(?=.*[0-9])[A-Za-z0-9\\\~\!\@\#\$\%\^\&\*\(\)\[\]\{\}\<\>\'\;\.\?\:\"\`\|\/\+\-\_\=\,]{8,50}$/']                  
            ];
            
            $validator = Validator::make(
                $request->all(), $rules, $message
            );

            $credentials = $request->only(
                'email', 'password', 'password_confirmation', 'token', 'id'
            );
            
            $credentials['id'] = (int) \Crypt::decrypt($credentials['id']);		

            $userEmail = Helpers::getUserDetails(['id'=>$credentials['id']], ['email']);		
            if($userEmail['email'] != $request->email){		
                return redirect()->back()		
                    ->withInput($request->only('email'))		
                    //->withErrors(['email' => trans('messages.error.invalid_token')]);		
                    ->withErrors(['email' => trans('passwords.token')]);		
            }

            if ($validator->fails()) {
                return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
            }
            $user_id = $this->getValidUserId($credentials);
            if (!($user_id)) {
                return redirect()->back()
                        ->withInput($request->only('email'))
                        ->withErrors(['email' => trans('passwords.token')]);
            }

            if ($this->checkIfTokenIsInvalid($credentials) === false) {
                return redirect()->back()
                        ->withInput($request->only('email'))
                        //->withErrors(['email' => trans('messages.error.invalid_token')]);
                        ->withErrors(['email' => trans('passwords.token')]);
            }
            
            if ($this->trackUserPassword($credentials) === false) {
                return redirect()->back()
                        ->withInput($request->only('email'))
                        ->withErrors(['password' => trans('error_message.last_six_password_match',['pass'=> config('b2c_common.MAX_PASSWORD_ALLOWED')])]);
            }
            

            // Get user data by email
            $user = $this->userRepo->getUserByEmail($request->only('email'));

            $response = $this->broker()->reset(
                $credentials, function ($user, $password) {
                $user->password = bcrypt($password);
                $user->save();
            }
            );

            switch ($response) {
                case Password::PASSWORD_RESET:
                    $this->swapUserSession($user);
                      $this->savePassword($user_id, bcrypt($credentials['password']));
                    Event::fire("user.resetpassword", serialize(['email' => $credentials["email"]]));
                    Session::flash('message', trans('messages.password_changed_successfully'));
                    return redirect($this->redirectPath());

                default:
                    return redirect()->back()
                            ->withInput($request->only('email'))
                            ->withErrors(['email' => trans($response)]);
            }
        } catch (Exception $e) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }
    
    /**
     * Check if token is valid or not against email id
     *
     * @param array $usercredentials
     */
    protected function checkIfTokenIsInvalid($usercredentials)
    {
        $count = $this->userRepo->checkTokenValidForUser($usercredentials['email'], $usercredentials['token']);
        return ($count == 0) ? true : false;
    }
    
    /**
     * Check if user is frontend user
     *
     * @param array $credentials
     * @return integer | boolean
     */
    protected function getValidUserId($credentials)
    {
        $user_id = $this->userRepo->getUserIdByEmail($credentials['email'], config('b2c_common.USER_FRONTEND'));
        return $user_id ? $user_id : false;
    }
    
    /**
     * Track User Password
     *
     * @param array $input
     *
     */
    protected function trackUserPassword($input)
    {
        $user_id = $this->getValidUserId($input);
        $validuser = $this->isValid($user_id, $input['password']);
        if ($validuser) {
           return true;
        }
        return false;
    }
    
    /**
     * Check if password is valid
     *
     * @param array $user
     * @return type
     */
    protected function isValid($user_id, $user_password)
    {        
        $savedpassword = $this->userRepo->getAllPasswordForUser((int) $user_id);
        if ($savedpassword->isEmpty()) {
            return true;
        }
        $arr = [];
        foreach ($savedpassword as $password) {
            $arr[] = $this->verifyPassword($user_password, $password->password);
        }
        return !in_array(0, $arr);
    }
    
    /**
     * Verifying A Password Against A Hash
     *
     * @param string $currentpassword
     * @param string $password
     * @return boolean
     */
    protected function verifyPassword($currentpassword, $password)
    {
        if (Hash::check($currentpassword, $password)) {
            return 0;
        } else {
            return 1;
        }
    }
    
    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);
        $user->setRememberToken(Str::random(60));
        $user->save();
        event(new PasswordReset($user));
        $this->guard()->login($user);
    }
    
    /**
     * Save User Password
     *
     * @param array $user_id
     * @param string $password
     */
    protected function savePassword($user_id, $password)
    {
        $userinfo['user_id'] = $user_id;
        $userinfo['password'] = $password;
        $this->userRepo->savePassword($userinfo);
    }
}
