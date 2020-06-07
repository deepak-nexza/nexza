    
		<footer class="footer footer-black">
			<div class="container">

				<div class="footer__up-part">
					<div class="row">
						<div class="col-md-4">

							<div class="footer__widget text-widget">
								<img src="images/logo.png" alt="triptip-logo">
								<p class="footer__widget-description">
									Lorem ipsum dolor sit amet, conse ctetuer adipiscing elit, sed diam nonu mmy nibh euismod tincidunt ut laoreet dolore magna aliquam erat. 
								</p>
							</div>

						</div>
						<div class="col-md-4">

							<div class="footer__widget subscribe-widget">
								<h2 class="footer__widget-title footer__widget-title-white">
									Subscribe
								</h2>
								<p class="footer__widget-description">
									Be notified about new locations
								</p>
								<form class="footer__subscribe-form">
									<input class="footer__subscribe-input" type="text" name="email-sub" id="email-sub" placeholder="Enter your Email" />
									<button class="footer__subscribe-button footer__subscribe-button-primary" type="submit">
										<i class="la la-arrow-circle-o-right" aria-hidden="true"></i>
									</button>
								</form>
							</div>

						</div>
						<div class="col-md-4">

							<div class="footer__widget text-widget">
								<h2 class="footer__widget-title footer__widget-title-white">
									Contact Info 
								</h2>
								<p class="footer__widget-description">
									1000 5th Ave to Central Park, New York <br>
									+1 246-345-0695 <br>
									info@example.com
								</p>
							</div>

						</div>
					</div>
				</div>

				<div class="footer__down-part footer__down-part-black">
					<div class="row">
						<div class="col-md-7">
							<p class="footer__copyright">
								© Copyright 2018 - All Rights Reserved
							</p>
						</div>
						<div class="col-md-5">
							<ul class="footer__social-list">
								<li><a class="facebook" href="#"><i class="fa fa-facebook"></i></a></li>
								<li><a class="twitter" href="#"><i class="fa fa-twitter"></i></a></li>
								<li><a class="linkedin" href="#"><i class="fa fa-linkedin"></i></a></li>
								<li><a class="google" href="#"><i class="fa fa-google-plus"></i></a></li>
								<li><a class="instagram" href="#"><i class="fa fa-instagram"></i></a></li>
							</ul>
						</div>
					</div>
				</div>

			</div>

		</footer>
		<!-- End footer -->

	</div>
        
	<!-- End Container -->
	
	<script src="{{ asset('event/js/jquery.min.js') }}"></script>
	<script src="{{ asset('event/js/jquery.migrate.js') }}"></script>
	<script src="{{ asset('event/js/triptip-plugins.min.js') }}"></script>
	<script src="{{ asset('event/js/popper.js') }}"></script>
	<script src="{{ asset('event/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('event/js/jquery.countTo.js') }}"></script>
	<script src="{{ asset('event/js/script.js') }}"></script>
        
     <script type="text/javascript">
         var messages = {
            registerRoute:"{!! route('save_profile') !!}",
            _token:"{{ csrf_token() }}",
            resend_url:"{!! route('resend_otp') !!}",
            otp_url: "{{ URL::route('otp_validate') }}",
            otp_sent: "{{ Lang::get('error_message.otp_sent') }}",
            otp_blank: "{{ Lang::get('error_message.otp_blank') }}",
            otp_not_correct: "{{ trans('error_message.otp_not_correct') }}",
            otp_inactive: "{{ Lang::get('error_message.otp_inactive') }}",
            otp_expire: "{{ Lang::get('error_message.otp_expire') }}",
            email_sent: "{{ Lang::get('error_message.email_sent')}}",
            otp_attempt_left: "{{ trans('error_message.otp_attempt_left')}}",
            otp_max_limit: "{{ trans('error_message.otp_max_limit')}}",
            email_attempt_left: "{{ trans('error_message.reg.attempt_left')}}",
            email_max_limit: "{{ trans('error_message.reg.max_limit')}}",
            otp_authenticated : "{{ trans('error_message.otp_authenticated') }}",
            login_url : "{{ URL::route('login')}}",
            exception_error: "{{trans('error_message.exception_error')}}",
            otp_submit_last_attempt : "{{ trans('error_message.otp_submit_last_attempt') }}",
            otp_submit_max_limit : "{{ trans('error_message.otp_submit_max_limit') }}",
            otp_max_attempt : "{{ trans('error_message.otp_max_attempt') }}",
            promo_code_expired: "{{ trans('error_message.promo_code_expired') }}",
            otp_resent : "{{ trans('error_message.otp_resent') }}",
            state_route : "{!! route('stateindividual') !!}",
         };
    </script>
<script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
<script src="http://malsup.github.io/min/jquery.blockUI.min.js" ></script> 
<script src="{{ asset('event/js/common.js') }}"></script>
<script src="{{ asset('event/js/registerJs.js') }}"></script>
<script src="{{ asset('event/js/otp.js') }}"></script>
  <script type="text/javascript">
$(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
                $(this).toggleClass('active');
            });
        });
</script>
<!-- Color Setting -->

       
</body>

</html>