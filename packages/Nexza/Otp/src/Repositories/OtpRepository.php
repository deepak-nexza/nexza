<?php

namespace Nexza\Otp\Repositories;

use Nexza\Otp\Repositories\Otp\OtpInterface;
use Nexza\Otp\Repositories\Models\Otp;

class OtpRepository implements OtpInterface
{

    /**
     * Returns a six digits random number.
     *
     * @param void
     * @return integer
     */
    public function getNewOtp()
    {
        return mt_rand(100000, 999999);
    }

    /**
     * Find the parameter by id
     *
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function checkOtp($radamOtpCode,$resend)
    {
        return Otp::checkOtp((int) $radamOtpCode, $resend);
    }

    /**
     * Deactivate user's old OTP.
     *
     * @param integer $id
     * @return boolean
     */
    public function deactivateOtp($uid)
    {
        return Otp::deactivateOtp((int) $uid);
    }

    /**
     * Insert user's OTP.
     *
     * @param integer $id
     * @return boolean
     */
    public function insertOtp(array $attributes)
    {
        return Otp::insertOtp($attributes);
    }
    
    /**
     * Deactivate user's previous OTP.
     *
     * @param integer $userId
     *
     * @return boolean
     */
    public static function updateOtpStatus($userId, $otp  ,$resp )
    {
        return Otp::updateOtp((int) $userId, $otp ,$resp);
    }
}
