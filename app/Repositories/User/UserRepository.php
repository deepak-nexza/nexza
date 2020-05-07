<?php
namespace App\Repositories\User;


use App\Repositories\User\UserInterface as UserInterface;
use App\Repositories\Models\User;

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
        return $this->user->deleteUser($id);
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
}