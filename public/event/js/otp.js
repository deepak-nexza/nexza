try {
    function checkUserOtp(blackbox,otpVal, status, button_id, loginPassword)
    {
        $.ajax({
            type: "POST",
            url: messages.otp_url,
            data: {blackbox:blackbox, otpCode: otpVal, _token: messages._token, status: status, button_id: button_id},
            dataType: "json",
            cache: false,
            async: true,
            success: function (data) {
                 $('.otpsubmit').text('Submit');
                 $(".errmsg").html("");
                var code = data.status;
                var button_id = data.button_id;
                if (code == 0) {
                    htmlContent = bootStarpDangerAlert(messages.otp_not_correct);
                    $('.msghtml').html(htmlContent);
                    return false;
                }
                if (code == 1) {
                      htmlContent = bootStarpDangerAlert(messages.otp_inactive);
                    $('.msghtml').html(htmlContent);
                    return false;
                } else if (code == 2) {
                    htmlContent = bootStarpDangerAlert(messages.otp_expire);
                    $('.msghtml').html(htmlContent);
                    return false;
                } else if (code == 4) {
                    window.location.href = messages.login_auth_url;
                    return false;
                } else if (code == 5 || code == 3 ) {
                    htmlContent = bootStarpDangerAlert('Registration Successful');
                     $('.msghtml').html(htmlContent);
                    window.location.href = messages.login_url+'?login=login';
                    return false;
               } else if (code == 6) {
                    htmlContent = bootStarpDangerAlert(messages.otp_authenticated);
                    $('.msghtml').html(htmlContent);
                    return false;
                } else if (code == 7) {
                      window.location.href = messages.login_url+'?login=login';
                    return false;
                }else if (code == 8) {
                    htmlContent = bootStarpSuccessAlert(messages.otp_submit_last_attempt);
                    $('.errmsg').html("");
                    $(".msghtml").html(htmlContent);
                    return false;
                } else if (code == 9) {
                    $("#resent_code").hide();
                    htmlContent = bootStarpDangerAlert(messages.otp_max_attempt);
                    $('.errmsg').html("");
                    $('.msghtml').html(htmlContent);
                    return false;
                } else if (code == 10) {
                    var p = window.parent;
                    p.jQuery('#otpreg').modal('hide');
                    p.jQuery('#loginmodal').find('.modal-header').addClass('hidden');
                 }  else if (code == 11) {
                    htmlContent = bootStarpDangerAlert('You exceeded one-time verification code verification max attempt, try again after 60 mins.');
                    $('.msghtml').html("");
                    parent.window.location.href = messages.login_url;
                    return false;
                }
            },   error: function (data) {
                $('.otpsubmit').text('submit');
                if (data.status == 401) {
                    window.location.href = messages.login_url;
                    return false;
                } else {
                    $(".errmsg").html(data.responseText);
                }
            }
        });

    }

    function sendOtp(status)
    {
        var call_status = status;
        $.ajax({
            type: "POST",
            url: messages.resend_url,
            data: {resendStatus: status, _token: messages._token},
            dataType: "json",
            cache: false,
            async: true,
            success: function (data) {
                $(".errmsg").html("");
                var code = data.status;
                if (code == 1) {
                      htmlContent = bootStarpDangerAlert(messages.otp_sent + " " + messages.otp_attempt_left);
                      $('.msghtml').html(htmlContent);  
                } else if (code == 2) {
                     htmlContent = bootStarpDangerAlert(messages.otp_sent);
                     $('.msghtml').html(htmlContent);  
                } else if (code == 3) {
                    window.location.href = messages.login_url;
                    return false;
                } else if (code == 4) {
                     htmlContent = bootStarpDangerAlert(messages.email_sent + " " + messages.email_attempt_left);
                     $('.msghtml').html(htmlContent);  
                } else if (code == 5) {
                     htmlContent = bootStarpDangerAlert(messages.email_sent);
                     $('.msghtml').html(htmlContent);  
                } else if (code == 6) {
                    window.location.href = messages.login_url;
                    return false;
                } else if (code == 7) {
                    htmlContent = bootStarpDangerAlert(messages.otp_authenticated);
                    $('.msghtml').html(htmlContent);
                 } else if (code == 8) {
                    htmlContent = bootStarpDangerAlert(messages.exception_error);
                    alert(htmlContent);
                    $('.msghtml').html(htmlContent);
                 } else if (code == 9) {
                    htmlContent = bootStarpDangerAlert("You exceeded one-time verification code verification max attempt, try again after 60 mins.");
                    $('.msghtml').html(htmlContent);  
                    setTimeout(
                    function()
                    {
                      $('#loginmodal').modal('hide');
                    }, 5000);
                    //window.location.href = messages.login_url;
                    return false;
                }
            },
            error: function (data) {
                if (data.status == 401) {
                    window.location.href = messages.login_url;
                    return false;
                } else {
                    $(".errmsg").html(data.responseText);
                }
            }
        });

    }
      $("#NexzaForms").validate({
            rules: {
                otp_vals: {
                    required: true,
                        }
               
            },
            messages: {
                otp_vals: {
                    required: messages.otp_req,
                   }
            },      
     
        });
    jQuery(document).ready(function ($) {
        // validate signup form on keyup and submit

//        $(".resendOtp").click(function () {
//            $(".errmsg").html("");
//            sendOtp(messages.otp_status);
//        });
//        $(".resendEmail").click(function () {
//            $(".errmsg").html("");
//            sendOtp(messages.email_status);
//        });

        $(".otpsubmit").on("click", function () {
          $('.otpsubmit').text('Please Wait......');
           var isValid = $("#NexzaForms").valid();
                if (isValid) {
                var otpVal = $("#otp_vals").val();
                var blackbox = $("#ioBB").val();
                var status='';
                var button_id=this.id;
                if(button_id == 2) {
                    var loginPassword = $('#password', window.parent.document).val();
                } else {
                    var loginPassword = $('#nPassword').val();
                }
                $(".errmsg").html("");
                if (otpVal == "") {
                    $(".errmsg").html(messages.otp_blank);
                    return false;
                }
                var userOtpstatus=checkUserOtp(blackbox,otpVal,status,button_id, loginPassword);
            }
            else {
                $('.otpsubmit').text('submit');
            }
        });
        $(document).on("keypress", ".numberCls", function (evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            } else if (charCode == 13) {
                $(".otpsubmit").trigger("click");
                return false;
            }
            return true;
        });
       
        $('#resent_code').click(function () {
            $("#resent_code").text('resending otp...');
           $(".otpsubmit").hide();
           $.ajax({
                url: messages.resend_url,
                type: 'POST',
                dataType: "json",
                cache: false,
                async: true,
                data: { _token: messages._token},
                success: function (result) {
                    $( "#resent_code").text('Resend Otp');
                    $( "#resent_code").bind( "click" );
                    $("#resent_code").show();
                    $(".otpsubmit ").show();
                    var code = result.status;
                    if (code == 8) {
                        htmlContent = bootStarpSuccessAlert(messages.otp_submit_last_attempt);
                        $(".msghtml").html(htmlContent);
                        return false;
                    } else if (code == 9) {
                        $("#resent_code").hide();
                        //$(".otpsubmit ").hide();
                        htmlContent = bootStarpDangerAlert(messages.otp_max_attempt);
                        $('.msghtml').html(htmlContent);
                        return false;
                    } else {
                        $("#resent_code").show();
                        $(".otpsubmit ").show();
                        htmlContent = bootStarpSuccessAlert(messages.otp_resent);
                        $('.msghtml').html(htmlContent);
                    }
                },
                error: function (data) {
                $("#resent_code").text('Resend Otp');
            }
            });
        });
       
//        jQuery(document).on('keydown', '#otp_vals', function(ev) {
//            if(ev.which === 13) {                
//                $('.otpsubmit').trigger('click');
//                return false;
//            }
//        });
       
       
        $('.close').click(function() {
           var url = $('.promo_code_exriry').attr('data-rel');
           var p = window.parent;
           $('#promo_expiry_modal').modal('hide');
           p.jQuery('#loginmodal').modal('hide');
           p.jQuery('#otpreg').modal('hide');
         //  parent.window.location.href=url;
        });

    });
} catch (e) {
    if (typeof console !== 'undefined') {
        console.log(e);
    }
}