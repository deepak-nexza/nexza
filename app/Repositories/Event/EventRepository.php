<?php
namespace App\Repositories\Event;

use App\Repositories\Event\EventInterface as EventInterface;
use App\User;
use Illuminate\Support\Facades\Request;
use App\Repositories\Models\Venue as venue;
use App\Repositories\Models\Master\State as state;
use App\Repositories\Entities\User\Exceptions\BlankDataExceptions;
use App\Repositories\Entities\User\Exceptions\InvalidDataTypeExceptions;


class EventRepository implements EventInterface

{

    public $user;


    function __construct(User $user) {

	$this->user = $user;

    }


    public function getAll()

    {

        return $this->user->getAll();

    }


    public function find($id)

    {

        return $this->user->findUser($id);

    }


    public function delete($id)

    {

        return $this->user->deleteUser($id);

    }
    
    /**
     * 
     * save event details
     * 
     * @param type $arrDatagetEventList
     * @param type $id
     * @return type
     * @throws InvalidDataTypeExceptions
     * 
     */
    public static function saveEvent($arrData,$id) 
        {  
            return Venue::saveEvent($arrData,$id);
        }
        
    public static function updateEvent($arrData,$id) 
        {  
            return Venue::updateEvent($arrData,$id);
        }
        
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
    public static function getEventList($flag) 
        {  
            return Venue::getAllEvent($flag);
        }
        
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
    public static function getstatetList($state_id) 
        {  
            $statelist =   state::getStateData($state_id);
            $readyOption = '';
            if(!empty($statelist)) {
                 foreach ($statelist as $state) {
                       $readyOption .= "<option value='".$state['id']."'>".$state['name']."</option>";
                       }
               }else{
                   $readyOption .= "<option value=''>No result found</option>";
               }
            return $readyOption;
        }
        
     /**
     * 
     * get event details
     * 
     * @param type $arrData
     * @param type $id
     * @return type
     * @throws InvalidDataTypeExceptions
     * 
     */
    public static function getEventDetails($id) 
        {  
            return Venue::getEventDetails($id);
        }
    /**
     * 
     * get event details
     * 
     * @param type $arrData
     * @param type $id
     * @return type
     * @throws InvalidDataTypeExceptions
     * 
     */
    public static function stateDetails($id) 
        {  
        $stateDatta = state::stateDetails($id);
            $readyOption = '';
            if(!empty($stateDatta)) {
                       $readyOption .= "<option value='".$stateDatta['id']."' selected>".$stateDatta['name']."</option>";
                       }else { 
                   $readyOption .= "<option value=''>No result found</option>";
               }
            return $readyOption;
        }
}