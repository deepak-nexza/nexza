<?php namespace App\Repositories\Models;

use App\Repositories\Factory\Models\BaseModel;

class PasswordReset extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'password_resets';

    /**
     * Custom primary key is set for the table null
     *
     * @var integer
     */
    protected $primaryKey = null;

    /**
     * Maintain created_at and updated_at automatically
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'token'
    ];

    /**
     * Check token is valid or not
     *
     * @param string $token
     * @return boolean
     *
     */
    public static function checkTokenValid($token)
    {
        $count = self::where('token', $token)->count();
        return $count;
    }

    /**
     * Check if token is valid or not against email id
     *
     * @param string $email
     * @param string $token
     * @return integer
     */
    public static function checkTokenValidForUserOld($email, $token)
    { 
        $count = self::where('token', $token)->where('email', $email)->count();
        return $count;
    }
    
   public static function checkTokenValidForUser($email = null, $token = null)
   {
       if($token != null && $email != null) {
           $count = self::where('token', $token)->where('email', $email);
       }
       
       else if($email != null) {
           $count = self::where('email', $email);
       }
       
       $count = $count->count();
       return $count;
   }
    
    /**
     * Update open at date w.r.t token.
     *
     * @param string $token
     * @param timestamp $open_date
     *
     * @return boolean
     *
     */
    public static function updateOpenTokenDate($token, $open_date)
    {
        $rowUpdate = self::where('token', $token)->update(['opened_at' => $open_date]);
        return $rowUpdate;
    }
    
    /**
     * Get password reset token info
     *
     * @param string $token
     * @return boolean
     *
     */
    public static function getPasswordResetTokenInfo($token)
    {

        $tokenData = self::select('*')->where('token', '=', $token)
                ->first();
        
        return ( $tokenData ? $tokenData : false );
    }
    
    /**
     * Delete a token record by token.
     *
     * @param  string  $token
     * @return void
     */
    public static function deletePasswordResetToken($token)
    {

        $tokenDelete = self::where('token', $token)->delete();

        return ( $tokenDelete ? true : false );
    }
    
    /**
     * get user data by token
     *
     * @param  string  $token
     * @return void
     */
    public static function getUserDataByToken($token, $user_type)
    {
        $userData = self::From('password_resets')
                            ->select('u.id', 'u.email', 'u.username')
                            ->join('users as u', 'u.email', '=', 'password_resets.email')
                            ->where('password_resets.token', $token)
                            ->where('u.user_type', $user_type)
                            ->first();

        return ( $userData ?  : false );
    }
}
