@extends('layouts.event.layout_event')
         
@section('contentData')
<section class="explore" style="padding:50px;">
			<div class="container">
				<div class="row">
                                    <div class="col-lg-3" style="border-right: 1px;">
						<div class="explore__filter">
							<form class="explore__form">
								<h3 class="explore__form-title">
                                                                    Filter Listings - (<span id="totalRec">12</span>) records
								</h3>
                                                            <select class="explore__form-input js-example-basic-multiple" id="location">
                                                                <option  value="">Location ? </option>
                                                                    @foreach($stateList as $val)
                                                                        <option value="{{$val['stateid']}}" {{ ((!empty($location) && $location == (int) $val['stateid'])? "selected":"") }}  >{{$val['statename']}} </option>
                                                                        @endforeach
								</select>
                                                                <select class="explore__form-input js-example-basic-multiple" id='etype'>
                                                                    <option value="">Select Event Type</option>
                                                                    @foreach($eventType as $val)
                                                                        <option value="{{$val['id']}}">{{$val['name']}} </option>
                                                                        @endforeach
								</select>
								<div class="">
									<span>Event Status</span>
                                                                        <ul class="explore__form-checkbox-list" id="estatus">
                                                                            <?php  $status = config('common.EVENT_STATUS'); ?>
                                                                            @foreach($status as $key=>$val)
										<li>
                                                                                    <input class="explore__input-checkbox estatus" type="checkbox" name="event_status[]" value="{{ $key }}" />
											<span class="explore__checkbox-style"></span>
											<span class="explore__checkbox-text">{{ $status[$key] }}</span>
										</li>
                                                                                @endforeach
									</ul>
								</div>
							</form>
						</div>
					</div>
					<div class="col-lg-9">
						<div class="explore__box">
							<h2 class="explore__filter-title">
								<!--<span>Results For:</span> Trending Listings-->
							</h2>


                                                        <div class="explore__wrap iso-call list-version" id="listElement">
                                                            
							</div>
                                                    <div class="center-button" style="cursor: pointer">
                                                        <input type="hidden" id="row" value="0">
                                                        <input type="hidden" id="all" value="">
                                                        <a class="btn-default btn-default-red load-more" >
                                                            <i class="fa fa-refresh"></i>
									Load More Listing
								</a>
							</div>
						</div>
					</div>

					

				</div>
			</div>
		</section>

@endsection
`       

@section('headDatajsorcss')
<!-- Datatable CSS -->
@endsection
@section('jscript')
<script src="{{ asset('event/js/search.js') }}"></script>
  <script type="text/javascript">
var messages = {
    _token:"{{ csrf_token() }}",
     searchUrl:"{!! route('searchlist') !!}",
     dataLimiter:"{!! config('common.DATA_LIMITER') !!}",
      state_route : "{!! route('statelist') !!}",
};
jQuery(document).ready(function ($) {
//    $('.estatus').click(function(){ 
//        this.setAttribute('checked',this.checked);
//        console.log( $(this).is(':checked'));
//    });
});
</script>
@endsection