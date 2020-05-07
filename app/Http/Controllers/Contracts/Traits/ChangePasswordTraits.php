<?php

namespace App\Http\Controllers\Contracts\Traits;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Lang;

trait ChangePasswordTraits
{


    /**
     * Determine if the user has too many failed change password attempts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function hasTooManyChangePasswordAttempts(Request $request)
    {
        $attempts = app(RateLimiter::class)->attempts(
            $this->getThrottleKey($request)
        );

        if ($attempts > $this->maxChangePasswordAttempts) {
            $this->clearChangePasswordAttempts($request);
            return true;
        }
    }

      

    /**
     * Increment the change password attempts for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return int
     */
    protected function incrementChangePasswordAttempts(Request $request)
    {
        app(RateLimiter::class)->hit(
            $this->getThrottleKey($request),
            $this->decayMinutes
        );
    }

    /**
     * Determine how many retries are left for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return int
     */
    protected function retriesLeft(Request $request)
    {
        $attempts = app(RateLimiter::class)->attempts(
            $this->getThrottleKey($request)
        );

        return $this->maxChangePasswordAttempts() - $attempts + 1;
    }


    /**
     * Clear the change password locks for the given user credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function clearChangePasswordAttempts(Request $request)
    {
        app(RateLimiter::class)->clear(
            $this->getThrottleKey($request)
        );
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function getThrottleKey(Request $request)
    {
         return mb_strtolower(Auth::user()->username).'|changepassword|'.$request->ip();
    }
}
