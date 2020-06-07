<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Auth;
use Carbon\Carbon;
use Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest as profileRequest;
use App\Repositories\User\UserInterface as UserInterface;
use App\Repositories\Event\EventInterface as EventInterface;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $user;
    
    
    private $event;
    
    public function __construct(UserInterface $user,EventInterface $event)
    {
        $this->user = $user;
         $this->event = $event;
    }

   
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function openProfile()
    {
        $userProfile = $this->user->getUserProfile(Auth::id());
        return view('auth.open_profile',['user'=>$userProfile]);
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function updateProfile(profileRequest $request)
    {
        return view('auth.open_profile');
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function toSearchList(Request $request)
    {
        $locData = $request->get('location');
        return redirect()->route('search',['location'=>(int) $locData]);
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function search(Request $request)
    {
        $location = !empty($request->get('location'))?(int)$request->get('location'):'';
        $tobook = !empty($request->get('book_id'))?(int)$request->get('book_id'):'';
        
        $stateList = Helpers::stateList()->toArray();
        $eventlist = $this->event->getAllEventWithDetails(null);
        $evenType = Helpers::getAllEvent()->toArray();
        return view('eventfrontend.search',['elist'=>$eventlist,'stateList'=>$stateList,'eventType'=>$evenType,'location'=>$location,'tobook'=>$tobook]);
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function searchlist(Request $request)
    {
        $data = [];
        $arrdata = [];
        $drawCounter = $request->get('drawCounter') ;
        $data['state_id'] = $request->get('location');
        $data['event_type'] = $request->get('etype');
        $data['event_status'] = $request->get('event_status');
        $data['row'] = (int) $request->get('row');
        $eventlistData = $this->event->searchEvent($data);
        
        $html = '';
        if(count($eventlistData[0])>0){
            
        foreach($eventlistData[0] as $key=>$val)
        {
            $preg_replace = preg_replace('/\s+/', '-', $val['event_name']);
            $html .= '<div class="item" id="list_'.$val['event_uid'].'">';

                                $html .= '<div class="place-post list-style">';
                                        $html .= '<div class="place-post__gal-box">';
                                                $html .= '<a href="'.route('event_detail',['name'=>$preg_replace.'-'.$val['event_uid']]).'"><img class="place-post__image" src="'.asset('/').'Eventupload/'.$val['banner_image'].'" alt="place-image"></a>';
                                               $html .= '<span class="place-post__rating">₹ '.!empty($val['price'])?''.$val['price']:'Free'.'</span>';
                                                $html .= '<a class="place-post__like" href="#"><i class="fa fa-heart-o" aria-hidden="true"></i></a>';
                                        $html .= '</div>';
                                        $html .= '<div class="place-post__content">';
                                                $html .= '<p class="place-post__info">';
                                                       $html .= ' <i class="fa fa-clock-o" aria-hidden="true"></i>';
                                                       $html .= ' <span >'.\Carbon\Carbon::parse($val['start_date'])->format('j F, Y');
                                               $html .= ' </p>';
                                               
                                                $html .= '<h2 class="place-post__title">';
                                                     $html .= '   <a href="'.route('event_detail',['name'=>$preg_replace.'-'.$val['event_uid']]).'">'.$val['event_name'].'</a>';
                                                $html .= '</h2>';
                                                $html .= '<p class="place-post__description">';
                                                        $html .= '<span class="place-post__description-review">';
                                                           $html .= ' <a class="btn-default btn-default-red" href="'.route('event_detail',['name'=>$preg_replace.'-'.$val['event_uid'],'for'=>'booking']).'" >';
    									$html .= 'Book Now';
								$html .= '</a>';
                                                        $html .= '</span>';
                                                $html .= '</p>';
                                                $html .= '<p class="place-post__text">';
                                                   
                                                    $html .= substr(strip_tags($val['description']),0,100);
                                                $html .= '</p>';
                                                $html .= '<p class="place-post__address">';
                                                      $html .= '  <i class="fa fa-map-marker" aria-hidden="true"></i>';
                                                        $html .= '<span>'.$val['event_location'].'</span><span class="place-post__title" style="float:right;font-weight:bold">'.$val['statname'].'</span>';
                                                $html .= '</p>';
                                        $html .= '</div>';
                                $html .= '</div>';
                                 

                       $html .= ' </div>';
        }
        }else{
             $html .= '<div class="item" id="">';

                                $html .= '<div class="place-post list-style">';
                                        $html .= '<div class="place-post__content">';
                                                $html .= '<p class="place-post__info">';
                                                       $html .= ' <i class="fa fa-google " aria-hidden="true"></i>';
                                                       $html .= ' <span >No Result';
                                               $html .= ' </p>';
                                $html .= '</div>';
                                $html .= '</div>';
                       $html .= ' </div>';
              
        }

       return  [$html,$eventlistData[1]];
       
    }
    
    
       /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function eventDetailPage(Request $request)
    {
          $getsegment = $request->segment(2);
          $preg_split = preg_split('/\-/', $getsegment);
          $euid = '';
          foreach($preg_split as $key=>$val){
              $check = preg_match('/[A-Z]{3}[\d]/', $val,$match);
              if($check){ $euid=$preg_split[$key];  }
          }
          $tobook = !empty($request->get('for'))?$request->get('for'):'';
        $eventDetail = $this->event->getEventDetails($euid);
        
        
       $stateList = Helpers::stateList()->toArray();
        $evenType = Helpers::getAllEvent()->toArray();
        $open = Helpers::getEventCount(false,(int) $eventDetail['user_id']);
        $close = Helpers::getEventCount(true,(int) $eventDetail['user_id']);
        $ticketList = $this->event->getTicketList($eventDetail['event_id'],$eventDetail['user_id']);
                $currentDate = date_create(date('Y-m-d D H:i:s T'));
                $updated_date = date_create($eventDetail['start_date']);
                $diff_object = date_diff($currentDate, $updated_date);
                $tot_days = $diff_object->days;
        return view('eventfrontend.event_detail',
                ['book_id'=>$tobook,'eventDetail'=>$eventDetail,'stateList'=>$stateList,
                    'eventType'=>$evenType,'open'=>count($open),'close'=>count($close),'ticket_list'=>$ticketList]);
       
        }
}
