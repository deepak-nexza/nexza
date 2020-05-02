$(document).ready(function() {

	$('.bkk_amt').on("keyup", function (event){
            var real_amt = $('.bkk_amt').val();
            var freal_amt = currency(real_amt);
            $('.amt_buy').text(freal_amt);
            var nper = freal_amt.multiply(messages.currencyAmt).divide(100);
            $('.nexza_Amt').text(nper);
            var gateAmt = freal_amt.multiply(messages.gatewayAmt).divide(100);
            $('.gate_Amt').text(gateAmt);
            var finalAmt = freal_amt.subtract(nper).subtract(gateAmt);
            $('.finalAmt').text(finalAmt);
        });
        $('.bkk_amt').keyup();
});