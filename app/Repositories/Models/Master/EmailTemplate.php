<?php

namespace App\Repositories\Models\Master;

use Illuminate\Support\Facades\Cache;
use App\Repositories\Factory\Models\BaseModel;
use App\Repositories\Entities\User\Exceptions\BlankDataExceptions;

class EmailTemplate extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'nex_mst_email_template';

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
     * Maintain created_by and updated_by automatically
     *
     * @var boolean
     */
    public $userstamps = true;

    /**
     * Maintain  cache key 
     *
     * @var string
     */
    public static $cacheKey = 'EmailTemplate';
     
    /**
     * Multilingual column name
     *
     * @var string
     */
    public static $multilingual;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $fillable = [
        'email_cat_id',
        'mail_title',
        'en_mail_subject',
        'en_mail_body',
        'en_sms_body',
        'fr_mail_subject',
        'fr_mail_body',
        'fr_sms_body',
        'template_type',
        'is_active',
        'type',
        'reciepient_cc',
        'reciepient_bcc',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];

    /**
     * Scopes for active System Template list
     *
     * @param string $query
     * @param string $type
     * @return type
     */
    public function scopeActive($query, $type)
    {
        return $query->where('is_active', $type);
    }

    /**
     * Get email template w.r.t. email title
     *
     * @param string $mail_title
     *
     * @return array mail data
     *
     * @since 0.1
     */
    public static function getEmailTemplate($mail_title)
    {
        /**
         * Check Data is not blank
         */
        if (empty($mail_title)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }
        
        $arrMailData = self::select('*');
        $arrMailData = $arrMailData->active(1)
                ->where('mail_title', $mail_title)->first();

        return ($arrMailData ? : false);
    }

    /**
     * Get System generated all email template
     *
     * @return array or boolean
     */
    public static function getSystemTemplatesList()
    {
        $arrMailData = self::from('mst_email_template as eml_temp')->select('eml_temp.*',  'eml_temp.id AS templ_id')
            ->leftjoin('mst_email_category as eml_cat', 'eml_temp.email_cat_id', '=', 'eml_cat.id')
            ->where('template_type', config('b2c_common.SYSTEM_GENERATED'))
            ->get();
        return ($arrMailData ? : false);
    }

    /**
     * Get User defined all email template
     *
     * @return array or boolean
     */
    public static function getUserTemplatesList()
    {
        $arrUserMailData = self::active(config('b2c_common.ACTIVE'))
            ->where('template_type', config('b2c_common.USER_DEFINED'))
            ->where('created_by', \Auth::user()->id)
            ->get();
        return ($arrUserMailData ? $arrUserMailData : false);
    }

    /**
     * Save or update system Templates
     *
     * @param type $trigger_id
     * @param type $arrData
     * @throws InvalidDataTypeExceptions
     */
    public static function saveSystemTemplates($email_cat_id, $arrData = [])
    {
        //Check email category id is int
        if (!is_int($email_cat_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        //Check $arrData is an array
        if (!is_array($arrData)) {
            throw new InvalidDataTypeExceptions(trans('error_message.send_array'));
        }

        $rowUpdate = self::where('email_cat_id', $email_cat_id)
            ->where('template_type', config('b2c_common.SYSTEM_GENERATED'))
            ->update($arrData);
        return ($rowUpdate ? (int) $email_cat_id : false);
    }

    /**
     * Get System Template by template Category Id
     *
     * @param Integer $template_cat_id
     * @return array
     * @throws InvalidDataTypeExceptions
     */
    public static function getTemplateById($template_cat_id)
    {
        // check $template_cat_id is int
        if (!is_int($template_cat_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        $template = self::active(config('b2c_common.ACTIVE'))
            ->where('email_cat_id', $template_cat_id)
            ->where('template_type', config('b2c_common.SYSTEM_GENERATED'))
            ->first();

        return ($template ? $template : false);
    }

    /**
     * Get System Template by template Category Id
     * irrespective of status
     *
     * @param Integer $template_cat_id
     * @return type
     * @throws InvalidDataTypeExceptions
     */
    public static function getTemplate($template_cat_id)
    {
        // check $template_cat_id is int
        if (!is_int($template_cat_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

            $template = self::where('id', $template_cat_id)
            //->where('template_type', config('b2c_common.SYSTEM_GENERATED'))
            ->first();
        return ($template ? $template : false);
    }

    /**
     * Get User Email Template by template Id
     *
     * @param Integer $template_id
     * @return array
     * @throws InvalidDataTypeExceptions
     */
    public static function getUserTemplate($template_id)
    {
        // check $template_cat_id is int
        if (!is_int($template_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        $user_template = self::active(config('b2c_common.ACTIVE'))
            ->where('id', $template_id)
            ->where('created_by', \Auth::user()->id)
            //->where('template_type', config('b2c_common.user_defined'))
            ->first();

        return ($user_template ? $user_template : false);
    }

    /**
     * Save or Update User defined templates
     *
     * @param Integer $template_id
     * @param array $arrData
     * @return Integer or Boolean
     * @throws InvalidDataTypeExceptions
     * @throws BlankDataExceptions
     */
    public static function saveUserTemplates($template_id, $arrData = [])
    {
 
        // check data is array
        if (!is_array($arrData)) {
            throw new InvalidDataTypeExceptions(trans('error_message.send_array'));
        }

        // Check Data is not blank
        if (empty($arrData)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }

        $objtemplate = self::updateOrCreate(['id' => (int) $template_id], $arrData);
        return ($objtemplate->id ? : false);
    }

    /**
     * Deleting User defined templates
     *
     * @param Integer $tempalte_id
     * @return boolean
     * @throws InvalidDataTypeExceptions
     */
    public static function deleteUserTemplate($tempalte_id)
    {
        /**
         * Check id is not an Integer
         */
        if (!is_int($tempalte_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }
        $template_delete = self::where('id', (int) $tempalte_id)->delete();
        return $template_delete ? 1 : 0;
    }

    /**
     *
     * @param Integer $template_id
     * @return boolean
     */
    public static function updateTemplateStatus($template_id, $status)
    {
        if (!is_int($template_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        if ($status == 1) {
            $temp_status = 0;
        } else {
            $temp_status = 1;
        }
        $status_update = self::where('email_cat_id', (int) $template_id)
            ->update(['is_active' => $temp_status]);
        return ($status_update ? (int) $temp_status : false);
    }

    /**
     * Get User defined all email template according to logged in user id
     *
     * @return array or boolean
     */
    public static function getTemplateByloggedIn($logged_in_id, $template_type = null)
    {
        if (empty($template_type)) {
            $template_type = config('b2c_common.user_defined');
        }
        $dataArray = self::active(config('b2c_common.ACTIVE'))
                ->where('template_type', config('b2c_common.USER_DEFINED'))
                ->where('created_by', $logged_in_id)
                ->pluck('mail_title', 'id');
        return ($dataArray ? $dataArray : false);
    }

    /**
     * Get email template w.r.t. email title on lead share
     *
     * @param string $mail_title
     *
     * @return array mail data
     *
     * @since 0.1
     */
    public static function getLeadShareEmailTemplate($mail_title)
    {
        /**
         * Check Data is not blank
         */
        if (empty($mail_title)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }

        $arrMailData = self::active(config('b2c_common.ACTIVE'))
                ->where('mail_title', $mail_title)->first();

        return ($arrMailData ? : false);
    }
    
    /**
     * Get email template w.r.t. email title on case share
     *
     * @param string $mail_title
     *
     * @return array mail data
     *
     * @since 0.1
     */
    public static function getCaseShareEmailTemplate($mail_title)
    {
        /**
         * Check Data is not blank
         */
        if (empty($mail_title)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }

        $arrMailData = self::active(config('b2c_common.ACTIVE'))
                ->where('mail_title', $mail_title)->first();

        return ($arrMailData ? : false);
    }
    
    /**
     * get email templates
     *
     * @param array $attributes
     * @return mixed
     */
    public static function getEmailTemplates($attributes = [])
    {

        if (empty($attributes)) {
            $result = self::select('*')->get();
        } else {
            $result = self::select('*')->where($attributes)->get();
        }

        return ( $result ? $result : null);
    }
    
     /**
     * Get all triggers
     *
     * @return array or boolean
     */
    public static function getAllEmailTemplateList()
    {
        if (Cache::has(self::$cacheKey)) {
            $result = Cache::get(self::$cacheKey);
           
        } else {
            $result = self::active(config('b2c_common.ACTIVE'))->pluck("mail_title", "id");
            Cache::forever(self::$cacheKey, $result);
        }
        return ($result ? $result : false);
    }
}
