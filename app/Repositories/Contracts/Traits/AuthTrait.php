<?php
namespace App\B2c\Repositories\Contracts\Traits;

use Auth;

trait AuthTrait
{
    /**
     * Get authenticated user data
     *
     * @return mixed user object when loged in | false if not
     *
     * @since 0.1
     */
    public function getAuthUserData()
    {
        return Auth::user() ? : false;
    }
}
