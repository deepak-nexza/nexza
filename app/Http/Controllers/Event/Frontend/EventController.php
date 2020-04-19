<?php
namespace App\Http\Controllers\Event\Frontend;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Session;
class EventController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
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
    public function createEvent()
    {
        return view('eventbackend.create_event');
    }
    
}
