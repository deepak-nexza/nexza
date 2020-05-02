<?php
namespace App\Http\Controllers\Event\Frontend;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest as eventRequest;
use App\Http\Requests\eventTicketRequest as eventTicketRequest;
use Session;
use Helpers;
use Illuminate\Validation\Validator; 
use App\Repositories\Event\EventInterface as EventInterface;
use Auth;
class EventController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $event;
    
    public function __construct(EventInterface $event)
    {
        $this->event = $event;
      $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function myaccount()
    {
        return view('eventfrontend.dashboard');
    }
    
     /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createEvent(Request $request)
    {
        $eventUid = $request->get('event_uid');
        $countryList = Helpers::countryList()->toArray();
        $stateList = Helpers::stateList()->toArray();
        $eventlist = Helpers::getAllEvent()->toArray();
        $mode = config('common.privacy_mode');
        if(!empty($eventUid)){
        $eventDetail = $this->event->getEventDetails($eventUid);
        }
        return view('eventbackend.create_event',[
            'country_list'=>$countryList,
            'state_list'=>$stateList,
            'mode'=>$mode,
            'eventDetail'=>!empty($eventDetail)?$eventDetail:null,
            'eventUid'=>!empty($eventUid)?$eventUid:null,
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
    public function eventTicket()
    {
        $eventList = $this->event->getEventList(false,(int) Auth::id());
        return view('eventbackend.event_ticket',['eventlist'=>$eventList]);
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
        $ticketDetails = $this->event->getTicketDetails(['ticket_id'=>$ticket_id,'user_id'=>$user_id]);
        $eventList = $this->event->getEventList(false,(int) Auth::id());
        return view('eventbackend.event_ticket',['eventlist'=>$eventList,'ticketDetails'=>$ticketDetails,'ticket_id'=>$ticket_id]);
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
        if(!empty($ticket_ID) && !empty($user_id)){
            $ticketDetails = $this->event->getTicketDetails(['ticket_id'=>$ticket_ID,'user_id'=>$user_id]);
        }
        $ticketDetails = !empty($ticketDetails)?$ticketDetails:null;
        $eventList = $this->event->getEventList(false,(int) Auth::id());
        return view('eventbackend.event_ticket_list',['eventlist'=>$eventList,'ticket_id'=>$ticket_ID,'user_id'=>$user_id,'ticketDetails'=>$ticketDetails]);
    }
    
    public function saveEvent(eventRequest $request)
    {
        try{
        if($files=$request->file('banner_image')){
            $name=$files->getClientOriginalName();
            $storage_path = storage_path(); 
            $uploadDir = config('common.uploadDir');
            $uploadPath = $storage_path.'/'.$uploadDir;
            $files->move($uploadPath,$name);
        }
        $dateRange = $request->get('event_duration');
        $description = $request->get('description');
        $geteventRange = preg_split('/[\s][\-][\s]/',$dateRange);
        $eventDesc = Helpers::formatEditorData($description);
        $data = [];
        $data['event_name'] = $request->get('event_name');
        $data['event_type'] = $request->get('event_type');
        $data['country_id'] = $request->get('country_id');
        $data['state_id'] = $request->get('state_id');
        $data['event_duration'] = $request->get('event_duration');
        $data['event_privacy'] = $request->get('event_privacy');
        $data['status'] = $request->get('status');
        $data['event_location'] = $request->get('event_location');
        $data['description'] = $request->get('description');
        $data['banner_image'] = $name;
        $data['start_date'] = $geteventRange[0];
        $data['end_date'] = $geteventRange[1];
        $data['user_id'] = Auth::id();
        $id = null;
        $retData = $this->event->saveEvent($data,$id);
        $createEventID = 'NEX'.$retData['event_type'].$retData['event_id'].$retData['user_id'];
        $datas['event_uid'] = $createEventID;
        $this->event->updateEvent($datas,['event_id'=>$retData['event_id']]);
        return redirect('event/upcoming-event');
       } catch (\Exception $ex) {
                dd($ex->getMessage());
       }
    }
    
    
    public function updateEvent(eventRequest $request)
    {
        $eventUid = $request->get('event_uid');
        try{
        $data = [];
        $dateRange = $request->get('event_duration');
        $description = $request->get('description');
        $geteventRange = preg_split('/[\s][\-][\s]/',$dateRange);
        $eventDesc = Helpers::formatEditorData($description);
        $data['event_name'] = $request->get('event_name');
        $data['event_type'] = $request->get('event_type');
        $data['country_id'] = $request->get('country_id');
        $data['state_id'] = $request->get('state_id');
        $data['event_duration'] = $request->get('event_duration');
        $data['event_privacy'] = $request->get('event_privacy');
        $data['status'] = $request->get('status');
        $data['event_location'] = $request->get('event_location');
        $data['description'] = $request->get('description');
        if($files=$request->file('banner_image')){
            $name=$files->getClientOriginalName();
            $storage_path = public_path(); 
            $uploadDir = config('common.uploadDir');
            $uploadPath = $storage_path.'/'.$uploadDir;
            $files->move($uploadPath,$name);
            $data['banner_image'] = $name;
        }
        $data['start_date'] = $geteventRange[0];
        $data['end_date'] = $geteventRange[1];
        $data['user_id'] = Auth::id();
        $this->event->updateEvent($data,['event_uid'=>$eventUid]);
        return redirect('event/upcoming-event')->with('success', [trans('message.success_update')]);
       } catch (\Exception $ex) {
                dd($ex->getMessage());
       }
    }
    
    
    
    public function saveEventTicket(eventTicketRequest $request)
    {
        try{
        $ticket_id = $request->get('ticket_id');
        $dateRange = $request->get('booking_duration');
        $description = $request->get('message');
        $geteventRange = preg_split('/[\s][\-][\s]/',$dateRange);
        $eventDesc = Helpers::formatEditorData($description);
        $data = [];
        $data['title'] = $request->get('title');
        $data['event_id'] = $request->get('event_type');
        $data['type'] = $request->get('type');
        $data['amt_per_person'] = $request->get('amt_per_person');
        $data['ticket_duration'] = $dateRange;
        $data['start_date'] = $geteventRange[0];
        $data['end_date'] = $geteventRange[1];
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
        $retData = $this->event->saveEventTicket($data,$id);
        return redirect()->route('list_event_ticket',['ticket_id'=>$id,'user_id'=>Auth::id()]);
       } catch (\Exception $ex) {
                dd($ex->getMessage());
       }
    }
    
    
}
