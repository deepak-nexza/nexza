<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Auth;
use Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest as profileRequest;
use App\Http\Requests\CandidateRequest as candidateRequest;
use App\Repositories\User\UserInterface as UserInterface;
use App\Repositories\Event\EventInterface as EventInterface;
use App\Rules\MatchOldPassword;
use App\Repositories\Models\User;
use Illuminate\Support\Facades\Hash;
class GuestController extends Controller
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
    public function index()
    {
        $eventlist = $this->event->getAllEventWithDetails(null);
        $stateList = Helpers::stateList()->toArray();
         $evenType = Helpers::getAllEvent()->pluck('name','id');
        return view('eventfrontend.index',['eventList'=>$eventlist,'stateList'=>$stateList,'eventType'=>$evenType]);
    }
    
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function aboutUs()
    {
        return view('eventfrontend.about');
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function contactus()
    {
        return view('eventfrontend.contact');
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function event_gallery()
    {
        return view('eventfrontend.event_gallery');
    }
    
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function eventDetails(Request $request)
    {
        $id = $request->get('event_uid');
         $eventDetail = $this->event->getEventDetails($id);
        return view('eventfrontend.event_detail',['eventDetail'=>$eventDetail]);
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function openProfile()
    {
        $userProfile = $this->user->getUserProfile(Auth::id());
        return view('auth.open_profile');
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
    *
     * @param type $request
     * @param type $loginUsername
     * @return type
     */
    public function chagnePassword(Request $request) {
           return view('auth.passwords.confirm');
    }
    
        /** 
    *
     * @param type $request
     * @param type $loginUsername
     * @return type
     */
    public function savePassword(Request $request) {
         
        $validate = $request->validate([
            'old_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],

        ]);
        Session::flash('message','Password Changed Successfully');
        User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);
        return redirect()->route('password.confirm');
    }
    
         /** 
    *
     * @param type $request
     * @param type $loginUsername
     * @return type
     */
    public function privacy_policy(Request $request) {
           return view('eventfrontend.privacy');
    }
    
         /** 
    *
     * @param type $request
     * @param type $loginUsername
     * @return type
     */
    public function tandc(Request $request) {
           return view('eventfrontend.t_c');
    }
    
         /** 
    *
     * @param type $request
     * @param type $loginUsername
     * @return type
     */
    public function disclaimer(Request $request) {
           return view('eventfrontend.disclaimer');
    }
    
    public function bookEvent(Request $request)
    {
          $sessionID = \Session::getId();
          $this->event->deleteCandidateDetails($sessionID);
          $getsegment = $request->segment(2);
          $preg_split = preg_split('/\-/', $getsegment);
          $euid = '';
          foreach($preg_split as $key=>$val){
              $check = preg_match('/[A-Z]{3}[\d]/', $val,$match);
              if($check){ $euid=$preg_split[$key];  }
          }
          
        try{
            $data['event_uid'] = $euid;
//            $bookingSave = $this->event->saveBooking($data,$id=null);
            $eventDetail = $this->event->getEventDetails($data['event_uid']);
            $ticketList = $this->event->getTicketList($eventDetail['event_id'],$eventDetail['user_id']);
            return view('eventfrontend.payment',
                    ['ticketList'=>!empty($ticketList)?$ticketList:'',
                     'eventDetail'=>!empty($eventDetail)?$eventDetail:'']);
            
       } catch (\Exception $ex) {
                return response(Helpers::getExceptionMessage($ex));
       }
    }
    public function bookEventCandidates(Request $request)
    {
          $getsegment = $request->segment(2);
          $preg_split = preg_split('/\-/', $getsegment);
          $euid = '';
          foreach($preg_split as $key=>$val){
              $check = preg_match('/[A-Z]{3}[\d]/', $val,$match);
              if($check){ $euid=$preg_split[$key];  }
          }
         $canArr = [];
         $ticket =  $request->get('tickets');
         $quantity =  $request->get('quantity');
         foreach($ticket as $key=>$val){
            $ticketID =  \Scramble::decrypt($val);
            $canArr[$ticketID] = $quantity[$key];
         }
        try{
            $data['event_uid'] = $euid;
            $eventDetail = $this->event->getEventDetails($data['event_uid']);
            $ticketList = $this->event->getTicketList($eventDetail['event_id'],$eventDetail['user_id']);
            return view('eventfrontend.candidates',
                    ['ticketList'=>!empty($ticketList)?$ticketList:'',
                    'canArr'=>!empty($canArr)?$canArr:'',
                     'eventDetail'=>!empty($eventDetail)?$eventDetail:'']);
            
       } catch (\Exception $ex) {
                return response(Helpers::getExceptionMessage($ex));
       }
    }
    
      public function thankyou(Request $request)
    {
        
            return view('eventfrontend.thankyou');
            
    }
    
      public function saveCandidate(candidateRequest $request)
    {
            $retArr = [];
            $data['full_name'] = $request->get('full_name');
            $data['email'] = $request->get('email');
            $data['phone'] = $request->get('contact_number');
            $data['address'] = $request->get('address');
            $data['event_id'] = $request->get('event_id');
            $data['user_id'] = $request->get('user_id');
            $data['ticket_id'] = $request->get('ticket_id');
            $data['ticket_amt'] = $request->get('amt_per_person');
            $data['session_id'] = \Session::getId();
            $form_id = !empty($request->get('calID'))?\Scramble::decrypt($request->get('calID')):null;
            $uid = !empty($request->get('uid'))?\Scramble::decrypt($request->get('uid')):null;
            $cid = !empty($request->get('cid'))? \Scramble::decrypt($request->get('cid')) : '';
            $id = !empty($cid)?$cid:null;
            $retData = $this->event->saveCandidate($data,$id);
            $retArr['full_name'] = $retData['full_name'];
            $retArr['email'] = $retData['email'];
            $retArr['phone'] = $retData['phone'];
            $retArr['address'] = $retData['address'];
            $retArr['gid'] = \Scramble::encrypt($retData['id']);
            $retArr['calID'] = \Scramble::encrypt($uid.'-'.$retData['id']);
            $retData = $this->event->saveCandidate(['form_id'=>$uid.'-'.$retData['id']],$retData['id']);
            return $retArr;
    }
    
      public function payNow(Request $request)
    {
        try{
          $getsegment = $request->segment(2);
          $preg_split = preg_split('/\-/', $getsegment);
          $euid = '';
          foreach($preg_split as $key=>$val){
              $check = preg_match('/[A-Z]{3}[\d]/', $val,$match);
              if($check){ $euid=$preg_split[$key];  }
          }
          
            $eventDetail = $this->event->getEventDetails($euid);
            $gst = $eventDetail['gst'];
            $taxRate = 0;
            if($gst==2){
                $taxRate = config('common.TAX_RATE');
            }
            $sessionID = \Session::getId();
            $retData = $this->event->getCandidateDetailsUsession($sessionID);
            $payAmt = 0.00;
            if(!empty($retData)){
                foreach($retData as $key=>$val){
                     $ticketDetail = $this->event->getTicketDetails(['ticket_id'=>$val['ticket_id'],'user_id'=>$val['user_id']]);
                     $payAmt += $ticketDetail['amt_per_person'];
                }
            }
            $finalPay = !empty($taxRate)?(($payAmt * $taxRate)/100):0;
            $amtToPay = $payAmt + $finalPay;
            $data = [];
            $data['order_amt'] = $payAmt;
            $data['session_id'] = $sessionID;
            $retData = $this->event->saveOrder($data,null);
            $this->event->updateCandidate(['order_id'=>$retData['order_id']],['session_id'=>$retData['session_id']]);
            $this->event->updateCandidate(['session_id'=>null],['order_id'=>$retData['order_id']]);
            $this->event->updateOrder(['session_id'=>null,'user_id'=>$eventDetail['user_id'],'event_id'=>$eventDetail['event_id']],['order_id'=>$retData['order_id']]);
            return ['order' => \Scramble::encrypt($retData['order_id'])];
       } catch (\Exception $ex) {
                return response(Helpers::getExceptionMessage($ex));
       }
    }
    public function viewList(Request $request){
        
             $eventlist = $this->event->getAllEventWithDetails(null);
            return json_encode($eventlist);
    }
      public function delEvent(Request $request){
        
             $eventlist = $this->event->delEventdata($request->get('id'));
            return json_encode($eventlist);
    }
      public function createUser(Request $request){
        
//        $eventid = (int) $request->get('event_id');
        $data['event_name'] = (int) $request->get('event_name');
        
        $datas = $this->event->saveEvent($data,null);
        return json_encode($datas);
    }
    
    
}
