<?php namespace App\Repositories\Contracts\Traits;

use Session;
use Carbon\Carbon;

trait UserSessionTrait
{
    /**
     * Swap a user session with a current one
     *
     * @param \App\B2c\Repositories\Models\User $user
     *
     * @return boolean
     *
     * @since 0.1
     */
    protected function swapUserSession($user)
    {
        if (!($user instanceof \App\Repositories\Models\User)) {
            return false;
        }

        $new_sessid = Session::getId(); //get new session_id after user sign in
        
        if ($user->last_session_id !== null) {            
            $last_session = Session::getHandler()->read($user->last_session_id); // retrive last session            
            if ($last_session) {
                Session::getHandler()->destroy($user->last_session_id);
            }
        }

        $user->last_visited_date = Carbon::now()->toDateTimeString();
        $user->last_session_id = $new_sessid;
        $user->save();

        return true;
    }
}
