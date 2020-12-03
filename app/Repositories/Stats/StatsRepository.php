<?php
namespace App\Repositories\Stats;

use App\Repositories\Stats\StatsInterface as StatsInterface;
use App\User;
use Illuminate\Support\Facades\Request;
use App\Repositories\Models\Venue as venue;
use App\Repositories\Models\Booking as booking;
use App\Repositories\Models\Ticket as ticket;
use App\Repositories\Models\Candidate as candidate;
use App\Repositories\Models\Order as order;
use App\Repositories\Models\Payment as payment;
use App\Repositories\Models\Master\State as state;
use App\Repositories\Models\Master\City as city;
use App\Repositories\Models\Master\Eventype as Eventype;
use App\Repositories\Entities\User\Exceptions\BlankDataExceptions;
use App\Repositories\Entities\User\Exceptions\InvalidDataTypeExceptions;


class StatsRepository implements StatsInterface

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
 
        
    public static function getOrders() 
        {  
            return order::getAllOrders();
        }
        
    public static function getOrdersDetails($order , $event_id) 
        {  
            return candidate::getOrdersDetails($order , (int) $event_id);
        }
        
        public static function getCandidatesWithEvent($order , $event_id) 
        {  
            $candidates = candidate::getOrdersDetails($order , (int) $event_id);
            $data = StatsRepository::collectCandidateHTml($candidates);
            return $data;
        }
        
        public static function collectCandidateHTml($data) 
        {  
            $i = 0;
            $html = '';
            foreach($data as $key=>$val)
            {
                if($i%2==0) { $cls = 'odd'; } else { $cls = 'even'; }
                $html .= '<tr role="row" class="'.$cls.'">';
                $html .= '    <td class="sorting_1">'.$val['event_name'].'</td>';
                $html .= '    <td class="sorting_1">'.$val['title'].'</td>';
                $html .= '    <td class="sorting_1">'.$val['full_name'].'</td>';
                $html .= '   <td>'.$val['phone'].'</td>';
                $html .= '  <td>'.$val['email'].'</td>';
                $html .= '   <td>'.substr($val['address'],0,100).'</td>';
                $html .= '   <td>'.substr($val['message'],0,100).'</td>';
                $html .= '   <td>'.$val['created_at'].'</td>';
                $html .= ' </tr>';
                $i++;
            }
            return $html;
        }
        
         public static function paymentRequest($arr , $id) 
        {  
            $paymentRequest = payment::savePayment($arr , (int) $id);
        
        }
         public static function getpaymentRequest($user) 
        {  
            return payment::getpaymentRequest($user);
        
        }
}