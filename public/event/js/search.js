try {
    $(document).ready(function(){
        callAjaxSearch();

  $('#etype,#location').change(function(){
      callAjaxSearch();
  });

  $('#estatus').click(function(){
     callAjaxSearch();
    });
  // Load more data
 $('a.load-more').on('click',function(e){
     
     var eventstatus = [];
     $.each($(".estatus"), function(){
         var data = $(this).is(':checked');
         if(data){
         eventstatus.push($(this).val());
         }
     });
        
       
        var row = parseInt($('#row').val());
        var allcount = parseInt($('#all').val());
        var rowperpage = messages.dataLimiter;
        row = parseInt(row);
        rowperpage = parseInt(rowperpage);
        row = parseInt(row + rowperpage);
        if(row <= allcount){
             var formData  =  {
          _token:messages._token,
          location : $('#location').val(),
          etype : $('#etype').val(),
          event_status: eventstatus,
          row:row
       }
       
            $("#row").val(row);
            $.ajax({
                url:messages.searchUrl,
                type: 'post',
                data: formData,
                beforeSend:function(){
                    $(".load-more").text("Loading...");
                },
                success: function(response){
                    // Setting laittle delay while displaying new content
                    setTimeout(function() {
                        // appending posts after last post with class="post"
                        $(".item:last").after(response[0]).show().fadeIn("slow");

                        row = parseInt(row);
                        rowperpage = parseInt(rowperpage);
                        var rowno = parseInt(row + rowperpage);
                        // checking row value is greater than allcount or not
                        if(rowno > allcount){

                            // Change the text and background
                            $('.load-more').text("No more results!!");
                            $('.load-more').css("background","red");
                        }else{
                            $(".load-more").text("Load more");
                        }
                    }, 2000);

                }
            });
        }else{
            $('.center-button').addClass('hidden');

//            // Setting little delay while removing contents
//            setTimeout(function() {
//
//                // When row is greater than allcount then remove all class='post' element after 3 element
//                $('.item:nth-child(2)').nextAll('.item').remove();
//
//                // Reset the value of row
//                $("#row").val(0);
//
//                // Change the text and background
//                $('.load-more').text("Load more");
//                $('.load-more').css("background","#15a9ce");
//                
//            }, 2000);


        }

    });



function callAjaxSearch(){
    var eventstatus = [];
     $.each($(".estatus"), function(){
         var data = $(this).is(':checked');
         if(data){
         eventstatus.push($(this).val());
         }
     });
     $('.center-button').removeClass('hidden');
     $(".load-more").text("Load more");
      $('.load-more').css("background","none");
     $("#row").val('0');
     $("#all").val('');
         var formData  =  {
          _token:messages._token,
          location : $('#location').val(),
          etype : $('#etype').val(),
          event_status: eventstatus
       }
//    $.blockUI({message:'<img src=""  style="border-radius:5px; margin:5px" />'+'Please Wait...', css: {color: '#0073bb', border:'none', width:'auto', left:'50%', padding:'5px', 'border-radius':'5px', 'margin-left':'-50px'}});
    $.ajax({
       url:messages.searchUrl,
       type: "POST",
        data: formData, 
        success: function (data) {
            if(messages.dataLimiter >= data[1]){
                $('.center-button').addClass('hidden');
            }
             $.unblockUI();
             $('.list-version').removeAttr("style");
             $('.list-version').html(data[0]);
             $('#all').val(data[1]);
             $('#totalRec').text(data[1]);
        },
    });
    }




});





} catch (e) {
    if (typeof console !== 'undefined') {
        console.log(e);
    }
}