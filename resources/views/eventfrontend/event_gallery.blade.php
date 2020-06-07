@extends('layouts.event.layout_event')
@section('contentData')
 	<!-- about-block
			================================================== -->
		<section class="about">
			<div class="container">
				<div class="about__box">

					<div class="about__box-line">
						<div class="row">
							<div class="col-lg-8">
								
								<!-- article-post module -->
								<div class="article-post">
									Coming Soon
								</div>
								<!-- end article-post module -->

							</div>
								
								

							</div>
						</div>
					</div>

					

				</div>
			</div>
		</section>
		<!-- End about-block -->


                    
@endsection
@section('headDatajsorcss')
<link rel="stylesheet" href="{{ asset('event/css/swipebox.css')}}">
@endsection
@section('jscript')
<script src="{{ asset('event/lib/jquery-2.0.3.js')}}"></script>
<script src="{{ asset('event/js/jquery.swipebox.js')}}"></script>
<script type="text/javascript">
( function( $ ) {

	$( '.swipebox' ).swipebox( {
		useCSS : true, // false will force the use of jQuery for animations
		useSVG : true, // false to force the use of png for buttons
		initialIndexOnArray : 0, // which image index to init when a array is passed
		hideCloseButtonOnMobile : false, // true will hide the close button on mobile devices
		hideBarsDelay : 3000, // delay before hiding bars on desktop
		videoMaxWidth : 1140, // videos max width
		beforeOpen: function() {}, // called before opening
		afterOpen: null, // called after opening
		afterClose: function() {}, // called after closing
		loopAtEnd: false // true will return to the first image after the last image is reached
	} );

} )( jQuery );
</script>
@endsection