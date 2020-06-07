@extends('layouts.event.layout_event')
@section('contentData')
<!-- about-block
                ================================================== -->
<section class="about" style="background: none">
    <div class="container">

        <div class="row">
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
        </div>



        <br>
        <br>
        <div class="shopping-cart">
            <div class="row">
                <div class="col-md-9">
                    <div class="column-labels">
                        <label class="product-details">Product</label>
                        <label class="product-price">Price</label>
                        <label class="product-quantity">Quantity</label>
                        <!--<label class="product-removal">Remove</label>-->
                        <label class="product-line-price">Total</label>
                    </div>
                    @php $preg_replace = preg_replace('/\s+/', '-', $eventDetail['event_name']); @endphp
                    {!!
        Form::open(
        array(
        'id' => 'book_now_form',
        'method'=>'POST',
        'url'=>route('book_event_candidates',['name'=>$preg_replace.'-'.$eventDetail['event_uid']]),
        'autocomplete' => 'off',
        Form::pkey() => [
    'event_id' => $eventDetail['event_id'],
    'user_id' => $eventDetail['user_id'],
    'event_uid' => $eventDetail['event_uid'],
      ],
        )
        )
        !!}
                @foreach($ticketList as $key=>$val)
                    <div class="product">
                        <div class="product-details">
                            <input type="hidden" value="{{ \Scramble::encrypt($val['ticket_id']) }}" name="tickets[]">
                             <div class="product-title">{{$val['title']}}</div>
                            <p class="product-description"> {{$val['message']}}</p>
                        </div>
                        <div class="product-price">{{$val['amt_per_person']}}</div>
                        <div class="product-quantity">
                            <div class="input-group">
                              <input type="button" value="-" id="sub" class="sub" data-field="quantity">
                              <input type="text" step="1" max="" min="0" value="0" name="quantity[]"  class="quantity-field items">
                                <input type="button" value="+" id="add" class="add" data-field="quantity">
                            </div>
                        </div>
                        <div class="product-line-price">0.00</div>
                    </div>
                    
                    


                @endforeach
  {!! Form::close() !!}
                </div>
                
 <div class="col-md-3">
            <div class="totals">
                <div class="totals-item">
                    <label>Total Items</label>
<div style="float:right">items(<span id="totalItem"></span>)</div>
                </div>
                <div class="totals-item">
                    <label>Subtotal</label>
                    <div class="totals-value" id="cart-subtotal">0.00</div>
                </div>
                @if($eventDetail['gst']==2)
                <div class="totals-item">
                    <label>Tax & Fees</label>
                    <div class="totals-value" id="cart-tax">0.00</div>
                </div>
                @endif
                <div class="totals-item totals-item-total">
                    <label>Grand Total</label>
                    <div class="totals-value" id="cart-total">0.00</div>
                </div>
            </div>

 <button class="checkout" id="continue">Continue</button>

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
          ownedBy: "{{ $eventDetail['gst'] }}",
      };
      </script>
<script src="{{ asset('js/eventbackend/currency.min.js')}}"></script>
<script src="{{ asset('js/eventbackend/invoice.js')}}"></script>

@endsection