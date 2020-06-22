<?php
namespace App\Http\Middleware;

use Auth;
use Closure;
use Request;
use Session;
use Redirect;
use App\B2c\Repositories\Libraries\CmLogger;
use Illuminate\Contracts\Auth\Guard;

class Authenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Route name excludes from redirection
     *
     * @var array
     */
    protected $excluded_routes = [
        'statelist',
        'login_password_reset',
        'login_password_update',
        'otp_authentication',
        'get_city_state_by_postcode',
        'get_state_postcode_by_city',
        
    ];

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {   
        $route = $request->route()->getName();
        if (!in_array($route, $this->excluded_routes) && $this->auth->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('/');
            }
        }
        // Add our Case Manager Activity tracking here
        $domain = $request->server('HTTP_HOST');
//        if(isset(\Auth::user()->block_status) && \Auth::user()->block_status == 1)
//        {
//            \Auth::logout();
//            return redirect()->route('login');
//        }

        return $next($request);
    }

    /**
     * Returns whether a use is required to update his/her password
     *
     * @return boolean
     */
    protected function passwordResetOnFirstLogin()
    {
        return (Auth::user()->is_password_set_onlogin === null);
    }
}