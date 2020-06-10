jQuery(document).ready(function ($) {

    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

$('.saveAttendie').on('click',function(e){
    $('.error').empty();
e.preventDefault(); 
var buttonLoad= $(this).attr('data-link');
$('.'+buttonLoad).find('.fa-spin').removeClass('hidden');
var href_data = $(this).attr('data-id');
var data_link = $(this).attr('data-link');
var formdata = $('#'+href_data).serialize();
    $.ajax({
        type: "POST",
        url: messages.candidateRoute,
        data: formdata,
        success: function (data) {
            $('#'+data_link).trigger('click');
            $('#'+data_link).removeClass('show');
             $('html,body').animate({
        scrollTop: $("#container-fluid").offset().top},
        '40000');
         $('.'+href_data).css('background','#fb646f');
         $('.'+href_data).prepend('<span class="fa fa-user-circle-o">Added</span>');
            $('.'+buttonLoad).text('Successfully Saved');
            var refdata = $('#'+href_data);
            refdata.find('#full_name').val(data.full_name);
            refdata.find('#email').val(data.email);
            refdata.find('#contact_number').val(data.phone);
            refdata.find('#address').val(data.address);
            refdata.find('#recID').val(data.gid);
            refdata.find('#calID').val(data.calID);
        },
        error: function (data) {
//            $.unblockUI();
$('.'+buttonLoad).text('Save Details');
            var responseText = $.parseJSON(data.responseText);
            $.each(responseText.errors, function (index, value) {
                        var id = index.replace(/\./g, "_");
                        $('#' +href_data + id).next('label[class="error"]').remove();
                        $('#' +href_data +  id).removeClass('error');
                        $('#' +href_data +  id).addClass('error');
                        $('#' +href_data +  id).after('<label style="float:left" for="' + id + '" class="error">' + value[0] + '</label>');
                    });
            
        }
    }); 

});


$('.razorpay-payment-button').css('display','none');
$('.checkout').on('click',function(e){
    $.ajax({
        type: "POST",
        url: messages.payroute,
        data: {_token:messages._token},
        success: function (data) {
            alert(data.order)
          if(data){
              $('#data').val(data.order);
              $('#finalPayRazor').submit();
          }
        },
        error: function (data) {
            alert('something went wrong');
        }
    }); 

});



});
