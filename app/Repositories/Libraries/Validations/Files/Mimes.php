<?php

namespace App\B2c\Repositories\Libraries\Validations\Files;

class Mimes
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
     * Allowed mime types in our application
     *
     * @var array
     */
    protected $mimeTypes = [
        'pdf' => ['application/pdf', 'application/x-download'],
        'xls' => ['application/excel', 'application/vnd.ms-excel', 'application/msexcel', 'application/vnd.ms-office'],
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'jpg' => ['image/jpeg', 'image/pjpeg'],
        'png' => ['image/png', 'image/x-png'],
        'txt' => ['text/plain'],
        'text' => ['text/plain'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
    ];

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
     * Check for valid file
     *
     * @return boolean
     */
    public function isValid()
    {
        $extension = strtolower($this->value->getClientOriginalExtension());

        if (! array_key_exists($extension, $this->mimeTypes)) {
            return false;
        }

        if (! in_array(strtolower($this->value->getMimeType()), $this->mimeTypes[$extension])) {
            return false;
        }

        return true;
    }
}
