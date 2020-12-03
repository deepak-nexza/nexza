jQuery(document).ready(function ($) {
   $('#amount_id').on("keyup", function (event) {
       var rAmt = $(this).val();
        var freal_amt = currency(rAmt);
        var pendingAmt = currency(messages.amt);
        var finalpendingAmt = pendingAmt.subtract(freal_amt);
        $('#paymentpending').text(finalpendingAmt);
        if(parseInt(finalpendingAmt) <= 0){
            $('#paymentpending').text('0.00');
            alert("Requested Amount should not be more than available amount");
        }
    });
});
