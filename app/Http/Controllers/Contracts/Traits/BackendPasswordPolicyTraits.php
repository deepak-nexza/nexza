<?php

namespace App\Http\Controllers\Contracts\Traits;

use Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;

trait BackendPasswordPolicyTraits
{


    /**
     * Track Login Attempts
     *
     * @param Request $request
     * @param array $user
     * @param array $error
     * @return string
     */
    public function loginAttempt($request, $user, $error, $credentials)
    {
        if ($this->maxLoginAttempts() / 2 >= $this->retriesLeft($request)) {
            $error = $this->getRetriesleft($this->retriesLeft($request));
        }

        if ($this->retriesLeft($request) == 0 && $user) {
            $this->userRepo->updateUser($user->id, ['block_status' => 1, 'block_type_id' => 3]);
            $this->fireBlockedEvent($user->username);
            Event::fire("user.backenddeactivateuser", serialize(['user_id' => $user->id, 'username' => $user->username, 'block_type_id' => 3]));
            $this->clearBackendLoginAttempts($request);
            $error = $this->getBlockedMessage();
        }

        return $error;
    }

    /**
     * Check Password Last Update date
     * and if greater then max allowed days
     * redirect to update password page.
     *
     * @param integer $user_id
     * @return type
     */
    public function trackUserPassword($user_id)
    {
        $userInfo = $this->userRepo->getLastRecord($user_id);
        if ($userInfo) {
            $created = new Carbon($userInfo->created_at);
            $now     = Carbon::now();
            ($created->diff($now->addDay(1))->days > config('b2c_common.PASSWORD_EXPIRATION_DAYS')) ? $this->userRepo->updateUser(
                (int) $userInfo->user_id,
                ['is_password_set_onlogin' => null]
            ) : '';
        } else {
            $this->userRepo->updateUser((int) $user_id, ['is_password_set_onlogin' => null]);
        }
    }

    /**
     * Get Retries Left Message
     *
     * @param integer $retries_left
     * @return string
     */
    protected function getRetriesleft($retries_left)
    {
        return trans('auth.retry', ['retry' => $retries_left]);
    }

    /**
     * Get User Blocked Message
     *
     * @return string
     */
    protected function getBlockedMessage()
    {
        return trans('auth.blocked');
    }

    /**
     * Fire Blocking Event
     *
     */
    protected function fireBlockedEvent($credentials)
    {
        Event::fire("user.login.blocked", serialize(['username' => $credentials]));
    }

    /**
     * Clear the login locks for the given user credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function clearBackendLoginAttempts(Request $request)
    {
        app(RateLimiter::class)->clear(
            $this->getThrottleKey($request)
        );
    }
}
