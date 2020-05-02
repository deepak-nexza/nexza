<?php

namespace App\Repositories\Models;

use App\Repositories\Factory\Models\BaseModel;
use App\Repositories\Entities\User\Exceptions\BlankDataExceptions;
use App\Repositories\Entities\User\Exceptions\InvalidDataTypeExceptions;

class Ticket extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'nex_event_ticket';

    /**
     * Custom primary key is set for the table
     *
     * @var integer
     */
    protected $primaryKey = 'ticket_id';

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
    protected $fillable = [ 'ticket_id', 'event_id', 'user_id', 'title', 'amt_per_person', 'type', 'ticket_duration', 'booking_space', 'start_date', 'end_date', 'message', 'nexza_amt', 'gatway_amt', 'nexza_per', 'gateway_per', 'customer_total', 'tnc', 'is_active', 'created_at', 'updated_at', 'updated_by', 'created_by'
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
    public static function saveEventTicket($arrData,$id = null) 
        {  
            //Check Data is Array
            if (!is_array($arrData)) {
                throw new InvalidDataTypeExceptions(trans('error_message.send_array'));
            }
            $query = self::updateOrCreate(['ticket_id' => (int) $id], $arrData);
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
    public static function getAllEvent($flag=false,$user_id)
    {
        
        if($flag){ $comp = '<'; } else { $comp = '>'; }
        $arrCity = self::select('*')
                
            ->where('user_id',$user_id)
            ->where('start_date',$comp,date('Y-m-d h:i:s a'))
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
    public static function getEventListWithUid($event_id=null,$user_id=null)
    {   
        

         $result =  self::select('nex_event_ticket.*','v.event_uid','v.event_id','v.event_name')
                    ->leftJoin('nex_venue as v', 'v.event_id', '=', 'nex_event_ticket.event_id')
                    ->where('v.status', 1)
                        ->where(function($query) use ($event_id) {
                            if(!empty($event_id)){
                              $query->where('v.event_id', $event_id);
                            }
                          })
                    ->where(function($query) use ($user_id) {
                        if(!empty($user_id)){
                        $query->where('v.user_id', $user_id);
                        }
                      })->get();
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
    public static function getTicketDetails($arrTr=[]){
        $returnData =  self::select('nex_event_ticket.*','v.event_uid','v.event_id','v.event_name')
                    ->leftJoin('nex_venue as v', 'v.event_id', '=', 'nex_event_ticket.event_id')
                    ->where('v.status', 1)
                    ->where('nex_event_ticket.ticket_id', $arrTr['ticket_id'])
                    ->where('nex_event_ticket.user_id', $arrTr['user_id'])->first();
        return $returnData ? $returnData :false;
    }
}
