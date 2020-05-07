<?php

use Nexza\Otp\Otp;

if (! function_exists('get_new_otp')) {
    /**
     * Get new OTP.
     *
     * @param void
     * @return integer
     */
    function get_new_otp()
    {
        return (new Otp())->getNewOtp();
    }
}
