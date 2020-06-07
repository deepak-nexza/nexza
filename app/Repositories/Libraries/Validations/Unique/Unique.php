<?php

namespace App\Repositories\Libraries\Validations\Unique;

use DB;
use App\Repositories\Models\User;
class Unique
{

    /**
     * Check if columns value are unique
     *
     * @param array $attribute
     * @param array $value
     * @param array $parameters
     * @return boolean
     */
    public static function isUnique($attribute, $value, $parameters, $validator)
    {
        // Get table name from first parameter
        $table = array_shift($parameters);
        // Build the query
        $query = DB::table($table);
        if (isset($validator->getData()['id'])) {
            $id       = self::isExist($table, $parameters, $validator);
            $is_valid = self::compareResult($id, $validator->getData()['id']);
            return $is_valid;
        } else {
            foreach ($parameters as $i => $field) {
                $query->where($field, $validator->getData()[$field]);
            }
            return ($query->count() == 0);
        }
    }

    /**
     * Check if record already exist
     *
     * @param string $table
     * @param array $parameters
     * @param array $validator
     * @return integer
     */
    public static function isExist($table, $parameters, $validator)
    {
        $query = DB::table($table);

        foreach ($parameters as $i => $field) {
            $query->where($field, $validator->getData()[$field]);
        }

        return $query->pluck('id');
    }

    /**
     * Compare Existing id and Edit id
     *
     * @param integer $existing_id
     * @param integer $edit_id
     * @return boolean
     */
    public static function compareResult($existing_id, $edit_id)
    {
        if ($existing_id == $edit_id) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Check if columns value are unique
     *
     * @param array $attribute
     * @param array $value
     * @param array $parameters
     * @return boolean
     */
    public static function emailExists($attribute, $value, $parameters, $validator)
    {
        // Get table name from first parameter
        $emailDtails = User::checkEmailUnique($value);
        // Build the query
        if(!empty($emailDtails) && $emailDtails->is_otp_authenticate==1){
            return false;
        }elseif(!empty($emailDtails) && $emailDtails->is_otp_authenticate==0){
            User::deleteUser($value);
            return true;
        }
        return true;
    }
    /**
     * Check if columns value are unique
     *
     * @param array $attribute
     * @param array $value
     * @param array $parameters
     * @return boolean
     */
    public static function isUniquePhone($attribute, $value, $parameters, $validator)
    {
        // Get table name from first parameter
        $emailDtails = User::checkIfPhone($value);
        // Build the query
        if(!empty($emailDtails->contact_number)){
            return false;
        }elseif(empty($emailDtails->contact_number)){
            return true;
        }
        return true;
    }
}
