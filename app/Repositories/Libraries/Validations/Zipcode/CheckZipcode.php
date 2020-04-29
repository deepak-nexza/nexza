<?php

namespace App\B2c\Repositories\Libraries\Validations\Zipcode;

use App\B2c\Repositories\Models\Master\Zipcode;

class CheckZipcode
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
        $this->attribute  = $attribute;
        $this->value      = $value;
        $this->parameters = $parameters;
        $this->validator  = $validator;
    }

    /**
     *  Check if password is valid
     */
    public function isValid()
    {
        $value        = $this->value;
        $validzipcode = Zipcode::checkZipcodeExistance($value);
        return $validzipcode;
    }
}
