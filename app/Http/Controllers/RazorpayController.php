<?php

namespace App\Http\Controllers;

use Auth;
use Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Razorpay\Api\Api;
use Session;
use Redirect;
use App\Http\Requests\ProfileRequest as profileRequest;
use App\Http\Requests\CandidateRequest as candidateRequest;
use App\Repositories\User\UserInterface as UserInterface;
use App\Repositories\Event\EventInterface as EventInterface;


class RazorpayController extends Controller
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
    
    public function dopayment(Request $request)
    {        
         $dataOrder = $request->get('data');
                 if(empty($dataOrder)){
                     abort(400);
                 }
        $orderData = (int) \Scramble::decrypt($dataOrder);
        $retData = $this->event->getOrderDetails(['order_id'=>(int) $orderData]);
        $api = new Api(config('razorpay.razor_key'), config('razorpay.razor_secret'));
        $order  = $api->order->create([
            'receipt'         => $retData['order_id'],
            'amount'          => $retData['order_amt'] * 100, // amount in the smallest currency unit
            'currency'        => 'INR',// <a href="https://razorpay.freshdesk.com/support/solutions/articles/11000065530-what-currencies-does-razorpay-support" target="_blank">See the list of supported currencies</a>.)
            'payment_capture' =>  '1'
        ]);
        \Session::put('razorpay_order_id',$order['id']);
//        dd($order);
        
        
        return view('eventfrontend.razorpayform',['data'=>$retData,'order'=>$order]);
    }

    public function payment(Request $request)
    {
        //Input items of form
         $input = Input::all();
        //get API Configuration 
        $api = new Api(config('razorpay.razor_key'), config('razorpay.razor_secret'));
        //Fetch payment information by razorpay_payment_id
        $payment = $api->payment->fetch($input['razorpay_payment_id']);
        $updateFields = ['email'=>$payment->email,'phone'=>$payment->contact,'gatway_order_id'=>$input['razorpay_order_id'],'transaction_id'=>$input['razorpay_payment_id'],'payment_response'=>json_decode($payment->amount),'Payment_frm_gtwy'=> $payment->amount,'status'=>!empty($payment->captured)?1:0,'methods'=>$payment->method];
        $where = ['order_id'=>(int) $payment->notes->order_id];
        $this->event->updateOrder($updateFields,$where);
        if(count($input)  && !empty($input['razorpay_payment_id'])) {
            try {
//                $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount'=>$payment['amount'])); 
                $retData = $this->event->getOrderDetails(['order_id'=>(int) $payment->notes->order_id]);
                return redirect()->route('receipt',[$input['razorpay_order_id']]);
            } catch (\Exception $e) {
                return  $e->getMessage();
                \Session::put('error',$e->getMessage());
                return redirect()->back();
            }

            // Do something here for store payment details in database...
        }
        
        \Session::put('success', 'Payment successful, your order will be despatched in the next 48 hours.');
        return redirect()->back();
    }
    public function receipt(Request $request)
    {
        \Session::forget('razorpay_order_id');
        $getsegment = $request->segment(2);
        $retData = $this->event->getOrderDetails(['gatway_order_id'=>$getsegment]);
        $candidates = $this->event->getCandidateDetail(['order_id'=>$retData['order_id']]);
        return view('eventfrontend.receipt',['candidates'=>$candidates,'order'=>$retData]);
    }
}