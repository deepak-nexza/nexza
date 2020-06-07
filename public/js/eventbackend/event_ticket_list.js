$(document).ready(function() {
        var isSelectedEvType =  $('.event_type').val();
        getTicketLIst(isSelectedEvType);
        if(isSelectedEvType){
            $('#event_type').change();
            $('#event_type').blur();
        }
	$('#event_type').on("change", function (event){
             var sval = $(event.target).val();
                getTicketLIst(sval);             
        });
        
});
function getTicketLIst(sval){
    $.ajax({
		type: "POST",
		url: messages.listroute,
		data:{event_uid:sval,_token:messages._token},
		beforeSend: function() {
			$(".rowclass").addClass("loader");
		},
		success: function(data){
                    if(data){
                        data = data;
                    }else{
                        data = '<tr><td colspan="5">No Record found</td>/tr>';
                    }
                   $(".recorFill").html(data);
		}
	});
}