<?php
namespace App\Http\Controllers\Event\Frontend;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest as eventRequest;
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
        $eventList = $this->event->getEventList(false);
        return view('eventbackend.upcoming_event',['eventlist'=>$eventList]);
    }
    
     /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function pastEvent()
    {
        $eventList = $this->event->getEventList(true);
        return view('eventbackend.past_event',['eventlist'=>$eventList]);
    }
    
     /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function eventTicket()
    {
        return view('eventbackend.event_ticket');
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
        $geteventRange = explode('-',$dateRange);
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
        $geteventRange = explode('-',$dateRange);
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
    
}
