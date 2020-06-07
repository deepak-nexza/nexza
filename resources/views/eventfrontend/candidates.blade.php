@extends('layouts.event.layout_event')
@section('contentData')
<!-- about-block
                ================================================== -->
<section class="about" style="background: none">
    <div class="container">

<!--        <div class="row">
            <div class="col-md-12">

                <div class="invoice">

                    <div class="invoice-header">
                        <div class="invoice-from">
                            <address class="m-t-5 m-b-5">
                                <strong class="text-inverse">{{$eventDetail['event_name']}}</strong><br>
                                {{$eventDetail['event_location']}}<br>
                                Phone: {{$eventDetail['contact_number']}}<br>
                            </address>
                        </div>
                        <div class="invoice-date">
                            <small>Invoice / {{ \Carbon\Carbon::now()->format('F') }} period</small>
                            <div class="date text-inverse m-t-5">{{ \Carbon\Carbon::now()->format('j F, Y ') }}</div>
                            <div class="invoice-detail">
                                {{$eventDetail['user_id']}}{{ $eventDetail['event_uid']}}<br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>-->



        <br>
        <br>
        <br>
        <div class="shopping-cart">
            <div class="row">
                <div class="col-md-9">
                 @php    $k = 0 @endphp
                @foreach($ticketList as $key=>$val)
                @php $preg_replace = preg_replace('/\s+/', '-', $val['title']); @endphp
                @foreach($canArr as $keyData=>$value)
                @if($keyData==$val['ticket_id'])
                @php $j = 1 @endphp
                @for ($i = 0; $i < $value; $i++)
                @php $canDetails = Helpers::getCandidateDetails($preg_replace.$i) @endphp
                            <div id="accordion">
                                <div class="card " style="width:100%;">
                                <div class="card-header {{$preg_replace.$i}}">
                                  <a class="card-link" data-toggle="collapse" href="#{{$keyData}}-{{$j}}">
                                 {{ $val['title'] }} -  Candidate {{$j}}
                                  </a>
                                </div>
                                <div id="{{$keyData}}-{{$j}}" class="collapse  @if($k==0) show @endif" data-parent="#accordion">
                                  <div class="card-body">
                          <!-- Material form contact -->
                          <div class="card">
                              <!--Card content-->
                              <div class="card-body px-lg-5 pt-0">
                                  <!-- Form -->
                                  {!!
                                  Form::open(
                                  array(
                                  'name' => 'bookingform',
                                  'id' => $preg_replace.$i,
                                  'method'=>'POST',
                                  'autocomplete' => 'off',
                                  'class'=>'text-center',
                                  Form::pkey() => [
                              'event_id' => $eventDetail['event_id'],
                              'user_id' => $eventDetail['user_id'],
                              'event_uid' => $eventDetail['event_uid'],
                              'ticket_id' => $val['ticket_id'],
                              'amt_per_person' => $val['amt_per_person'],
                              'uid' =>  \Scramble::encrypt($preg_replace.$i),
                                ],
                                  )
                                  )
                                  !!}
                                  @csrf
                                  
                                  <input type="hidden" id="recID" name="cid" value="{{ !empty($canDetails->id)?\Scramble::encrypt($canDetails->id):''}}">
                                  <input type="hidden" id="calID" name="calID" value="{{ \Scramble::encrypt($preg_replace.$i)}}">
                                      <!-- Name -->
                                      <div class="md-form mt-3">
                                      <input type="text" id="{{$preg_replace.$i}}full_name" value="{{ !empty($canDetails->full_name)?$canDetails->full_name:'' }}" placeholder="Full Name" name="full_name" class="form-control" required>
                                      </div>
                                              <br>
                                      <!-- E-mail -->
                                      <div class="md-form">
                                          <input type="email" id="{{$preg_replace.$i}}email" value="{{ !empty($canDetails->email)?$canDetails->email:'' }}" placeholder="E-mail" name="email" class="form-control" required>
                                      </div>
                                      <br>
                                      <!-- E-mail -->
                                      <div class="md-form">
                                          <input type="text" id="{{$preg_replace.$i}}contact_number" value="{{ !empty($canDetails->phone)?$canDetails->phone:'' }}" placeholder="Contact Number" name="contact_number" class="form-control" required>
                                      </div>
                                      <br>
                                      <!--Message-->
                                      <div class="md-form">
                                      <textarea id="{{$preg_replace.$i}}address" class="form-control md-textarea" rows="2" name="address" placeholder="Address Details" required>{{ !empty($canDetails->address)?$canDetails->address:'' }}</textarea>
                                      </div>
                                      <br>
                                      <!-- Send button -->
                                      <button  class="btn btn-outline-info btn-rounded btn-block z-depth-0 my-4 waves-effect {{$keyData}}-{{$j}} saveAttendie" data-link="{{$keyData}}-{{$j}}" data-id="{{$preg_replace.$i}}"   data-href="{{ route('save_candidate')}}">Save Details <i class="fa fa-refresh fa-spin hidden"></i></button>
                                   {!! Form::close() !!}
                                  <!-- Form -->

                              </div>

                          </div>
                          <!-- Material form contact -->
                                  </div>
                                </div>
                              </div>
                            </div>
                <br>
                @php $j++ @endphp
                @php    $k++ @endphp
                @endfor
                
@endif
@endforeach
  
                @endforeach
                </div>
    @php $totalAmt = 0.00 @endphp
    @php $items = 0 @endphp
    @foreach($canArr as $tickKey=>$value)
                @php $items += (int) $value @endphp
    @endforeach
                
    @foreach($ticketList as $key=>$val)
                @foreach($canArr as $tickKey=>$value)
                  @if($tickKey==$val['ticket_id'])
                @php $totalAmt += (int) $val['amt_per_person'] * (int) $value @endphp
                @endif
                @endforeach
                @endforeach
                
                @php               
                $tax = (($totalAmt * 18)/100)
                @endphp
                
 <div class="col-md-3">
            <div class="totals">
                <div class="totals-item">
                    <label>Total Items</label>
<div style="float:right">items(<span id="totalItem">{{$items}}</span>)</div>
                </div>
                <div class="totals-item">
                    <label>Subtotal</label>
                    <div class="totals-value" id="cart-subtotal">{{$totalAmt + $tax}}</div>
                </div>
            </div>
@php $preg_replace = preg_replace('/\s+/', '-', $eventDetail['event_name']); @endphp
 <a href="{{ route('pay',['event'=>$eventDetail['event_uid']]) }}"> <button class="checkout">Pay Now</button></a>

        </div>
        </div>
                    </div>
            </div>
    </div>
</div>

</section>
<!-- End about-block -->



@endsection
@section('headDatajsorcss')
<link rel="stylesheet" href="{{ asset('event/css/swipebox.css')}}">
@endsection
@section('jscript')
<script>
      var messages = {
          _token: "{{ csrf_token() }}",
          NexFee: "{{ config('common.nexzoa_per') }}",
          gatewayFee: "{{ config('common.nexzoa_Gateway_fee') }}",
          tax_rate: "{{ config('common.TAX_RATE') }}",
          checkTicket: "{!! route('check_ticket') !!}",
          candidateRoute: "{!! route('save_candidate') !!}",
      };
      </script>
<script src="{{ asset('js/eventbackend/currency.min.js')}}"></script>
<script src="{{ asset('js/eventbackend/invoice.js')}}"></script>
<script src="{{ asset('js/eventbackend/candidate.js')}}"></script>

@endsection