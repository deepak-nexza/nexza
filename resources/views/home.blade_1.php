@extends('layouts.event.layout_event')
         

@section('contentData')
<section class="user-detail">
			<div class="user-detail__profile">
				<div class="container">
					<div class="row">
						<div class="col-lg-5">
							<div class="user-detail__profile-box">
								<a class="user-detail__profile-image" href="#">
                                                                @if(!empty(auth()->user()->profile_image))
                                                                <img  class="img-circle" src="{{asset( 'Eventupload/'. auth()->user()->profile_image) }}" >
                                                                @else
                                                                <img  class="img-circle" src="{{asset( 'img/profile.jpg') }}" >
                                                                @endif
                                                                </a>
								<h3 class="user-detail__profile-title">
									<a href="#">{{ auth()->user()->email }} </a>
									<span class="user-detail__profile-location">
										<!--<i class="fa fa-map-marker"></i>-->
										<!--New York-->
									</span>
								</h3>
							</div>
						</div>
						<div class="col-lg-7">
							<ul class="user-detail__profile-list">
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
					</div>
				</div>
			</div>


		

		</section>

<section class="sign">
<div class="container sign__area">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card" style="text-align: center">
               Welcome To Nexza!!
                </div>
		</div>
            </div>
        </div>
</section>
@endsection
