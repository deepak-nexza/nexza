@php $totalamt = ($data->order_amt * 100) @endphp
                        <button id="rzp-button1">Pay</button>

<script src="{{ asset('event/js/jquery.min.js') }}"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
       
var options = {
    "key": "{{ config('razorpay.razor_key') }}", // Enter the Key ID generated from the Dashboard
    "amount": "{{$totalamt}}",
    "currency": "INR",
    "name": "Acme Corp",
    "description": "Test Transaction",
    "image": "https://example.com/your_logo",
    "order_id": "{{ \Session::get('razorpay_order_id') }}",
    "receipt": "1232",
     "data-session-cookie-name":"default",
     "data-session-id-value": "{{ \Session::get('razorpay_order_id') }}",
    "handler": function (response){
        $('#razorpay_payment_id').val(response.razorpay_payment_id);
        $('#razorpay_order_id').val(response.razorpay_order_id);
        $('#razorpay_signature').val(response.razorpay_signature);
        $('#razorpayFrmId').submit();
    },
    	"prefill":{
        "name": "Sharma Ji",
        "email": "sharma@gmail.com",
        "contact": "9818672306"
    },
    "notes": {
        "address": "Razorpay Corporate Office",
        "order_id": "{{ $data->order_id }}"
    },
    "theme": {
        "color": "#F37254"
    }
};

</script>
 <script>
      var rzp1 = new Razorpay(options);
                            rzp1.open();
        $('.razorpay-payment-button').trigger('click');
        $('.razorpay-payment-button').css('display','none');
</script>

                    
                    <form action="{{ route('payment') }}"  method="POST" id='razorpayFrmId' >
                        <input type="hidden" name="_token" value="{!!csrf_token()!!}">
                        <input type="text" id="razorpay_order_id" name="razorpay_order_id"  value="">
                        <input type="text" id="razorpay_payment_id" name="razorpay_payment_id"  value="">
                        <input type="text" id="razorpay_signature" name="razorpay_signature"  value="">
                    </form>