<?php
namespace App\Repositories\Event;

use App\Repositories\Event\EventInterface as EventInterface;
use App\User;
use Illuminate\Support\Facades\Request;
use App\Repositories\Models\Venue as venue;
use App\Repositories\Models\Ticket as ticket;
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
    public static function getEventList($flag, $user_id) 
        {  
            return Venue::getAllEvent($flag, $user_id);
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
        
        
        public static function saveEventTicket($arrData,$id) 
        {  
            return ticket::saveEventTicket($arrData,$id);
        }
        
        public static function updateEventTicket($arrData,$id) 
        {  
            return ticket::updateEventTicket($arrData,$id);
        }
        
        public static function getEventListWithUid($event_id,$user_id) 
        {  
            $tableData = ticket::getEventListWithUid($event_id,$user_id);
            $data = EventRepository::eventTicketTable($tableData);
            return $data;
        }
        
        public static function eventTicketTable($data) 
        {  
            $i = 0;
            $html = '';
            foreach($data as $key=>$val)
            {
                if($i%2==0) { $cls = 'odd'; } else { $cls = 'even'; }
                $html .= '<tr role="row" class="'.$cls.'">';
                $html .= '    <td class="sorting_1">'.$val['event_uid'].'</td>';
                $html .= '    <td class="sorting_1">'.$val['event_name'].'</td>';
                $html .= '    <td class="sorting_1">'.$val['title'].'</td>';
                $html .= '   <td>'.$val['start_date'].'</td>';
                $html .= '  <td>'.$val['end_date'].'</td>';
                $html .= '   <td>'.$val['amt_per_person'].'</td>';
                if($val['is_active']==1) { $valSta = 'Active'; } else { $valSta = 'InActive'; } 
                $html .= '   <td>'.$valSta.'</td>';
                $html .= "   <td><a href=".route("update_event_ticket",['user_id'=>$val['user_id'],'ticket_id'=>$val['ticket_id']]).">Edit</a>/<a href='route()'>Delete</a></td>";
                $html .= ' </tr>';
                $i++;
            }
            return $html;
        }
        
        public static function getTicketDetails($arrData) 
        {  
            return ticket::getTicketDetails($arrData);
        }
}