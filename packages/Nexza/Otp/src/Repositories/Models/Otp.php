<?php

namespace Nexza\Otp\Repositories\Models;
use Helpers;
use Illuminate\Support\Facades\Session;
use App\Repositories\Factory\Models\BaseModel;
use App\Repositories\Models\User as UserModel;


class Otp extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'nex_user_otp';

    /**
     * Custom primary key is set for the table
     *
     * @var integer
     */
    protected $primaryKey = 'id';

    /**
     * Maintain created_at and updated_at automatically
     *
     * @var boolean
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'otp',
        'is_active',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];

    /**
     * Check user OTP is valid or not.
     *
     * @return integer
     */
    public static function checkOtp($otp, $resend)
    {
        $count = 1;
        $record = self::where('otp', $otp)->where('user_id', (int) Session::get('otpUserId'))->orderBy("id", "desc")->first();
        return self::getOptStatus($record);
    }

    /**
     * Get the status of the OTP.
     *
     * @param \Nexza\Otp\Repositories\Models\Otp $record
     * @return integer
     */
    protected static function getOptStatus($record)
    {
        $user_otp_status = 0;
        $expiration_time = config('messages.expire_in');
        if (!empty($record)) {
            $status = $record->is_active;
            if ($status == 0) {
                $user_otp_status = 1;  // send 1 for inactive case
                return $user_otp_status;
            } elseif (strtotime($record->created_at) < strtotime("-" . $expiration_time . " minutes")) {
                $user_otp_status = 2;  // if otp code has been expired
                return $user_otp_status;
            }
            $user_otp_status = 3; // everthing is fine.
        }
        return $user_otp_status;
    }
    /**
     * Deactivate user's previous OTP.
     *
     * @param integer $userId
     *
     * @return boolean
     */
    public static function deactivateOtp($userId)
    {
        $result = self::where('user_id', (int) $userId)->where('is_active', 1)->update(['is_active' => 0]);
        return ($result ? true : false);
    }

    /**
     * Insert OTP
     *
     * @param  array $attributes
     *
     * @return mixed
    */
    public static function insertOtp(array $attributes, $user_id=null)
    {
        $otpInsert = self::updateOrCreate(['user_id' => (int) $user_id], $attributes);
        return ($otpInsert->id ?:false);
    }
}
