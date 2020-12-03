<?php

namespace App\Repositories\Models;

use App\Repositories\Factory\Models\BaseModel;
use App\Repositories\Entities\User\Exceptions\BlankDataExceptions;
use App\Repositories\Entities\User\Exceptions\InvalidDataTypeExceptions;
use Auth;

class Payment extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'nex_user_payment';

    /**
     * Custom primary key is set for the table
     *
     * @var integer
     */
    protected $primaryKey = 'pay_id';

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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pay_id', 
        'user_id',
        'req_amt', 
        'req_date',
        'payment_status',
        'razorpay_status',
        'created_at',
        'updated_at',
        'updated_by',
        'created_by'
            ];

    /**
     * Multilingual column name
     *
     * @var string
     */
    public static $multilingual;
	


     /**
     * 
     * save event details
     * 
     * @param type $arrData
     * @param type $id
     * @return type
     * @throws InvalidDataTypeExceptions
     * 
     */
    public static function savePayment($arrData,$id = null) 
        {  
                //Check Data is Array
            if (!is_array($arrData)) {
                throw new InvalidDataTypeExceptions(trans('error_message.send_array'));
            }
            $query = self::updateOrCreate(['pay_id' => (int) $id], $arrData);
            return $query ? $query : '';

        }
        
    /**
     * scope to get state name and get state key
     *
     * @param type $query
     * @return type
     */
    public static function getpaymentRequest($user_id)
    {
        if (empty($user_id)) {
                throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
            }
            $payDetails = self::select('*')
             ->where('user_id',$user_id)
             ->where('payment_status',0)
            ->orderBy('pay_id','desc')
            ->get();
        return ($payDetails ? $payDetails : false);
        
    }
    
 

    /**
     * Set State Active or Inactive.
     *
     * @param integer $state_id
     * @param integer $status
     * @return type
     * @throws InvalidDataTypeExceptions
     */
    public static function updateCandidate($arrData, $attr =[])
    {
        if (!is_array($attr)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        $state_update = self::where($attr)
                ->update($arrData);
        return ($state_update ?  $state_update : false);
    }

 

    /**
     * Get All States of USA
     *
     * @return type
     */
    public static function getAllPayments($flag=false,$user_id)
    {
        $adminConfig = config('common.ADMIN_ID');
        if($flag){ $comp = '<'; } else { $comp = '>'; }
        $arrCity = self::select('*')
             ->where(function($query)use($user_id,$adminConfig){
                    if($user_id!=$adminConfig) {
                        $query->where('user_id',$user_id);
                    }
                })
            ->where('start_date',$comp,date('Y-m-d h:i:s a'))
            ->where('is_deleted','!=',1)
            ->orderBy('event_name', 'asc')
            ->get();
        return ($arrCity ? : false);
    }


    /**
     * Save State Details
     *
     * @param array $arrData
     * @param integer $id
     * @return type
     * @throws InvalidDataTypeExceptions
     * @throws BlankDataExceptions
     */
    public static function saveState($arrData, $id)
    {
        /**
         * Check array is not
         */
        if (!is_array($arrData)) {
            throw new InvalidDataTypeExceptions(trans('error_message.send_array'));
        }
        /**
         * Check Data is not blank
         */
        if (empty($arrData)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }
        $query = self::updateOrCreate(['id' => (int) $id], $arrData);
        return ($query->id ? : false);
    }
    
   
    
  
  
}
