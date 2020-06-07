<?php

namespace App\Repositories\Models\Master;

use App\Repositories\Factory\Models\BaseModel;
use App\Repositories\Entities\User\Exceptions\BlankDataExceptions;
use App\Repositories\Entities\User\Exceptions\InvalidDataTypeExceptions;

class Eventype extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'nex_mst_event_type';

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
    public $timestamps = false;

    /**
     * Maintain created_by and updated_by automatically
     *
     * @var boolean
     */
    public $userstamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','name','status'];

    /**
     * Multilingual column name
     *
     * @var string
     */
    public static $multilingual;
	


    /**
     * scope to get state name and get state key
     *
     * @param type $query
     * @return type
     */
     public static function scopeStateName($query)
    {
        self::$multilingual = app()->getLocale();
        if(self::$multilingual == config('b2c_common.FRENCH_LOCALE')) {
            return $query->select('id', 'fr_state_name', 'state_key');
        }
        else {
           return $query->select('id', 'state_name', 'state_key'); 
        }
        
    }

    /**
     * Set State Active or Inactive.
     *
     * @param integer $state_id
     * @param integer $status
     * @return type
     * @throws InvalidDataTypeExceptions
     */
    public static function updateStatus($state_id, $status)
    {
        if (!is_int($state_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        $state_update = self::where('id', (int) $state_id)
                ->update(['is_active' => $status]);

        return ($state_update ? (int) $status : false);
    }

    /**
     * Check if state is already present
     *
     * @param string $state_name
     * @return type
     */
    public static function isStateAvailable($state_name)
    {
        $returnData = self::where('name', $state_name)->count();
        return $returnData;
    }

    /**
     * Get All States of USA
     *
     * @return type
     */
    public static function getAllEvent()
    {
        $arrCity = self::select('*')
            ->orderBy('name', 'asc')
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
    public static function saveEventCategory($arrData, $id)
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
    
    /**
     * Get All States List
     *
     * @param void()
     *
     * @return object roles
     *
     * @since 0.1
     */
    public static function getStatesList()
    {   
        self::$multilingual = app()->getLocale();
        if(self::$multilingual == config('b2c_common.FRENCH_LOCALE')) {
            $arrStates =  self::select('id', 'fr_state_name as state_name');
        }else{
            $arrStates =  self::select('id', 'state_name');
        }
        $arrStates = $arrStates->where('is_active', config('b2c_common.YES'))
                ->orderBy('state_name', 'ASC')
                ->pluck('state_name','id');
        return ($arrStates ? : false);
    }
    
    /**
     * Get State Id By Name
     *
     * @param void()
     *
     * @return object roles
     *
     * @since 0.1
     */
    public static function getStateId($state_name)
    {   
        $returnData = self::where('state_name', $state_name)->first();
        return $returnData;
    }
    
    /**
     * Get All state data
     *
     * @return list
     */
    public static function getAllEventCat()
    {
       $result =  self::select('*')
                ->get();
        return ($result ? : false);
    }
    
    /**
     * Get State Name By id
     *
     * @param void()
     *
     * @return object roles
     *
     * @since 0.1
     */
    public static function getEventCatDetails($id){
        $returnData = self::select('*')->where('id', $id)->first();
        return $returnData ? $returnData :false;
    }
    
     /**
     * Set State Active or Inactive.
     *
     * @param integer $state_id
     * @param integer $status
     * @return type
     * @throws InvalidDataTypeExceptions
     */
    public static function deleteEventCategory($e_id)
    {
        if (!is_int($e_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        $data = self::where('id', (int) $e_id)
                ->delete();

        return ($data ? (int) $data : false);
    }
}
