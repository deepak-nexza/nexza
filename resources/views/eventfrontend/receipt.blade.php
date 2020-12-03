@extends('layouts.event.layout_event')
@section('contentData')
 	<!-- about-block
  
		================================================== -->
                
		<section class="contact-page "  >
			<div class="container">
                            
<div class="row" id='printarea'>
		<div class="col-sm-12 col-sm-offset-1">
			<div class="widget-box">
				<div class="widget-header widget-header-large">
						<a class="navbar-brand" href="/" style="color:fb646f;font-weight:bold">
                                            <i class="fa fa-map-marker" aria-hidden="true"></i> NEXZOA
					</a>
						<br>
						<br>
                                                <p>{{ !empty($order['email'])?$order['email']:null }}</p>
                                                <p>{{ !empty($order['phone'])?$order['phone']:null }}</p>
<span >{{ !empty($order['created_at'])?\Carbon\Carbon::parse($order['created_at'])->format('j F, Y '):null }}</span><br>

						<span class="invoice-info-label">Invoice:</span>

						<span class="red">{{ !empty($order['order_id'])?$order['order_id']:null }}</span>

					
					<div class="widget-toolbar hidden-480">
                                                    <a href="{{ route('download_receipt',['order'=>$order['gatway_order_id'],'encyt'=>0]) }}">Download Reciept</a>
					</div>
				</div>

				<div class="widget-body">
					<div class="widget-main padding-24">
						
						<div class="space"></div>

						<div>
							<table class="table table-striped table-bordered">
								<thead>
									<tr>
										<th class="center">#</th>
										<th>Event Name</th>
										<th class="hidden-xs">Name</th>
										<th class="hidden-480">Email</th>
										<th class="hidden-480">Phone</th>
										<th class="hidden-480">Address</th>
									</tr>
								</thead>
								<tbody>
@if(sizeof($candidates) > 0)
@foreach($candidates as $key => $val)
@php $ticketHelp = \Helpers::getTicketDetails(['ticket_id'=>$val->ticket_id,'user_id'=>$val->user_id]) @endphp
									<tr>
										<td class="center">{{++$key}}</td>

										<td>
											{{$ticketHelp['title']}}
										</td>
										<td>
											{{$val['full_name']}}
										</td>
										<td class="hidden-xs">
											{{$val['email']}}
										</td>
										<td class="hidden-480">{{$val['phone']}} </td>
										<td>{{$val['address']}}</td>
									</tr>
                                                                        @endforeach
                                                                        @endif

									
								</tbody>
							</table>
						</div>

						<div class="hr hr8 hr-double hr-dotted"></div>

						<div class="row">
							<div class="col-sm-12 pull-right">
								<h4 class="pull-right">
									Total amount :
									<span class="red">INR {{$order['order_amt']}}</span>
								</h4>
							</div>
						</div>

						<div class="space-12"></div>
						<div class="well">
							Thank You.						</div>
					</div>
				</div>
			</div>
		</div>
	</div>   
    
                        </div>
		</section>
		<!-- End contact-page-block -->
                    
@endsection
@section('headDatajsorcss')
<link rel="stylesheet" type="text/css" href="{{ asset('event/css/receipt.css') }}">
@endsection
@section('jscript')
 <script>
jQuery(document).ready(function ($) {
        $('.print').on('click',function(){
            PrintElem('printarea');
            
        });
});
function PrintElem(elem)
{
    var mywindow = window.open('', 'PRINT', 'height=400,width=600');

    mywindow.document.write('<html><head><title>' + document.title  + '</title>');
    mywindow.document.write('</head><body >');
    mywindow.document.write('<h1>' + document.title  + '</h1>');
    mywindow.document.write(document.getElementById(elem).innerHTML);
    mywindow.document.write('</body></html>');

    mywindow.print();

    return true;
}
</script>
@endsection