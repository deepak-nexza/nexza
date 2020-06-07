<?php
namespace App\Repositories\User;


use App\Repositories\User\UserInterface as UserInterface;
use App\Repositories\Models\User;
use App\Repositories\Models\PasswordReset;

class UserRepository  implements UserInterface

{

    public $user;


    function __construct(User $user) {

	$this->user = $user;

    }


    public function getAll()

    {

        return $this->user->getAll();

    }


    public function find($id)

    {

        return $this->user->findUser($id);

    }


    public function delete($id)

    {

        return $this->user->deleteUser($id);

    }
 /**
     * Validating and parsing data passed thos this method
     *
     * @param array $attributes
     * @param mixed $user_id
     *
     * @return New record ID that was added
     *
     * @since 0.1
     */
    public function saveUser($attributes, $user_id)
    {
        return User::saveUser($attributes, $user_id);
    }
     /**
     * Get OTP authentication by user ID
     *
     * @param integer $userlID
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function checkAuthenticateUser($userId)
    {
             $result = User::where('id', $userId)->where('is_otp_authenticate',1)->first();
             return $result ? : false;
    }
    
    public function getUserProfile($id)
    {
        return $this->user->findUser($id);
    }
    
    public function getUserDetail($id)
    {
        return User::getUserDetail( (int) $id);
    }
    
    public function updateUser($id , $attributes)
    {
        return User::updateUser( (int) $id ,$attributes);
    }
    
     /**
     * Get a user model by email
     *
     * @param string $email
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function getUserByEmail($email)
    {
        $result = User::where('email', $email)->first();

        return $result ? : false;
    }
    
     /**
     * Get a user details by email and password
     *
     * @param string $email and @password string
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function checkUserDetails($email, $password = "")
    {
        $result = User::where('email', $email)->first();
        $status = false;
        if (isset($result->password) && \Illuminate\Support\Facades\Hash::check($password, $result->password)) {
             $status =  $result;
        } else {
            $status = false;
        }
          return $status;
    }
    
     /**
     * check Email exist or not
     *
     * @param  string $email
     * @return boolean
     */
    public function checkEmailExistance($email, $old_email)
    {
        return User::checkEmailExistance($email, $old_email);
    }

    /**
     * Save User Temp Password
     *
     * @param  array $userInfoTemp
     * @return mixed
     */
    public function saveUserTempPassword($userInfoTemp, $user_id)
    {
        return UserTempPassword::saveUserTempPassword($userInfoTemp, $user_id);
    }

    /**
     * Check if token is valid or not against email id
     *
     * @param  string $email
     * @param  string $token
     * @return integer
     */
    public function checkTokenValidForUser($email, $token)
    {
        return PasswordReset::checkTokenValidForUser($email, $token);
    }

    /**
     * Check token expiration on page open
     *
     * @param type $token
     */
    public function checkTokenExiration($token)
    {
        $tokenData = Helpers::getPasswordResetTokenInfo($token);
        if (!empty($tokenData['opened_at'])) {
            $expirationTime = strtotime($tokenData['opened_at']) + config('auth.password.page_expire')*60;
            if ($expirationTime < time()) {
                Helpers::deletePasswordResetToken($token);
            }
        }
    }
    
    /**
     * Get UserID by Email and Type
     *
     * @param  string  $email
     * @param  integer $usertype
     * @return integer | boolean
     */
    public function getUserIdByEmail($emailOrEmplID)
    {
        return User::getUserIdByEmail($emailOrEmplID);
    }
    
    /**
     * Get All User Saved Passwords
     *
     * @param  integer $user_id
     * @return mixed
     */
    public function getAllPasswordForUser($user_id)
    {
        return UserLastPassword::getAllPasswordForUser((int) $user_id);
    }
}