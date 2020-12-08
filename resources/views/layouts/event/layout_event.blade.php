<html lang="en" class="no-js">
<head>
	<title>Nexzoa</title>

	<meta charset="utf-8">

	<meta http-equiv="X-UA-Compatible" content="IE=edge">


<!------ Include the above in your HEAD tag ---------->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
         <link rel="icon"  href="{{asset( 'event/images/favicon-32x32.png') }}" type="image/gif" sizes="16x16"> 
	<link rel="stylesheet" href="{{ asset('event/css/triptip-assets.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('event/css/style.css') }}">
        @yield('headDatajsorcss')
</head>
<body>

	<div id="container-fluid">
            
		<!-- Header
		    ================================================== -->
		<header class="clearfix white-header-style">

			<nav class="navbar navbar-expand-lg navbar-light bg-light">
				<div class="container">

                                    <a class="navbar-brand" href="/" style="color:fb646f;font-weight:bold">
                                            <i class="fa fa-map-marker" aria-hidden="true"></i> NEXZOA
					</a>

					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>

					<div class="collapse navbar-collapse" id="navbarSupportedContent">
                                                    {!!
        Form::open(
        array(
        'url'=>route('search'),
        'method'=>'POST',
        'autocomplete' => 'off',
        'class'=>'search-form loclistClick',
        )
        )
        !!}
    <div class="search-form__input-holders ">
        <select name="location" class="search-form__input search-form__input-locatios-example-basic-multiplen js-example-basic-multiple loclist">
                <option>Location? </option>
                    </select>
            </div>
        <button style="color:red" class="search-form__submit" type="submit"><i class="fa fa-search" ></i></button>

{!! Form::close() !!}
						<ul class="navbar-nav ml-auto right-list">
                                                      @if (Auth::guest())
                    <li><a href="{{ route('login') }}"><i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i> Sign In</a></li>
                     <li><a href="{{ route('create_profile') }}"><i class="fa fa-user-o" aria-hidden="true"></i> Register</a></li>
                        @else
                       <li><a href="#"><i class="fa fa-user-o" aria-hidden="true"></i> My Account</a>
                       <ul class="dropdown">
									<li><a href="{{ route('myaccount') }}">Manage Event</a></li>
									<li><a href="{{ route('profile') }}">Profile</a></li>
									<li><a href="{{ route('password.confirm') }}">Password</a></li>
									<li><a href="{{ route('logout') }}">Logout</a></li>
								</ul>
                           </li>
                        @endif
							
						</ul>
						<a href="{{ route('create_event') }}" class="add-list-btn btn-default"><i class="fa fa-plus" aria-hidden="true"></i> Create Event</a>
					</div>
				</div>
			</nav>
		</header>
		<!-- End Header -->
                 @yield('contentData')
    <!-- Footer -->
<footer class="page-footer font-small blue-grey lighten-5">

  <div style="background-color: #fb646f;">
    <div class="container">

      <!-- Grid row-->
      <div class="row py-4 d-flex align-items-center">

        <!-- Grid column -->
        <div class="col-md-6 col-lg-5 text-center text-md-left mb-4 mb-md-0">
          <h6 class="mb-0"></h6>
        </div>
        <!-- Grid column -->

        <!-- Grid column -->
        <div class="col-md-6 col-lg-7 text-center text-md-right">

          <!-- Facebook -->
          <a class="fb-ic">
            <i class="fa fa-facebook mr-4"> </i>
          </a>
          <!-- Twitter -->
          <a class="tw-ic1">
            <i class="fa fa-twitter white-text mr-4"> </i>
          </a>
          <!-- Google +-->
          <a class="gplus-ic">
            <i class="fa fa-google white-text mr-4"> </i>
          </a>
          <!--Linkedin -->

        </div>
        <!-- Grid column -->

      </div>
      <!-- Grid row-->

    </div>
  </div>

  <!-- Footer Links -->
  <div class="container text-center text-md-left mt-5">

    <!-- Grid row -->
    <div class="row mt-3 dark-grey-text">

      <!-- Grid column -->
      <div class="col-md-3 col-lg-4 col-xl-3 mb-4">

        <!-- Content -->
        <div style="color:#fb646f;font-size: 30px;"> <i class="fa fa-map-marker"  aria-hidden="true"></i>
                                                                Nexzoa
                                                            </div>
        <hr class="teal accent-3 mb-4 mt-0 d-inline-block mx-auto" style="width: 60px;">
        <p>Nexzoa is an event plateform of digital and creative cultures. Where the event gathers people of all backgrounds from all around the world. They are designers, scientists, makers, entrepreneurs,,...</p>

      </div>
      <!-- Grid column -->

      <!-- Grid column -->
      <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mb-4">

        <!-- Links -->
        <h6 class="text-uppercase font-weight-bold">Recent Citys ON Top</h6>
        <hr class="teal accent-3 mb-4 mt-0 d-inline-block mx-auto" style="width: 60px;">
        <p>
          <a class="dark-grey-text" href="#!">Delhi</a>
        </p>
        <p>
          <a class="dark-grey-text" href="#!">Noida</a>
        </p>
        <p>
          <a class="dark-grey-text" href="#!">Indrapuram</a>
        </p>
        <p>
          <a class="dark-grey-text" href="#!">Niti Khan</a>
        </p>

      </div>
      <!-- Grid column -->

      <!-- Grid column -->
      <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-4">

        <!-- Links -->
        <h6 class="text-uppercase font-weight-bold">Useful links</h6>
        <hr class="teal accent-3 mb-4 mt-0 d-inline-block mx-auto" style="width: 60px;">
        <p>
          <a class="dark-grey-text" href="{{ route('about') }}">About Us</a>
        </p>
        <p>
          <a class="dark-grey-text" href="{{ route('event_gallery') }}">Event Gallery</a>
        </p>
        <p>
          <a class="dark-grey-text" href="#!">Policy</a>
        </p>
        <p>
          <a class="dark-grey-text" href="{{ route('contact') }}">Contact</a>
        </p>

      </div>
      <!-- Grid column -->

      <!-- Grid column -->
      <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mb-md-0 mb-4">

        <!-- Links -->
        <h6 class="text-uppercase font-weight-bold">Contact</h6>
        <hr class="teal accent-3 mb-4 mt-0 d-inline-block mx-auto" style="width: 60px;">
        <p>
          <i class="fas fa-home mr-3"></i> E-171, First Floor Noida, Sector 63, Noida, Uttar Pradesh 201301</p>
        <p>
          <i class="fas fa-envelope mr-3"></i> nexzoa@gmail.com</p>
        <p>
          <i class="fas fa-phone mr-3"></i> +91 9818672306</p>

      </div>
      <!-- Grid column -->

    </div>
    <!-- Grid row -->

  </div>
  <!-- Footer Links -->

  <!-- Copyright -->
  <div class="footer-copyright text-center text-black-50 py-3">© 2020 Copyright:
    <a class="dark-grey-text" href="https://nexzoa.com/">Nexzoa</a> | 
    <a class="dark-grey-text" href="{{ route('privacy_policy') }}">Privacy Policy</a> |
    <a class="dark-grey-text" href="{{ route('t_c') }}">Terms & Conditions</a> |
    <a class="dark-grey-text" href="{{ route('disclaimer') }}">Disclaimer</a> 
  </div>
  <!-- Copyright -->

</footer>
<!-- Footer -->

	</div>
        
	<!-- End Container -->
	
	<script src="{{ asset('event/js/jquery.min.js') }}"></script>
	<script src="{{ asset('event/js/jquery.migrate.js') }}"></script>
	<script src="{{ asset('event/js/triptip-plugins.min.js') }}"></script>
	<script src="{{ asset('event/js/popper.js') }}"></script>
	<script src="{{ asset('event/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('event/js/jquery.countTo.js') }}"></script>
	<script src="{{ asset('event/js/script.js') }}"></script>
        <script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
<script src="https://malsup.github.io/min/jquery.blockUI.min.js" ></script>  
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
            state_route : "{!! route('statelist') !!}",
         };
    </script>
    <script src="{{ asset('event/js/registerJs.js') }}"></script>
    <script src="{{ asset('event/js/otp.js') }}"></script>
<script src="{{ asset('event/js/common.js') }}"></script>
@yield('jscript')
  <script type="text/javascript">
$(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
                $(this).toggleClass('active');
            });
        });
        scrollToAnchor('{{ !empty($book_id)?$book_id:'' }}');
          function scrollToAnchor(id){
    var aTag = $("#"+id);
    $('html,body').animate({scrollTop: aTag.offset().top},'slow');
}
</script>
<!-- Color Setting -->

       
</body>

</html>