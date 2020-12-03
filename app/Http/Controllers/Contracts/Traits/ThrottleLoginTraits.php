<?php

namespace App\Http\Controllers\Contracts\Traits;

use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Lang;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use App\B2c\Repositories\Models\UserTempPassword as UserTempPassword;

trait ThrottleLoginTraits
{

    use ThrottlesLogins;

    /**
     * Increment the login attempts for the user.
     *
     */
    public function incrementLoginAttempts($request)
    {
        app(RateLimiter::class)->hit(
            $this->getThrottleKey($request),
            $this->decayMinutes
        );
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendLockoutResponse($request)
    {
        $seconds = app(RateLimiter::class)->availableIn(
            $this->getThrottleKey($request)
        );

        return redirect()->back()
                ->withInput($request->only($this->loginUsername(), 'remember'))
                ->withErrors([
                    $this->loginUsername() => $this->getLockoutErrorMessage($seconds),
                ]);
    }

    /**
     * Get the login lockout error message.
     *
     * @param  int  $hours
     * @return string
     */
    protected function getLockoutErrorMessage($seconds)
    {
        return Lang::has('auth.throttle')
            ? Lang::get('auth.throttle', ['seconds' => $seconds])
            : 'Your Login has been temporarily blocked for next '.seconds.' seconds.';
    }

    /**
     * Check If user account is blocked
     *
     * @param object $request
     * @param object $user
     * @return boolean
     */
    public function isAccountBlocked($request, $user)
    {
        if (!empty($user) && $user->block_status == 1) {
            return true;
        }

        return false;
    }

    /**
     * Redirect User with error message
     *
     * @param object $request
     * @param string $loginUsername
     * @return type
     */
    public function redirectWithError($request, $loginUsername)
    {
        return redirect($this->loginPath())
                ->withInput($request->only($loginUsername))
                ->withErrors($this->getLockedErrorMessage());
    }

    /**
     * Expiry time of wrong attempt in cache.
     *
     * @return int
     */
    protected function decayMinutes()
    {
        return property_exists($this, 'decayMinutes') ? $this->decayMinutes : 1;
    }

    /**
     * Get locked error message
     *
     * @return string
     */
    protected function getLockedErrorMessage()
    {
        $error_message = trans('auth.blocked');
        return $error_message ? $error_message : 'You have been locked Permanently.';
    }
    /**
     * Check If user temp passord is changed
     *
     * @param object $request
     * @param object $user
     * @return boolean
     */
    public function isTempPasswordExpired($request, $user)
    {
        if (!empty($user)) {
            return UserTempPassword::isTempPasswordExpired((int) $user->id);
        }

        return false;
    }
    
    
    protected function isUsingThrottlesLoginsTrait()
    {
        return in_array(
            ThrottlesLogins::class, class_uses_recursive(get_class($this))
        );
    }
}
