<?php

namespace App\Http\Controllers\Event;

use Illuminate\Http\Request;
use Session;
use Auth;
use App\Http\Controllers\Controller;
use App\Repositories\Event\EventInterface as EventInterface;
use App\Repositories\Stats\StatsInterface as StatsInterface;
class AjaxController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $event;
    
    
    public function __construct(EventInterface $event,StatsInterface $stats)
    {
        $this->event = $event;
         $this->stats = $stats;
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function stateList(Request $request)
    {
        $country_id = (int) $request->get('country_id');
        $data = $this->event->getstatetList($country_id);
        return $data;
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCityList(Request $request)
    {
        $state_id = (int) $request->get('state_id');
        $data = $this->event->getCityList($state_id);
        return $data;
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function stateDetails(Request $request)
    {
        $state_id = (int) $request->get('state_id');
        $data = $this->event->getstatetList($state_id,true);
        return $data;
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getTicketList(Request $request)
    {
        $event_id = (int) $request->get('event_uid');
        $data = $this->event->getEventListWithUid($event_id,(int) Auth::id());
        return $data;
    }
    
     /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function checkTicket(Request $request)
    {
        dd($request->all());
    }
    
    
     /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function updateCandidate(Request $request)
    {
        dd($request->all());
    }
    
        
   public function serachCandiByEvent(Request $request)
    {
       $event_id = $request->get('event_id');
       $candidateDetails = $this->stats->getCandidatesWithEvent(null,$event_id);
       return $candidateDetails;
    }
}
