@extends('layouts.event.layout_event')
         
@section('contentData')
 
   <!-- discover-module section
			================================================== -->
		<section class="discover discover-best">
                    
                    
                    
                    
			<div class="container">

				<p class="discover__description">
					Perfectly Planned ?
				</p>

				<h1 class="discover__title places-tab">
					Find the best event in the city.
				</h1>
                                <form action="{{ route('search') }}" class="discover__form discover-elegant__form">
					<div class="discover__input-holders">
						<input class="discover__form-input discover-elegant__form-input" type="text" name="place-event" id="place-event" placeholder="What are you looking for?" />
						<select class="discover__form-input discover-elegant__form-input js-example-basic-multiple" id="location">
                                                    <option>Location? </option>
						 @foreach($stateList as $key=>$val)
                                                 <option value="{{$val['stateid']}}">{{$val['statename']}}</option>
                                                   @endforeach
						</select>
					</div>
					<button class="discover-elegant__form-submit btn-default btn-default-red" type="submit"><i class="fa fa-search" aria-hidden="true"></i> Search</button>
				</form>


				
			</div>
		</section>
		<section class="trending-places">
			<div class="container">

				<!-- section-header module -->
				<div class="section-header">
					<h1 class="section-header__title">
						Upcoming Events
					</h1>
					<p class="section-header__description">
					</p>
				</div>
				<!-- end section-header module -->


				<div class="trending-places__box owl-wrapper">
					<div class="owl-carousel" data-num="3">
					 @foreach($eventList as $key=>$val)
                                         
						<div class="item">

							<!-- place-post module -->
							<div class="place-post">
								<div class="place-post__gal-box">
                                                                    <a href="{{route('event_detail',['id'=>$val['event_uid']])}}"><img style="height:200px;" class="place-post__image" src="{{asset( 'Eventupload/'. $val['banner_image']) }}" alt="place-image"></a>
									<span class="place-post__rating"><i class="fa fa-rupee"></i> {{ !empty($val['price'])?$val['price']:'Free' }}</span>
									<a class="place-post__like" href="#"><i class="fa fa-heart-o" aria-hidden="true"></i></a>
								</div>
								<div class="place-post__content">
									<p class="place-post__info">
										<i class="fa fa-clock-o" aria-hidden="true"></i>
										{{ \Carbon\Carbon::parse($val['start_date'])->format('j F, Y ') }} 
									</p>
									<h2 class="place-post__title">
										<a href="{{route('event_detail',['id'=>$val['event_uid']])}}">{{ $val['event_name'] }}</a>
                                                                                
									</h2>
									<p class="place-post__description">
                                                                            
											{{ substr(strip_tags($val['description']),0,100) }}....
									</p>
									<p class="place-post__address">
										<i class="fa fa-map-marker" aria-hidden="true"></i>
										{{ $val['event_location'] }}
									</p>
								</div>
							</div>
							<!-- end place-post module -->

						</div>
					@endforeach
					
					
					
						
					</div>
				</div>
				<div class="center-button">
					<a class="text-btn" href="{{ route('search')}}">
						View All Events </span>
					</a>
				</div>
			</div>
		</section>
		<!-- End trending-places-block -->

		<section class="trending-places">
			<div class="container">

				<!-- section-header module -->
				<div class="section-header">
					<h1 class="section-header__title">
						Music Events
					</h1>
					<p class="section-header__description">
					</p>
				</div>
				<!-- end section-header module -->


				<div class="trending-places__box owl-wrapper">
					<div class="owl-carousel" data-num="3">
				 @foreach($eventList as $key=>$val)
                                         
						<div class="item">

							<!-- place-post module -->
							<div class="place-post">
								<div class="place-post__gal-box">
                                                                    <a href="{{route('event_detail',['id'=>$val['event_uid']])}}"><img style="height:200px;" class="place-post__image" src="{{asset( 'Eventupload/'. $val['banner_image']) }}" alt="place-image"></a>
									<span class="place-post__rating"><i class="fa fa-rupee"></i> {{ !empty($val['price'])?$val['price']:'300' }}</span>
									<a class="place-post__like" href="#"><i class="fa fa-heart-o" aria-hidden="true"></i></a>
								</div>
								<div class="place-post__content">
									<p class="place-post__info">
										<i class="fa fa-clock-o" aria-hidden="true"></i>
										{{ \Carbon\Carbon::parse($val['start_date'])->format('j F, Y ') }} 
									</p>
									<h2 class="place-post__title">
										<a href="{{route('event_detail',['id'=>$val['event_uid']])}}">{{ $val['event_name'] }}</a>
                                                                                
									</h2>
									<p class="place-post__description">
                                                                            
											{{ substr(strip_tags($val['description']),0,100) }}....
									</p>
									<p class="place-post__address">
										<i class="fa fa-map-marker" aria-hidden="true"></i>
										{{ $val['event_location'] }}
									</p>
								</div>
							</div>
							<!-- end place-post module -->

						</div>
					@endforeach
					
						
					</div>
				</div>
				<div class="center-button">
					<a class="text-btn" href="{{ route('search')}}">
						View All Events </span>
					</a>
				</div>
			</div>
		</section>
		<!-- End trending-events-block -->
<section class="trending-places">
			<div class="container">

				<!-- section-header module -->
				<div class="section-header">
					<h1 class="section-header__title">
						Education Events
					</h1>
					<p class="section-header__description">
					</p>
				</div>
				<!-- end section-header module -->


				<div class="trending-places__box owl-wrapper">
					<div class="owl-carousel" data-num="3">
					 @foreach($eventList as $key=>$val)
                                         
						<div class="item">

							<!-- place-post module -->
							<div class="place-post">
								<div class="place-post__gal-box">
                                                                    <a href="{{route('event_detail',['id'=>$val['event_uid']])}}"><img style="height:200px;" class="place-post__image" src="{{asset( 'Eventupload/'. $val['banner_image']) }}" alt="place-image"></a>
									<span class="place-post__rating"><i class="fa fa-rupee"></i> {{ !empty($val['price'])?$val['price']:'300' }}</span>
									<a class="place-post__like" href="#"><i class="fa fa-heart-o" aria-hidden="true"></i></a>
								</div>
								<div class="place-post__content">
									<p class="place-post__info">
										<i class="fa fa-clock-o" aria-hidden="true"></i>
										{{ \Carbon\Carbon::parse($val['start_date'])->format('j F, Y ') }} 
									</p>
									<h2 class="place-post__title">
										<a href="{{route('event_detail',['id'=>$val['event_uid']])}}">{{ $val['event_name'] }}</a>
                                                                                
									</h2>
									<p class="place-post__description">
                                                                            
											{{ substr(strip_tags($val['description']),0,100) }}....
									</p>
									<p class="place-post__address">
										<i class="fa fa-map-marker" aria-hidden="true"></i>
										{{ $val['event_location'] }}
									</p>
								</div>
							</div>
							<!-- end place-post module -->

						</div>
					@endforeach
					
					
						
					</div>
				</div>
				<div class="center-button">
					<a class="text-btn" href="{{ route('search')}}">
						View All Events </span>
					</a>
				</div>
			</div>
		</section>

@endsection
@section('jscript')
`       <script src="{{ asset('event/js/registerJs.js') }}"></script>
<script src="{{ asset('event/js/otp.js') }}"></script>
@endsection