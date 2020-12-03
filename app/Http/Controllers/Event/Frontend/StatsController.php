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

class StatsController extends Controller
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
    public function paystats()
    {
        $open = $this->event->getEventList(false,(int) Auth::id());
        $Close = $this->event->getEventList(true,(int) Auth::id());
        return view('eventbackend.stats',['close'=>$Close->count(),'open'=>(int) $open->count()]);
    }
    
     /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function requestpayment()
    {
        $payDetails = $this->stats->getpaymentRequest(Auth::id());
            return view('eventbackend.payment_request',['payDetails'=>$payDetails]);
    }
    
     /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function saveRequestedPayment(Request $request)
    {
        $data=[];
        $data['req_amt'] = $request->get('req_amount');
        $data['user_id'] = Auth::id();
        $data['req_date'] = $request->get('release_date');
        $payDetails = $this->stats->getpaymentRequest(Auth::id());
        if(sizeof($payDetails) > 0){
           return redirect()->back()->with('message', 'Wait until previous request do not complete');
        }
        $this->stats->paymentRequest($data,null);
        return redirect()->back()->with('message', 'Payment Request saved successfully');
    }
    
    
}
