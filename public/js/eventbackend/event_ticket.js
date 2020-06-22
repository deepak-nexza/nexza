$(document).ready(function () {
    $('.bkk_amt').on("keyup", function (event) {
        ele = $(this).closest('form').attr('id');
        var ele = $('#' + ele);
        var real_amt = ele.find('.bkk_amt').val();
        var freal_amt = currency(real_amt);
        ele.find('.amt_buy').text(freal_amt);
        var nper = freal_amt.multiply(messages.currencyAmt).divide(100);
        ele.find('.nexza_Amt').text(nper);
        var gateAmt = freal_amt.multiply(messages.gatewayAmt).divide(100);
        ele.find('.gate_Amt').text(gateAmt);
        var finalAmt = freal_amt.subtract(nper).subtract(gateAmt);
        ele.find('.finalAmt').text(finalAmt);
    });

    function callCalc(val) {
        var ele = $('#' + val);
        var real_amt = ele.find('.bkk_amt').val();
        var freal_amt = currency(real_amt);
        ele.find('.amt_buy').text(freal_amt);
        var nper = freal_amt.multiply(messages.currencyAmt).divide(100);
        ele.find('.nexza_Amt').text(nper);
        var gateAmt = freal_amt.multiply(messages.gatewayAmt).divide(100);
        ele.find('.gate_Amt').text(gateAmt);
        var finalAmt = freal_amt.subtract(nper).subtract(gateAmt);
        ele.find('.finalAmt').text(finalAmt);

    }
    $('#event_type').on("change", function (event) {
        var sval = $(event.target).val();
        $.ajax({
            type: "POST",
            url: messages.checkTicket,
            data: {event_uid: sval, _token: messages._token},
            beforeSend: function () {
            },
            success: function (data) {
//                      if(data==1)
//                      {
//                          alert('Ticket already created for this event, Close it if new ticket need to create.');
//                      }
            }
        });

    });
    $('.ticketForm').on("click", function (event) {
        var formID = $(this).attr('id');
        $('#' + formID).submit();

    });
    $('.finalsubmit').on("click", function (event) {
        var formID = $(this).closest('form');
       formID.append('<input type="text" name="submitVal" value="1">');

    });

    $('.card-link').on("click", function (event) {
        var tabID = $(this).attr('href');
        y = $(tabID).find("form").attr('id');
        if (y != 'NexzaForms')
        {
            callCalc(y);
        } else {
        }
    });
});