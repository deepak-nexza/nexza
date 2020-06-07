@extends('layouts.event.layout_event')
@section('contentData')
 
             <!-- listing-detail
			================================================== -->
		<section class="listing-detail">
			<div class="listing-detail__gal">
				<div class="listing-detail__title-box">
					<div class="container">
						<div class="row">
							<div class="col-sm-9">
								
								<h1 class="listing-detail__title listing-detail__title-black">
									<span>{{ $eventDetail['event_name'] }}</span>
								</h1>
								<p class="listing-detail__address">
									<i class="fa fa-map-marker"></i>{{ $eventDetail['event_location'] }}
<!--									<--<span class="listing-detail__dollar-rate">
										<i class="fa fa-usd red-col" aria-hidden="true"></i>
										<i class="fa fa-usd red-col" aria-hidden="true"></i>
										<i class="fa fa-usd" aria-hidden="true"></i>
										<i class="fa fa-usd" aria-hidden="true"></i>
									</span>-->
                                                                        
                                                                        
								</p>
                                                                <span class="listing-detail__description-review-numb" style="font-weight:bold;color:black">
										{{ \Carbon\Carbon::parse($eventDetail['start_date'])->format('j F, Y ') }}
									</span>
                                                                <span class="listing-detail__description-review-numb" style="font-weight:bold;color:blue">
										 @php $data =  Carbon\Carbon::parse($eventDetail['start_date'])->diffInDays(Carbon\Carbon::now()) @endphp 
                                                                                @if($data>0)
                                                                                | {{$data}}  Days To Go
                                                                                @endif
									</span>
                                                                <span class="listing-detail__description-review-numb" style="font-weight:bold;color:blue">
										{{ !empty($eventDetail['price'])? '| Registration Fees : ₹ '.$eventDetail['price'].' Onwards':'Free' }} 
    									</span>
							</div>
							<div class="col-sm-3">
<!--								<--<div class="listing-detail__buttons listing-detail__buttons-icons">
									<a class="btn-default" href="#">
										<i class="la la-heart-o" aria-hidden="true"></i>
									</a>
									<a class="btn-default" href="#">
										<i class="la la-share-alt" aria-hidden="true"></i>
									</a>
								</div>-->

							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="listing-detail__content">
				<div class="container">
					<div class="row">
						<div class="col-lg-8">
							<div class="listing-detail__content-box listing-detail__content-box-nopadding">

								<div class="listing-detail__scroll-menu-box">
									<ul class="listing-detail__scroll-menu listing-detail__menu-top-border navigate-section">
										<li>
											<a class="active" href="#overview-box">Overview</a>
										</li>
<!--										<li>
											<a href="#photos-box">Gallery</a>
										</li>-->
@php $preg_replace = preg_replace('/\s+/', '-', $eventDetail['event_name']); @endphp
@if(count($ticket_list)>0 || !empty($eventDetail['site_url']) )
										<li>
                                                                                    <a href="{{ route('book_event',['bookID'=>$preg_replace.'-'.$eventDetail['event_uid']]) }}" style="background:#fb646f;color:white">Book Now</a>
										</li>
                                                                                @endif
									</ul>
								</div>

								<!-- overview box -->
								<div class="listing-detail__galleria">
									<div class="item-image">
										<img src="{{ asset('Eventupload/'.$eventDetail['banner_image']) }}" alt="">
									</div>
									<div class="item-image small-size">
										<img src="{{ asset('Eventupload/'.$eventDetail['banner_image']) }}" alt="">
									</div>
									<div class="item-image small-size">
										<img src="{{ asset('Eventupload/'.$eventDetail['banner_image']) }}" alt="">
									</div>
									<!--<a href="#photos-box" class="navigate-btn"><i class="la la-camera"></i>View all photos (21)</a>-->
								</div>

								<!-- overview box -->
								<div class="listing-detail__overview" id="overview-box">
									<h2 class="listing-detail__content-title">Overview</h2>
									<p class="listing-detail__content-description">
										{!! $eventDetail['description'] !!}
									</p>
								</div>
								<!-- tips & reviews-box -->
								<div class="listing-detail__reviews" id="booking">
									</h2>
									<div class="listing-detail__reviews-box">
                                                                @if(count($ticket_list)>0)

                                    @elseif(!empty($eventDetail['site_url']) )
                                    <h2 class="contact-form__title">
								Registration Url
							</h2>
                                    <li>
                                        <i class="la la-link"></i>
                                        <a href="{{ $eventDetail['site_url'] }}">{{ $eventDetail['site_url'] }}</a>
                                    </li>
                                    @endif

									</div>

								</div>

								<!-- gallery-box -->
<!--								<div class="listing-detail__photos" id="photos-box">
									<h2 class="listing-detail__content-title">Photos</h2>
									<div class="listing-detail__photos-inner iso-call" data-item-showen="6">
										<div class="item">
											<img src="upload/g1.jpg" alt="">
										</div>
										<div class="item">
											<img src="upload/g2.jpg" alt="">
										</div>
										<div class="item">
											<img src="upload/g3.jpg" alt="">
										</div>
										<div class="item">
											<img src="upload/g4.jpg" alt="">
										</div>
										<div class="item">
											<img src="upload/g5.jpg" alt="">
										</div>
										<div class="item">
											<img src="upload/g6.jpg" alt="">
										</div>
										<div class="item">
											<img src="upload/g1.jpg" alt="">
										</div>
										<div class="item">
											<img src="upload/g2.jpg" alt="">
										</div>
										<div class="item">
											<img src="upload/g3.jpg" alt="">
										</div>
										<div class="item">
											<img src="upload/g4.jpg" alt="">
										</div>
										<div class="item">
											<img src="upload/g5.jpg" alt="">
										</div>
									</div>
									<a href="#" 
										class="load-others" 
										data-less-text="View Less Photos"
										data-more-text="View all Photos"
										data-parent-class="listing-detail__photos-inner">
										<i class="la la-camera"></i>
										<span>View all photos (11)</span>
									</a>
								</div>-->

							</div>
						</div>
						<div class="col-lg-4">
							<div class="sidebar">

								<div class="sidebar__map-widget">
									<h2 class="sidebar__widget-title">
										Location
									</h2>
									<iframe
  width="400"
  height="340"
  frameborder="0" style="border:0"
  src="https://www.google.com/maps/embed/v1/place?key=AIzaSyBg0Mb0IqBD69cnEKY6t2YyvhIlGhZDVUk&q={{ $eventDetail['event_location'] }}" allowfullscreen>
</iframe>
								</div>

								<div class="sidebar__widget sidebar__widget-listing-details">
									<h2 class="sidebar__widget-title">
										Details
									</h2>
									<ul class="sidebar__listing-list">
										<li>
											<i class="la la-map-marker"></i>
											{{ $eventDetail['event_name'] }} {{ $eventDetail['statename'] }}
										</li>
										<li>
											<i class="la la-mobile-phone"></i>
											+91 {{ $eventDetail['contact_number'] }}
										</li>
										<li>
											<i class="la la-link"></i>
                                                                                        <a href="{{ $eventDetail['site_url'] }}">{{ $eventDetail['site_url'] }}</a>
										</li>
									</ul>
								</div>

								<div class="sidebar__widget sidebar__widget-author">
									<h2 class="sidebar__widget-title">
										Publisher
									</h2>
									
									<!-- Author-wrapper module -->
									<div class="author-wrapper">
										<div class="author-wrapper__profile">
											<div class="row">
												<div class="col-12">
													<div class="author-wrapper__content">
														<a class="author-wrapper__image" href="#">
                                                                                                                    @if(!empty($eventDetail['profile_image']))
                                                                                                                    <img  class="user-image" src="{{asset( 'Eventupload/'. $eventDetail['profile_image']) }}" >
                                                                                                                    @else
                                                                                                                    <img  class="user-image" src="{{asset( 'img/profile.jpg') }}" >
                                                                                                                    @endif
                                                                                                                
                                                                                                                </a>
                                                                                                            
														<h3 class="author-wrapper__title">
															<a href="#">{{ !empty($eventDetail['first_name'])? $eventDetail['first_name'].' '.$eventDetail['last_name'] : $eventDetail['email'] }}</a>
															<span class="author-wrapper__location">
																{{$eventDetail['statname'] }},{{ $eventDetail['event_location'] }}
															</span>
														</h3>
													</div>
											</div>
										</div>
                                                                                    <br><br>
										<ul class="author-wrapper__list">
											<li>
									<span>{{ $open }}</span>
									Open Events
								</li>
								<li>
									<span>{{ $close }}</span>
									Closed Events
								</li>
											
										</ul>
									</div>
									<!-- End Author-wrapper module-->
								</div>

								

							</div>
						</div>
					</div>
				</div>
			</div>

		</section>
		<!-- End listing-detail -->
                    
                    
                    
@endsection
