$(document).ready(function() {
//        var isSelectedEvType =  $('.event_type').val();
//        getTicketLIst(isSelectedEvType);
//        if(isSelectedEvType){
//            $('#event_type').change();
//            $('#event_type').blur();
//        }
	$('#event_type').on("change", function (event){
             var sval = $(event.target).val();
                getTicketLIst(sval);             
        });
        });
        
function getTicketLIst(selectID){
    $.ajax({
		type: "POST",
		url: messages.candidateList,
		data:{event_id:selectID,_token:messages._token},
		beforeSend: function() {
			$.blockUI({ message: '<h4>Please Wait</h4>' });
		},
		success: function(data){
                     if ($.fn.DataTable.isDataTable("#example1")) {
  $('#example1').DataTable().clear().destroy();
}
                   $(".recorFill").html(data);

                    $("#example1").DataTable({
        retrieve: true,
        });
                    $.unblockUI();
                    if(data){
                        data = data;
                    }else{
                        data = '<tr><td colspan="5">No Record found</td>/tr>';
                    }
		}
	});
}