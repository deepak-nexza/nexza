@extends('layouts.event.admin_layout_event')
@section('contentData')
<section class="explore">
    <br>
    <br>
    <br>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> Please correct errors and try again!.
                    <br/>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif  
                @if(sizeof($ticketDetail) > 0)
                @foreach($ticketDetail as $keyOne=>$ticketDetails)
                <div id="accordion" style="margin-bottom:5px;">

                    <div class="card" >
                        <div class="card-header" style="color:#000;background: #fb646f">
                            <a class="card-link" style="color:#fff;font-weight: bold;" data-toggle="collapse" href="#{{$ticketDetails['ticket_id']}}" >
                                 <span class="fa fa-calendar" style="  ">{{!empty($ticketDetails->ticket_id) ? ' '. \Carbon\Carbon::parse($eventDetail['start_date'])->format('j F, Y ').' '.'    -    '.$ticketDetails->title.'' : ''}}</span>
                            </a>
                        </div>
                        <div id="{{$ticketDetails['ticket_id']}}" class="collapse " data-parent="#accordion">
                            <div class="card-body">
                                <!-- SELECT2 EXAMPLE -->
                                {!!
                                Form::open(
                                array(
                                'name' => 'NexzaForms',
                                'id' => 'NexzaForms'.$ticketDetails['ticket_id'].$ticketDetails['user_id'],
                                'url'=>route('save_event_ticket'),
                                'method'=>'POST',
                                'files' => true,
                                'autocomplete' => 'off',
                                'class'=>'formElement otp',
                                Form::pkey() => [
                                'event_id' => $eventDetail['event_id'],
                                'user_id' => $eventDetail['user_id'],
                                'event_uid' => $eventDetail['event_uid'],
                                'ticket_id' => $ticketDetails['ticket_id'],
                                ],
                                )
                                )
                                !!}

                                <div class="row">
                                    <div class="col-sm-6">

                                        <div class="box-header with-border">
                                            <h3 class="box-title" style="font-weight: 700">Create Event Ticket  </h3>
                                        </div>

                                    </div>
                                </div>
                                <br>

                                <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />  
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="hidden" name="event_type" value="{{  !empty($ticketDetails->event_id)?$ticketDetails->event_id:''}}"  class="event_type">
                                        <div class="form-group">
                                            <label>Select Your Event</label>
                                            <select class="form-control select2 select2-hidden-accessible" id='event_type' onchange="changeSelect(event)" style="width: 100%;" tabindex="-1" aria-hidden="true">
                                                <option>Select Your Event</option>
                                                @foreach($eventlist as $item=>$val)
                                                <option value="{{$val->event_id}}"  {{ ((isset($ticketDetails->event_id) && $ticketDetails->event_id == $val->event_id)? "selected":"") }}>{{$val->event_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Ticket Title</label>
                                            <input type="text" name="title" placeholder="Ticket Title" class="form-control" value="{{  !empty($ticketDetails->title)?$ticketDetails->title:''}}">
                                        </div>
                                        <input type="hidden" name="type" value="{{  !empty($ticketDetails->type)?$ticketDetails->type:''}}"  class="t_type">
                                        <div class="form-group">
                                            <label>Ticket Type</label>
                                            <select class="form-control select2 select2-hidden-accessible" id="t_type" onchange="changeSelect(event)" style="width: 100%;" tabindex="-1" aria-hidden="true">
                                                <option value="">Select Ticket Type</option>
                                                <option value="1" {{ ((isset($ticketDetails->type) && $ticketDetails->type== 1)? "selected":"") }}>Paid</option>
                                                <option value="2" {{ ((isset($ticketDetails->type) && $ticketDetails->type== 2)? "selected":"") }}>Free</option>
                                            </select>
                                        </div>
                                      
                                        <label>Message Deliver Via Ticket</label>
                                        <div class="form-group">
                                            <textarea  name="message" cols="40"  placeholder="Message Deliver Via Ticket">{{  !empty($ticketDetails->message)?$ticketDetails->message:''}}</textarea>
                                        </div>

                                    </div><!-- /.col -->
                                    <div class="col-md-6">
                                          <div class="form-group">
                                            <label>Total Booking Space Available</label>
                                            <input type="text" name="event_space" placeholder="No Of Persons" value="{{  !empty($ticketDetails->booking_space)?$ticketDetails->booking_space:''}}" class="form-control">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Booking Amount per individual:</label>
                                            <input type="text" name="amt_per_person" value="{{  !empty($ticketDetails->amt_per_person)?$ticketDetails->amt_per_person:''}}" placeholder="INR" class="form-control bkk_amt">
                                        </div>
                                        <label>Amount BreakUp</label>
                                        <div class="form-group">
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <tbody>
                                                        <tr class="success">
                                                            <td>Amount Received Through Buyer</td>
                                                            <td >Rs<span class="amt_buy">0.00</span></td>
                                                        </tr>
                                                        <tr class="danger">
                                                            <td>Nexzoa Fee</td>
                                                            <td >Rs<span class="nexza_Amt">0.00</span></td>
                                                        </tr>      
                                                        <tr class="danger">
                                                            <td>Payment Gateway Fee</td>
                                                            <td >Rs<span class="gate_Amt">0.00</span></td>
                                                        </tr>
                                                        <tr class="warning">
                                                            <td>Paid Amt To You</td>
                                                            <td >Rs<span class="finalAmt">0.00</span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div> 

                                        </div>
                                    </div>
                                </div><!-- /.row -->
                                 <div class="box-footer">
                                    <div class="row">
                                        <div class="col-md-12">
 <button type="button" id="NexzaForms{{ $ticketDetails['ticket_id'].$ticketDetails['user_id'] }}" class="btn btn-primary ticketForm" style="float:right;cursor:pointer;background: #fb646f">Update</button>
                                        </div>
                                    </div>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>


                </div> 
                @endforeach
                @endif
                 <div id="accordion">

                    <div class="card" >
                        <div class="card-header" style="color:#000;background: #fb646f">
                            <a class="card-link" style="color:#fff;font-weight: bold;" data-toggle="collapse" href="#new" >
                               Add Ticket For ( {{ !empty($eventDetail->event_name)?$eventDetail->event_name:'' }}  )<span style="color:#000"></span>
                            </a>
                        </div>
                        <div id="new" class="collapse show" data-parent="#accordion">
                            <div class="card-body">
                                <!-- SELECT2 EXAMPLE -->
                                {!!
                                Form::open(
                                array(
                                'name' => 'NexzaForms',
                                'id' => 'NexzaForms',
                                'url'=>route('save_event_ticket'),
                                'method'=>'POST',
                                'files' => true,
                                'autocomplete' => 'off',
                                'class'=>'formElement otp',
                                Form::pkey() => [
                                'event_id' => $eventDetail['event_id'],
                                'user_id' => $eventDetail['user_id'],
                                'event_uid' => $eventDetail['event_uid'],
                                ],
                                )
                                )
                                !!}

                                <br>

                                <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />  
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="hidden" name="event_type" value=""  class="event_type">
                                        <div class="form-group">
                                            <label>Select Your Event </label>
                                            <select class="form-control select2 select2-hidden-accessible" id='event_type' onchange="changeSelect(event)" style="width: 100%;" tabindex="-1" aria-hidden="true" required>
                                                <option>Select Your Event</option>
                                                @foreach($eventlist as $item=>$val)
                                                <option value="{{$val->event_id}}"  {{ ((!empty($eventDetail->event_id) && $eventDetail->event_id == $val->event_id)? "selected":"") }}>{{$val->event_name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <input type="hidden" name="type" value=""  class="t_type">
                                        <div class="form-group">
                                            <label>Ticket Type</label>
                                            <select class="form-control select2 select2-hidden-accessible" id="t_type" onchange="changeSelect(event)" style="width: 100%;" tabindex="-1" aria-hidden="true" required>
                                                <option value="">Select Ticket Type</option>
                                                <option value="1" >Paid</option>
                                                <option value="2" >Free</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Ticket Title</label>
                                            <input type="text" name="title" placeholder="Ticket Title" class="form-control" value="">
                                        </div>
                                      
                                        <label>Message Deliver Via Ticket</label>
                                        <div class="form-group">
                                            <textarea  name="message" cols="40"  placeholder="Message Deliver Via Ticket"></textarea>
                                        </div>

                                    </div><!-- /.col -->
                                    <div class="col-md-6">
                                          <div class="form-group">
                                            <label>Total Booking Space Available</label>
                                            <input type="text" name="event_space" placeholder="No Of Persons" value="" class="form-control">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Booking Amount per individual:</label>
                                            <input type="text" name="amt_per_person" value="" placeholder="INR" class="form-control bkk_amt">
                                        </div>
                                        <label>Amount BreakUp</label>
                                        <div class="form-group">
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <tbody>
                                                        <tr class="success">
                                                            <td>Amount Received Through Buyer</td>
                                                            <td >Rs<span class="amt_buy">0.00</span></td>
                                                        </tr>
                                                        <tr class="danger">
                                                            <td>Nexzoa Fee</td>
                                                            <td >Rs<span class="nexza_Amt">0.00</span></td>
                                                        </tr>      
                                                        <tr class="danger">
                                                            <td>Payment Gateway Fee</td>
                                                            <td >Rs<span class="gate_Amt">0.00</span></td>
                                                        </tr>
                                                        <tr class="warning">
                                                            <td>Paid Amt To You</td>
                                                            <td >Rs<span class="finalAmt">0.00</span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div> 

                                        </div>
                                    </div>
                                </div><!-- /.row -->
                                <div class="box-footer">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button" id="NexzaForms" class="btn btn-primary ticketForm" style="float:right;cursor:pointer;background: #fb646f">Next</button>

                                        </div>
                                    </div>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>


                </div> 
                
            </div><!-- /.box-body -->
            
        </div><!-- /.box -->
        <br>
        <br>
                                    <div class="row">
                                        <div class="col-md-6" style="text-align: right">

                                            <a href="{{route('event_desc',['event_id'=>!empty($eventDetail->event_id)?$eventDetail->event_id:'','event_data'=>$eventDetail])}}"><button type="button" class="btn btn-primary" style="float:right;cursor:pointer;">Back</button></a>
                                        </div>
                                     <div class="col-md-6" style="text-align: left">

                                        @if(!empty($ticketDetail) && sizeof($ticketDetail) > 0)
                                        {!!
                                Form::open(
                                array(
                                'name' => 'NexzaForms_final',
                                'id' => 'NexzaForms_final',
                                'url'=>route('submit_event'),
                                'method'=>'POST',
                                'files' => true,
                                'autocomplete' => 'off',
                                'class'=>'formElement otp',
                                Form::pkey() => [
                                'event_id' => $eventDetail['event_id'],
                                ],
                                )
                                )
                                !!}
                                            <button type="submit"  class="btn btn-primary finalsubmit" style="cursor:pointer;background: #fb646f">Submit</button>
 {!! Form::close() !!}
                                        </div>
                                        @endif
                                    </div>
        </div><!-- /.box -->
    <br>



</section>
@endsection
@push('head')
<script src="{{ asset('js/eventbackend/currency.min.js')}}"></script>
<script src="{{ asset('js/eventbackend/event_ticket.js')}}"></script>
<script>
var messages = {
    _token: "{{ csrf_token() }}",
    currencyAmt: "{{ config('common.nexzoa_per') }}",
    gatewayAmt: "{{ config('common.nexzoa_Gateway_fee') }}",
    checkTicket: "{!! route('check_ticket') !!}",
};
</script>
@endpush