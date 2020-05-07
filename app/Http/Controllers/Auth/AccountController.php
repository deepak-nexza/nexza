<?php

namespace App\Http\Controllers\Auth;

use Hash;
use Auth;
use Event;
use Helpers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\B2c\Repositories\Models\Password;
use App\Http\Requests\ChangePasswordRequest;
use App\B2c\Repositories\Contracts\ApplicationInterface;
use App\Http\Controllers\Contracts\Traits\ApplicationTraits;
use App\B2c\Repositories\Contracts\UserInterface as B2cUserRepoInterface;

class AccountController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Account Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles the account activity of users as well as their
      | validation and creation. By default this controller uses a trait to
      | provide this functionality without requiring any additional code.
      |
     */


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
     * Application repository
     *
     * @var App\B2c\Repositories\Contracts\ApplicationInterface
     */
    protected $application;
    
    
    public function __construct(Request $request, B2cUserRepoInterface $user, ApplicationInterface $application) {
        $userSession = Auth::user();
        if(!isset($userSession['id'])){
            redirect('/home');
        }
        $this->request = $request;
        $this->userRepo = $user;
        $this->application = $application;
    }
    
    /**
     * Change password view
     *
     * @return Illuminate\Contracts\View
     *
     * @since 0.1
     *
     */
    public function changePassword(Request $request)
    {
        $app_user_id = $request->session()->get('otpUserId');
        $appData = $this->application->getApplicationBusiness((int)$app_user_id);
        $app_id = $appData[0]['app_id'];
        return view('frontend.account.change_password')->with('app_id', $app_id);
    }
    
    /**
     * Change password update
     *
     * @return response
     *
     */
    public function passwordSave(ChangePasswordRequest $request)
    {
        try {
            $fetchedUser = Password::Search($request['old_password']);
            if ($fetchedUser) {
                $password = Hash::make($request['new_password']);
                $this->userRepo->updateUserPassword(\Auth::user()->id, ['password' => $password]);
                // Password Track
                $userinfo['user_id']  = \Auth::user()->id;
                $userinfo['password'] = $password;
                $this->savePassword($userinfo);

                $user_data = \Auth::user();
                Event::fire(
                    "user.changepassword",
                    serialize(['user_id' => $user_data->id, 'by_whom_id' => $user_data->id, 'email' => $user_data->email])
                );
                $message = trans('messages.password_changed');
                $redirect = route('logout');
            } else {
                $message = trans('messages.form.label.change_paas_match');
                $redirect = route('change_password');
            }

            return Helpers::ajaxResponse(true, $message, null, $redirect);
        } catch (Exception $e) {
            return Helpers::ajaxResponse(false, null, Helpers::getExceptionMessage($e), null);
        }
    }
    
    /**
     * Save Password
     *
     * @param array $userinfo
     */
    protected function savePassword($userinfo)
    {
        return $this->userRepo->savePassword($userinfo);
    }
}
