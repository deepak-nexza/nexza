<?php

namespace App\Repositories\Models;

use App\Repositories\Factory\Models\BaseModel;
use App\Repositories\Entities\User\Exceptions\BlankDataExceptions;
use App\Repositories\Entities\User\Exceptions\InvalidDataTypeExceptions;

class Order extends BaseModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'nex_event_order';

    /**
     * Custom primary key is set for the table
     *
     * @var integer
     */
    protected $primaryKey = 'order_id';

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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id', 
        'order_amt',
        'transaction_id',
        'payment_response',
        'status', 
        'session_id',
        'Payment_frm_gtwy',
        'methods',
        'gatway_order_id',
        'email',
        'phone',
        'created_at',
        'updated_at',
        'updated_by',
        'created_by',
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
    public static function saveOrder($arrData,$id = null) 
        {  
            //Check Data is Array
            if (!is_array($arrData)) {
                throw new InvalidDataTypeExceptions(trans('error_message.send_array'));
            }
            $query = self::updateOrCreate(['order_id' => (int) $id], $arrData);
            return $query ? $query : '';

        }
        
    /**
     * Set State Active or Inactive.
     *
     * @param integer $state_id
     * @param integer $status
     * @return type
     * @throws InvalidDataTypeExceptions
     */
    public static function updateOrder($arrData, $attr =[])
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
    public static function getOrder($arr)
    {
        if (!is_array($arr)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }
        $arrData = self::select('*')
                ->where($arr)
            ->first();
        return ($arrData ? $arrData : false);
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
        $result = self::select('nex_venue.*','nex_user.*','state.name as statname','state.id as state_id','ticket.ticket_id')
        ->leftjoin('nex_user', 'nex_user.id', '=', 'nex_venue.user_id')
        ->leftjoin('nex_mst_state as state', 'state.id', '=', 'nex_venue.state_id')
        ->leftjoin('nex_event_ticket as ticket', 'ticket.event_id', '=', 'nex_venue.event_id');
        $result->where('nex_venue.status', 1 );
        $result->where('event_uid', $id);
        $result  = $result->first();
        return $result;
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
    
     /**
     * Get All States of USA
     *
     * @return type
     */
    public static function getAllEventWithDetails($id=null)
    {
        
        $result = self::select('*')
            ->where('event_privacy', 1 )
            ->where('status', 1 )
                ->where(function($query)use($id){
                    if(!empty($id)) {
                        $query->where('event_uid', $id);
                    }
                })
            ->orderBy('event_name', 'asc')
            ->get();
        return ($result ? $result : false);
    }
     /**
     * Get All States of USA
     *
     * @return type
     */
    public static function getEventData($id)
    {
        
        $result = self::select('*')
            ->where('event_privacy', 1 )
            ->where('status', 1 )
                ->where(function($query)use($id){
                    if(!empty($id)) {
                        $query->where('event_uid', $id);
                    }
                })
            ->orderBy('event_name', 'asc')
            ->get();
        return ($result ? $result : false);
    }
    
     /**
     * Get All States of USA
     *
     * @return type
     */
    public static function searchEvent($attr)
    {
         /**
         * Check Data is Array
         */
        $data = [];
        $rowperpage = config('common.DATA_LIMITER');
        $row = !empty($attr['row'])?$attr['row']:0;
        $result = self::select('nex_venue.*','emst.name as event_type','state.name as statname')
                ->leftjoin('nex_mst_event_type as emst', 'nex_venue.event_type', '=', 'emst.id')
                ->leftjoin('nex_mst_state as state', 'state.id', '=', 'nex_venue.state_id');
       
        if(!empty($attr['state_id'])) {
            $result->where('nex_venue.state_id', $attr['state_id']);
        }
        if(!empty($attr['event_type'])) {
            $result->where('nex_venue.event_type', $attr['event_type']);
        }
         if(!empty($attr['event_status'])){ 
            $arrField = $attr['event_status'];
                $result->where(function($query)use($arrField){
                    if(in_array(1,$arrField)){
                    $query->orwhere('nex_venue.start_date','>=',date('Y-m-d h:i:s a'));
                }
               if(in_array(2,$arrField)){
                    $query->orwhere('nex_venue.end_date','<=',date('Y-m-d h:i:s a'));
                }
               
            });
        } 
        
//        if(!empty($attr['event_status']) && n_array(1){ 
//            $result->where('nex_venue.start_date','>=',date('Y-m-d h:i:s a'));
//        } 
//        if(!empty($attr['event_status']) && $attr['event_status']==2){ 
//            $result->where('nex_venue.end_date','<=',date('Y-m-d h:i:s a'));
//        } 
        
        $result->where('nex_venue.event_privacy', 1 );
        $result->where('nex_venue.status', 1 );
        $resultCount = $result->count();
        $result = $result->offset($row)->limit($rowperpage)->orderBy('nex_venue.event_id', 'DESC')->get();
        $data = [$result,$resultCount];
        return ($result ? $data:false);
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
    public static function delEventdata($eid){
         $delStatus = self::where(['event_id'=>$eid])
                ->update(['is_deleted'=>1]);
        return $delStatus ? $delStatus :false;
    }
    
}