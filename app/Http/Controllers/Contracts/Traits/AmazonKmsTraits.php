<?php

namespace App\Http\Controllers\Contracts\Traits;

use App\Repositories\Libraries\Storage\Crypt\AwsKms;

trait AmazonKmsTraits
{
    /**
     * KMS
     *
     * @var object
     */
    protected $kms;

    /**
     * FieldName in DB
     *
     * @var string
     */
    protected $fieldname = 'sin';

    /**
     * Create Class Object
     *
     * @return object
     */
    public function crypt()
    {
        $this->kms = new AwsKms();
        return $this->kms;
    }

    /**
     * Encrypt plain text
     *
     * @param string $plaintext
     * @return string
     */
    public function encrypt($plaintext)
    {

        $cryptic = $this->crypt()->encrypt($plaintext);
        return $cryptic;
    }

    /**
     * Decrypt encrypted text
     *
     * @param string $cryptic
     * @return string
     */
    public function decrypt($cryptic)
    {
        $plaintext = $this->crypt()->decrypt($cryptic);
        return $plaintext;
    }

    /**
     * Encrypt and Save Tin w.r.t Application ID
     *
     * @param string $plaintext
     * @param type $repository
     * @param integer $application_id
     * @return integer | boolean
     */
    public function encryptAndSave($plaintext, $repository, $owner_id)
    {
        $crypted = ($this->encrypt($plaintext) == false) ? null : $this->encrypt($plaintext);
        $dataArr[$this->fieldname] = $crypted;
        return $repository->saveSin($owner_id, $dataArr);
    }

    /**
     * Decrypt and provide decrypted Tin w.r.t Application ID
     *
     * @param type $repository
     * @param integer $application_id
     * @return string | boolean
     */
    public function decryptAndSupply($businessdata, $repository, $application_id, $owner_id)
    {       
        $businessdata[$this->fieldname] = $this->decrypt($repository->getSin($application_id, $owner_id));        
        return $businessdata;
    }
}
