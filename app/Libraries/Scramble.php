<?php

namespace App\Libraries;

use Crypt;
use Session;
use Illuminate\Contracts\Encryption\EncryptException;

class Scramble
{

    public function __construct()
    {
    }

    /**
     * Encrypt the given value with session binding.
     *
     * @param string $value
     *
     * @return string
     *
     * @throws \Illuminate\Contracts\Encryption\EncryptException
     */
    public static function encrypt($value)
    {
        if ($value === false) {
            throw new EncryptException('Could not encrypt the data.');
        }
        $manupulate_val = Session::getId()."##".config('app.key')."##".$value;       
        return Crypt::encrypt($manupulate_val);
    }

    /**
     * Decrypt the given value.
     *
     * @param  string  $decrypted
     * @return string
     *
     * @throws \Illuminate\Contracts\Encryption\DecryptException
     */
    public static function decrypt($decrypted)
    {
        if ($decrypted === false) {
            throw new DecryptException('Could not decrypt the data.');
        }

        $sess_id         = Session::getId();
        $decryptedStr    = Crypt::decrypt($decrypted);
        $decryptedStrArr = explode("##", $decryptedStr);
        
        if (is_array($decryptedStrArr) && $decryptedStrArr['0'] !== $sess_id) {
            abort(400);
        }
        
        if (is_array($decryptedStrArr) && $decryptedStrArr['1'] !== config('app.key')) {
            abort(400);
        }
         
        return $decryptedStrArr['2'];
    }
}
