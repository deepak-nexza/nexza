@extends('layouts.event.eventapp')
@section('content')
    
<section class="content">
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
          <!-- SELECT2 EXAMPLE -->
               <div class="box box-info">
                <div class="box-header with-border">
                    @if(!empty($ticket_id))
                  <h3 class="box-title">Update Ticket Layout</h3>
                    @else
                  <h3 class="box-title">Create Ticket Layout</h3>
                    @endif
                </div>
            <div class="box-body">
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
        )
        )
        !!}
                
               @if(!empty($ticket_id))
               <input type="hidden" value="{{ $ticket_id }}" name="ticket_id" >
               @endif
            <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />  
              <div class="row">
                <div class="col-md-6">
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
                  <div class="form-group">
                    <label>Total Booking Space Available</label>
                    <input type="text" name="event_space" placeholder="No Of Persons" value="{{  !empty($ticketDetails->booking_space)?$ticketDetails->booking_space:''}}" class="form-control">
                  </div>
                    <label>Message Deliver Via Ticket</label>
                    <div class="form-group">
                        <textarea  name="message" rows="5" cols="40" placeholder="Message Deliver Via Ticket">{{  !empty($ticketDetails->message)?$ticketDetails->message:''}}</textarea>
                </div>
                    
                </div><!-- /.col -->
                <div class="col-md-6">
                    <div class="form-group">
                    <label>Booking Amount per individual:</label>
                    <input type="text" name="amt_per_person" value="{{  !empty($ticketDetails->amt_per_person)?$ticketDetails->amt_per_person:''}}" placeholder="INR" class="form-control bkk_amt">
                    </div>
                     <div class="form-group">
                    <label>Booking Start/End Date:</label>
                    <input type="text" name='booking_duration' value="{{  !empty($ticketDetails->ticket_duration)?$ticketDetails->ticket_duration:''}}" placeholder="Booking Start/End Date" value="" class="form-control my-colorpicker1" id="reservationtime1">
                 </div>
                    <input type="hidden" name="event_type" value="{{  !empty($ticketDetails->event_id)?$ticketDetails->event_id:''}}"  class="event_type">
                     <div class="form-group">
                    <label>Select Your Event</label>
                    <select class="form-control select2 select2-hidden-accessible" id='event_type' onchange="changeSelect(event)" style="width: 100%;" tabindex="-1" aria-hidden="true">
                        <option>Select Your Event</option>
                        @foreach($eventlist as $item=>$val)
                        <option value="{{$val->event_id}}"  {{ ((!empty($ticketDetails->event_id))? "selected":"") }}>{{$val->event_name}}</option>
                        @endforeach
                    </select>
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
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </div>
                {!! Form::close() !!}
            </div><!-- /.box-body -->
          </div><!-- /.box -->


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
      };
      </script>
@endpush