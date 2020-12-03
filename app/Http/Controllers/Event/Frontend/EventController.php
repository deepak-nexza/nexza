<?php
namespace App\Http\Controllers\Event\Frontend;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest as eventRequest;
use App\Http\Requests\EventRequestForDesc as eventRequestForDesc;
use App\Http\Requests\EventTicketRequest as eventTicketRequest;
use Session;
use Helpers;
use Illuminate\Validation\Validator; 
use App\Repositories\Event\EventInterface as EventInterface;
use App\Repositories\Stats\StatsInterface as StatsInterface;
use Auth;
use App\Repositories\User\UserInterface as UserInterface;

class EventController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $event;
    private $stats;
    
    public function __construct(UserInterface $user,EventInterface $event,StatsInterface $stats)
    {
        $this->event = $event;
        $this->user = $user;
        $this->stats = $stats;
      $this->middleware('auth');
    }

     /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $open = $this->event->getEventList(false,(int) Auth::id());
        $Close = $this->event->getEventList(true,(int) Auth::id());
        return view('home',['close'=>$Close->count(),'open'=>(int) $open->count()]);
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function myaccount()
    {
        $open = Helpers::getEventCount(false,(int) (int) Auth::id());
        $close = Helpers::getEventCount(true,(int) (int) Auth::id());
        return view('eventfrontend.dashboard',['open'=>count($open),'close'=>count($close)]);
    }
    
     /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createEvent(Request $request)
    {
        $eventid = $request->get('event_id');
        $countryList = Helpers::countryList()->toArray();
        $stateList = Helpers::stateList()->toArray();
        
        $eventlist = Helpers::getAllEvent()->toArray();
        $mode = config('common.privacy_mode');
        $eventDetail = [];
        $cityList = [];
        if(!empty($eventid)){
        $eventDetail = $this->event->getEventDetailsByID($eventid);
        $cityList = Helpers::cityList($eventDetail->state_id?$eventDetail->state_id:null)->toArray();
        }
        return view('eventbackend.create_event',[
            'country_list'=>$countryList,
            'state_list'=>$stateList,
            'city_list'=>$cityList,
            'mode'=>$mode,
            'eventDetail'=>!empty($eventDetail)?$eventDetail:null,
            'eventid'=>!empty($eventid)?$eventid:null,
            'eventlist'=>$eventlist]);
    }
    
     /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function upcomingEvent()
    {
        $eventList = $this->event->getEventList(false,(int) Auth::id());
        return view('eventbackend.upcoming_event',['eventlist'=>$eventList]);
    }
    
     /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function pastEvent()
    {
        $eventList = $this->event->getEventList(true,(int) Auth::id());
        return view('eventbackend.past_event',['eventlist'=>$eventList]);
    }
    
     /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function eventTicket(Request $request)
    {
        $eventid = $request->get('event_id');
        $eventDetail = $this->event->getEventDetailsByID($eventid);
        $ticketDetails = $this->event->getAllEventWithUid($eventDetail['event_id']);
        $eventList = $this->event->getEventListWithArr(['event_status'=>Config('common.STATUS.Submitted')],(int) Auth::id());
        return view('eventbackend.event_ticket',['eventlist'=>$eventList,'ticketDetail'=>$ticketDetails,'eventDetail'=>$eventDetail]);
    }
    
     /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function updateEventTicket(Request $request)
    {
        $user_id = $request->get('user_id');
        $ticket_id = $request->get('ticket_id');
        $ticketDetails=[];
        if(!empty($ticket_id) && !empty($user_id)){
            $ticketDetails = $this->event->getTicketDetails(['ticket_id'=>$ticket_id,'user_id'=>$user_id,'edit'=>1]);
        }
        $eventDetail = $this->event->getEventDetailsByID($ticketDetails[0]['event_id']);
        $eventList = $this->event->getEventList(false,(int) Auth::id());
        return view('eventbackend.event_ticket',['eventlist'=>$eventList,'ticketDetail'=>$ticketDetails,'eventDetail'=>$eventDetail,'edit'=>1]);
    }
    
      /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function eventTicketlist(Request $request)
    {
        
        $ticket_ID = (int) $request->get('ticket_id');     
        $user_id = (int) $request->get('user_id'); 
        $ticketDetails=[];
        if(!empty($ticket_ID) && !empty($user_id)){
            $ticketDetails = $this->event->getTicketDetails(['ticket_id'=>$ticket_ID,'user_id'=>$user_id]);
        }
        $ticketDetails = !empty($ticketDetails)?$ticketDetails:null;
        $eventList = $this->event->getEventList(false,(int) Auth::id());
        return view('eventbackend.event_ticket_list',['eventlist'=>$eventList,'ticket_id'=>$ticket_ID,'user_id'=>$user_id,'ticketDetails'=>$ticketDetails]);
    }
      /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function eventdescription(Request $request)
    {
        $event_data = $request->get('event_data');     
        return view('eventbackend.event_description',['eventDetail'=>$event_data]);
    }
    
    public function saveEvent(eventRequest $request)
    {
        
        $data = [];
        try{
        $dateRange = $request->get('event_duration');
        $description = $request->get('description');
        $geteventRange = preg_split('/[\s][\-][\s]/',$dateRange);
        $eventDesc = Helpers::formatEditorData($description);
        $data['event_name'] = $request->get('event_name');
        $data['event_type'] = $request->get('event_type');
        $data['country_id'] = $request->get('country_id');
        $data['state_id'] = $request->get('state_id');
        $data['city_id'] = $request->get('city_id');
        $data['event_duration'] = $request->get('event_duration');
        $data['event_privacy'] = $request->get('event_privacy');
        $data['status'] = $request->get('status');
        $data['event_location'] = $request->get('event_location');
        $data['twitter'] = $request->get('twitter');
        $data['facebook'] = $request->get('facebook');
        $data['youtube'] = $request->get('youtube');
        $data['instagram'] = $request->get('instagram');
        
        $data['start_date'] = $geteventRange[0];
        $data['end_date'] = $geteventRange[1];
        $data['user_id'] = Auth::id();
        $data['site_url'] = $request->get('websiteurl');
        $data['price'] = $request->get('min_amount');
        $data['gst'] = $request->get('gst');
        $id = !empty($request->get('event_id'))?$request->get('event_id'):null;
        if(!$id){
            $data['event_status'] = Config('common.STATUS.Progress');
        }
        $retData = $this->event->saveEvent($data,$id);
        if(empty($id)){
        $createEventID = 'NEX'.$retData['event_type'].$retData['event_id'].$retData['user_id'];
        $datas['event_uid'] = $createEventID;
        $this->event->updateEvent($datas,['event_id'=>$retData['event_id']]);}
        return redirect()->route('event_desc',['event_data'=>$retData]);
       } catch (\Exception $ex) {
                return response(Helpers::getExceptionMessage($ex));
       }
    }
    
    
    public function saveDesc(eventRequestForDesc $request)
    {
        $eventid = $request->get('event_id');
        try{
        $data['description'] = utf8_encode($request->get('description'));
        if(!empty($files=$request->file('banner_image'))){
            $name=$files->getClientOriginalName();
            $storage_path = public_path(); 
            $uploadDir = config('common.uploadDir');
            $uploadPath = $storage_path.'/'.$uploadDir;
            $files->move($uploadPath,$name);
            $data['banner_image'] = $name;
        }
        $this->event->saveEvent($data,$eventid);
        if(auth()->user()->is_admin){
            $this->event->saveEvent(['event_status'=>Config('common.STATUS.Submitted')],$eventid);
            return redirect()->route('upcoming_event');
        }
        return redirect()->route('event_ticket',['event_id'=>$eventid])->with('success', [trans('message.success_update')]);
       } catch (\Exception $ex) {
                return response(Helpers::getExceptionMessage($ex));
       }
    }
    
    
    
    public function saveEventTicket(eventTicketRequest $request)
    {
        try{
        $ticket_id = $request->get('ticket_id');
        $start_time = $request->get('ticket_start_time');
        $description = $request->get('message');
        $eventDesc = Helpers::formatEditorData($description);
        $data = [];
        $data['title'] = $request->get('title');
        $event_uid = $request->get('event_uid');
        $event_id = $request->get('event_id');
        $data['event_id'] = !empty($request->get('event_id'))?$request->get('event_id'):$request->get('event_type');
        $data['event_type'] = $request->get('event_type');
        $data['type'] = $request->get('type');
        $data['amt_per_person'] = $request->get('amt_per_person');
//        $data['ticket_duration'] = $dateRange;
         $data['start_date'] = $start_time;
//        $data['end_date'] = $geteventRange[1];
        $data['booking_space'] = $request->get('event_space');
        $data['message'] = $description;
        $data['user_id'] = Auth::id();
        $calAmt = Helpers::calculateMoney((int)$request->get('amt_per_person'));
        $data['nexza_amt'] = $calAmt['nexza_amt'];
        $data['gatway_amt'] = $calAmt['gatway_amt'];
        $data['customer_total'] = $calAmt['customer_total'];
        $data['nexza_per'] = config('common.nexzoa_per');
        $data['gateway_per'] = config('common.nexzoa_Gateway_fee');
        $id = !empty($ticket_id)?$ticket_id:null;
        $ticketStatus = $this->event->getEventDetailsByID($data['event_id']);
        $retData = $this->event->saveEventTicket($data,$id);
        $id = !empty($id)?$id:$retData['ticket_id'];
        if(!empty($ticketStatus->event_status) && $ticketStatus->event_status == Config('common.STATUS.Submitted')){
        return redirect()->route('list_event_ticket',['event_uid'=>$event_uid,'event_id'=>$event_id]);
        } else{
        return redirect()->route('event_ticket',['event_uid'=>$event_uid,'event_id'=>$event_id]);
         }
       } catch (\Exception $ex) {
                return response(Helpers::getExceptionMessage($ex));
       }
    }
    
    public function updateProfile(Request $request)
    {
        try{
        $data = [];
            if($files=$request->file('profile_image')){
               $name=$files->getClientOriginalName();
               $storage_path = public_path(); 
               $uploadDir = config('common.uploadDir');
               $uploadPath = $storage_path.'/'.$uploadDir;
               $files->move($uploadPath,$name);
                $data['profile_image'] = $name;
            }
        $data['email'] = $request->get('email');
        $data['first_name'] = $request->get('first_name');
        $data['last_name'] = $request->get('last_name');
        $data['contact_number'] = $request->get('phone');
        $retData = $this->user->updateUser(Auth::id(),$data);
        return redirect()->back()->with('message', 'Data Saved Successfully');
       } catch (\Exception $ex) {
                return response(Helpers::getExceptionMessage($e));
       }
    }
    
    public function eventSoftDel(Request $request)
    {
        try{
            $delId = $request->get('delid');
            $retData = $this->event->delEventdata($delId);
        return redirect()->back()->with('message', 'Event Deletion successful');
       } catch (\Exception $ex) {
                return response(Helpers::getExceptionMessage($e));
       }
    }
    
    public function closeTicket(Request $request)
    {
        try{
            $ticketID = $request->get('ticket_id');
            $userID = $request->get('user_id');
            $retData = $this->event->closeTicket($userID,$ticketID);
        return redirect()->back()->with('message', 'Ticket Deletion successful');
       } catch (\Exception $ex) {
                return response(Helpers::getExceptionMessage($e));
       }
    }
    
    public function checkTicket(Request $request)
    {
        try{
            $eventID = $request->get('event_uid');
//            $userID = $request->get('user_id');
            $retData = $this->event->checkTicket($eventID);
            if($retData){
                return 1;
            }
        return 0;
       } catch (\Exception $ex) {
                return response(Helpers::getExceptionMessage($e));
       }
    }
    
    
    public function eventCategory(Request $request)
    {
        try{
            $e_id  = (int) $request->get('e_id');
            $eventLis = [];
            if(!empty($e_id)){
            $eventLis = $this->event->getEventCatDetails($e_id);
            }
             return view('eventbackend.event_category',['catList'=>!empty($eventLis)?$eventLis:'']);
//            return redirect()->route('eventCategory_list');
       } catch (\Exception $ex) {
                return response(Helpers::getExceptionMessage($ex));
       }
    }
    
    
    public function saveEveCategory(Request $request)
    {
        try{
            $data = [];
            $e_id  = !empty($request->get('e_id'))?$request->get('e_id'):null;
            $data['status'] = $request->get('status');
            $data['name']  = $request->get('name');
            $eventList = $this->event->saveEventCategory($data,$e_id);
             return redirect()->route('eventCategory_list')->with('message', 'Event save successfully');
       } catch (\Exception $ex) {
                return response(Helpers::getExceptionMessage($ex));
       }
    }
    
    public function eventCategory_list(Request $request)
    {
        try{
            $data = [];
            $data['status'] = $request->get('status');
            $data['name']  = $request->get('name');
            $catList = $this->event->listEventCategory();
            return view('eventbackend.category_event_list',['catList'=>$catList]);
       } catch (\Exception $ex) {
                return response(Helpers::getExceptionMessage($ex));
       }
    }
    
    public function deleteEventCategory(Request $request)
    {
        try{
            $e_id = $request->get('e_id');
            $catList = $this->event->deleteEventCategory($e_id);
            return redirect()->back()->with('message', 'Event Deletion Successful');
       } catch (\Exception $ex) {
                return response(Helpers::getExceptionMessage($ex));
       }
    }
    
   public function submitEvent(Request $request)
    {
       try{
        $STATUS = Config('common.STATUS.Submitted');
        $eventid = (int) $request->get('event_id');
        $submitVal = (int) $request->get('submitVal');
        $eventDetail = $this->event->getEventDetailsByID($eventid);
        $data['event_status'] = (int) $STATUS;
        $this->event->saveEvent($data,$eventid);
        $event_name = preg_replace('/\s+/', '-', $eventDetail['event_name']);
        return redirect()->route('event_detail',['event_id'=>$eventid.'-'.$eventDetail['event_uid'],'encyt'=>0])->with('success', [trans('message.success_update')]);
       } catch (\Exception $ex) {
                return response(Helpers::getExceptionMessage($ex));
       }
    }
    
    
   public function orders(Request $request)
    {
        $bookings = $this->stats->getOrders();
        return view('eventbackend.orders',['bookings'=>$bookings]);
    }
    
   public function ordersDetails(Request $request)
    {
        $order_id = $request->get('order_id');
        $orderDetails = $this->stats->getOrdersDetails($order_id,null);
        return view('eventbackend.ordersDetails',['orderDetails'=>$orderDetails]);
    }
    
   public function totalCandidates(Request $request)
    {
       $eventList = $this->event->getEventList(false,(int) Auth::id());
       $orderDetails = $this->stats->getOrdersDetails(null, null);
        return view('eventbackend.total_candidates',['orderDetails'=>$orderDetails,'eventList'=>$eventList]);
    }
    
    public function bankDetails()
    {
       $userProfile = $this->user->getUserProfile(Auth::id());
        return view('eventbackend.bank_details',['user'=>$userProfile]);
    }
    public function savebankDetails(Request $request)
    {
        $data = [];
        $data['account_number'] = !empty($request->get('account_number'))?$request->get('account_number'):null;
        $data['account_name'] = !empty($request->get('account_name'))?$request->get('account_name'):null;
        $data['ifsc_code'] = !empty($request->get('ifsc_code'))?$request->get('ifsc_code'):null;
        $data['gst_number'] = !empty($request->get('gst_number'))?$request->get('gst_number'):null;
        $this->user->updateUser(Auth::id(),$data);
        return redirect()->back()->with('message', 'Bank details saved successfully');
    }
}
