<?php

namespace App\Repositories\Libraries\Validations\Password;

use Auth;
use Hash;
use App\Repositories\Models\UserLastPassword;

class CheckPassword
{
    /**
     * Attribute being tested
     *
     * @var mixed
     */
    protected $attribute;

    /**
     * Value of the attribute
     *
     * @var mixed
     */
    protected $value;

    /**
     * Array of parameters passed to the value
     *
     * @var array
     */
    protected $parameters;

    /**
     * Validator instance
     *
     * @var mixed
     */
    protected $validator;
    /**
     * Class constructor
     *
     * @param mixed $attribute
     * @param Uploaded $value
     * @param array $parameters
     * @param mixed $validator
     */
    public function __construct($attribute, $value, $parameters, $validator)
    {
        $this->attribute = $attribute;
        $this->value = $value;
        $this->parameters = $parameters;
        $this->validator = $validator;
    }

    /**
     *  Check if password is valid
     */
    public function isValid()
    {
        $value = $this->value;
        $savedpassword = UserLastPassword::getAllPasswordForUser((int) Auth::user()->id);
        $arr = [];
        foreach ($savedpassword as $password) {
            $arr[] = $this->verifyPassword($value, $password->password);
        }
        return !in_array(0, $arr);
    }

    /**
     * Verifying A Password Against A Hash
     *
     * @param string $currentpassword
     * @param string $password
     * @return boolean
     */
    protected function verifyPassword($currentpassword, $password)
    {
        if (Hash::check($currentpassword, $password)) {
            return 0;
        } else {
            return 1;
        }
    }
}
