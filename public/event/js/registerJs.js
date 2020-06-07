jQuery(document).ready(function ($) {

    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

$('.submit').on('click',function(e){
    $('.error').empty();
e.preventDefault(); 
$('.submit').text('Please Wait......');
var formData = {
    email: $('#email').val(),
    phone: $('#phone').val(),
    password: $('#password').val(),
    password_confirmation: $('#password_confirmation').val(),
    _token:messages._token
}
    $.ajax({
        type: "POST",
        url: messages.registerRoute,
        data: formData,
        success: function (data) {
             if(data.success==true){
                $('#otpreg').modal('show');
            }
        },
        error: function (data) {
            $('.submit').text('submit');
            $.unblockUI();
            var responseText = $.parseJSON(data.responseText);
            $.each(responseText.errors, function (index, value) {
                        var id = index.replace(/\./g, "_");
                        $('#' + id).next('label[class="error"]').remove();
                        $('#' + id).removeClass('error');
                        $('#' + id).addClass('error');
                        $('#' + id).after('<label for="' + id + '" class="error">' + value[0] + '</label>');
                    });
            
        }
    }); });


});
