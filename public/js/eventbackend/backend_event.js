$(document).ready(function() {

	// Preload
        getstate();
     $('#country_id').on("change", function (event){  // makes sure the whole site is loaded
         //alet
       var country_id = $(event.target).val();
	$.ajax({
		type: "POST",
		url: messages.city_route,
		data:{country_id:country_id,_token:messages._token},
		beforeSend: function() {
			$("#state_id").addClass("loader");
		},
		success: function(data){
			$("#state_id").html(data);
		}
	});
});
});
function getstate()
{
    var state_id = $('.state_id').val();
	$.ajax({
		type: "POST",
		url: messages.state_route,
		data:{state_id:state_id,_token:messages._token},
		beforeSend: function() {
			$("#state_id").addClass("loader");
		},
		success: function(data){
    			$("#state_id").html(data);
		}
	}); 
}