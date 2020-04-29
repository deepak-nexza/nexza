<?php

namespace App\Repositories\Models\Master;

use App\Repositories\Factory\Models\BaseModel;
use App\Repositories\Entities\User\Exceptions\BlankDataExceptions;
use App\Repositories\Entities\User\Exceptions\InvalidDataTypeExceptions;

class State extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'nex_mst_state';

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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','country_id', 'name', 'state_key', 'is_active'];

    /**
     * Multilingual column name
     *
     * @var string
     */
    public static $multilingual;
	
    /**
     * One-to-many relation with State
     *
     * @return json
     *
     * @since 0.1
     */
    public function cities()
    {
        return $this->hasMany('App\B2c\Repositories\Models\Master\City');
    }

    /**
     * Get all cities w.r.t. a state id
     *
     * @param integer $state_id state id
     *
     * @return string city data
     *
     * @since 0.1
     *
     * @author Rajeev Sharma
     */
    public static function getAllCities($state_id)
    {

        /**
         * Check id is not an integer
         */
        if (!is_int($state_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }


        $list = self::find((int) $state_id);

        /**
         * Check Data is not blank
         */
        if (empty($list)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }

        $arrCity = $list->cities()->where('is_active', config('b2c_common.ACTIVE'))->orderBy('name', 'asc')->lists("name", "id");

        $arrCities = [];
        $index = 0;
        foreach ($arrCity as $varSI => $varSIName) {
            $arrCities[$index]["name"] = ucwords(strtolower($varSIName));
            $arrCities[$index]["city_id"] = $varSI;
            $index++;
        }
        return ($arrCities ? : false);
    }

    /**
     * Get City list By State ID
     *
     * @param integer $state_id
     * @return mixed array | false
     * @throws InvalidDataTypeExceptions
     * @throws BlankDataExceptions
     */
    public static function getAllCityByState($state_id)
    {

        /**
         * Check id is not an integer
         */

        if (!is_int($state_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }
        $list = self::find((int) $state_id);
        /**
         * Check Data is not blank
         */
        if (empty($list)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }
        $arrCity = $list->cities()->where('is_active', config('b2c_common.ACTIVE'))->orderBy('state_name', 'asc')->lists("state_name", "id");
        return ($arrCity ? : false);
    }

    /**
     * Get country w.r.t. a state.
     * Inverse relation against the Country-State relation
     *
     * @return string
     *
     * @since 0.1
     */
    public function country()
    {
        return $this->belongsTo('App\Repositories\Models\Master\Country');
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
    public static function getAllStates()
    {
        $arrCity = self::select('name as statename', 'is_active as stateactive', 'id as stateid')
            ->where('is_active', 1)
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
    public static function stateDetails($id)
    {   
        $returnData = self::select('*')->where('id', $id)->first();
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
    public static function getStateData($country_id){
        $returnData = self::select('name','id')->where('country_id', $country_id)->where('is_active', 1)->get();
        return $returnData ? $returnData :false;
    }
}
