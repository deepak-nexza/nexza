<?php

namespace App\Http\Controllers\Contracts\Traits;

use Helpers;
use Session;
use Illuminate\Http\Request;

trait LoginTrait
{
    /**
     * Set the timezone in the session which user has logged in with
     *
     * @param Request $request
     */
    protected function setClientTimeZone(Request $request)
    {
        // Get client timezone string from the post data
        //dd($request->request->get('ctz'));
        $clientTz = trim($request->request->get('ctz'));

        // If the value is not one of the member of the supported list, set the application's timezone
        if (! in_array($clientTz, timezone_identifiers_list())) {
            $clientTz = date_default_timezone_get();
        }

        // Set the timezone in the session
        $request->session()->put('user.timezone', $clientTz);
    }

    /**
     * Set a UUID in session.
     *
     * @param void
     * @return void
     */
    protected function setUUIDInSession()
    {
        Session::put('uuid', Helpers::createUuid());
    }

    /**
     * Forget the CSRF token in order to get the new one in next request.
     *
     * @param void
     * @return void
     */
    protected function forgetCSRFToken()
    {
        Session::forget('_token');
    }
}
