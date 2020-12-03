$(document).ready(function() {

$('.add-listing__submit').on('click', function() {
    $("#NexzaFormsEventone").validate({
        ignore: '.select2-hidden-accessible',
        rules: {
        event_name: {
            required: true,
        },
        category:{
            required:true,
        }
         },
    messages: {
        selectnic: "Enter number of adults",
        event_name:"This Field is required",
    },
    });
// $('.selection').each(function (index) {
//                $(this).rules("add", {
//                    required: true,
//                    messages: {
//                        required: messages.common_required,
//                    }
//                });
//            });

        $('.classRequired').each(function (index,value) {
            $(this).rules("add", {
                required: true,
                messages: {
                    required: "This field is required.",
                }
            });
        });

});



//jQuery.validator.addMethod("title", function(value, element){
//    alert(value);
//   if (/^[0-9]{9}$/.test(value)) {
//       alert();
//        return true;  // FAIL validation when REGEX matches
//    } else {
//        alert(1);
//        return false;   // PASS validation otherwise
//    };
//}, "Only numerics allowed"); 
            // Preload
            $('#country_id').on("change", function (event) {  // makes sure the whole site is loaded
                //alet
                var country_id = $(event.target).val();
                $.ajax({
                    type: "POST",
                    url: messages.city_route,
                    data: {country_id: country_id, _token: messages._token},
                    beforeSend: function () {
                        $("#state_id").addClass("loader");
                    },
                    success: function (data) {
                        if (data == 0) {
                            $("#state_id").html('<option value="">No result found</option>');
                            $(".state_id").val('');
                            $(".city_id").val('');
                            $("#city_id").html('<option value="">No result found</option>');
                        } else {
                            $("#state_id").append('<option>adsf</option>').html(data);
                        }
                    }
                });
            });
            $('#state_id').on("change", function (event){  // makes sure the whole site is loaded
                     //alet
                   var state_id = $(event.target).val();
                    $.ajax({
                            type: "POST",
                            url: messages.getCity,
                            data:{state_id:state_id,_token:messages._token},
                            beforeSend: function() {
                                    $("#city_id").addClass("loader");
                            },
                            success: function(data){
                                    $("#city_id").html(data);
                            }
                    });
            });
});
function getcityList()
{
     var state_id = $('.state_id').val();
	$.ajax({
		type: "POST",
		url: messages.getCity,
		data:{state_id:state_id,_token:messages._token},
		beforeSend: function() {
			$("#city_id").addClass("loader");
		},
		success: function(data){
			$("#city_id").html(data);
		}
	});
}