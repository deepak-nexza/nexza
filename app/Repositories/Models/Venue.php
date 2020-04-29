<?php

namespace App\Repositories\Models;

use App\Repositories\Factory\Models\BaseModel;
use App\Repositories\Entities\User\Exceptions\BlankDataExceptions;
use App\Repositories\Entities\User\Exceptions\InvalidDataTypeExceptions;

class Venue extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'nex_venue';

    /**
     * Custom primary key is set for the table
     *
     * @var integer
     */
    protected $primaryKey = 'event_id';

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
    protected $fillable = [ 'event_id','user_id',
            'category_id',
            'event_type', 
            'event_name', 
            'event_uid',
            'description', 
            'banner_image',
            'country_id',
            'state_id',
            'phone',
            'booking_amt', 
            'event_location', 
            'booking_space', 
            'start_date',
            'end_date', 
            'event_duration',
            'event_privacy', 
            'facebook', 
            'twitter', 
            'google', 
            'created_at',
            'updated_at', 
            'created_by', 
            'updated_by', 
            'total_views', 
            'total_reviews', 
            'status', 
            'terms',
            'is_active'
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
    public static function saveEvent($arrData,$id = null) 
        {  
            //Check Data is Array
            if (!is_array($arrData)) {
                throw new InvalidDataTypeExceptions(trans('error_message.send_array'));
            }
            $query = self::updateOrCreate(['event_id' => (int) $id], $arrData);
            return $query ? $query : '';

        }
        
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
    public static function updateEvent($arrData, $attr =[])
    {
        if (!is_array($attr)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        $state_update = self::where($attr)
                ->update($arrData);
        return ($state_update ?  $state_update : false);
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
    public static function getAllEvent($flag=false)
    {
        if($flag){ $comp = '<'; } else { $comp = '>'; }
        $arrCity = self::select('*')
                
            ->where('start_date',$comp,date('m/d/Y h:i:s a'))
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
    public static function getEventDetails($id)
    {   
        $returnData = self::select('*')->where('event_uid', $id)->first();
        return $returnData;
    }
    
    /**
     * Get All state data
     *
     * @return list
     */
    public static function getAllStateData()
    {
       $result =  self::select('id', 'state_name')
                ->where('is_active', config('b2c_common.ACTIVE'))
                ->pluck('state_name', 'id');
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
    public static function getStateData($state_id){
        $returnData = self::select('state_name')->where('id', $state_id)->first();
        return $returnData ? $returnData['state_name'] :false;
    }
}
