jQuery(document).ready(function ($) {

    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

$('.submit').on('click',function(e){
e.preventDefault(); 
var formData = {
    email: $('#email').val(),
    password: $('#password').val(),
    password_confirmation: $('#password-confirm').val(),
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

        }
    }); });


});
