<!doctype html>


<html lang="en" class="no-js">

<!-- Mirrored from nunforest.com/triptip-demo/user-page.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 17 May 2020 07:11:26 GMT -->
<head>
	<title>@yield('page_title')</title>

	<meta charset="utf-8">

	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	
	<link rel="stylesheet" href="css/triptip-assets.min.css">
	<link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" href="{{ asset('eventAdmin/dist/css/AdminLTE.min.css') }}">
    <link rel="stylesheet" href="{{ asset('eventAdmin/plugins/datatables/dataTables.bootstrap.css') }}">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="{{ asset('eventAdmin/plugins/daterangepicker/daterangepicker-bs3.css') }}">
    <link rel="stylesheet" href="{{ asset('css/date_picker.css') }}">
    <link rel="stylesheet" href="{{ asset('eventAdmin/plugins/timepicker/bootstrap-timepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('eventAdmin/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('eventAminLTE.min.cssdmin/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">') }}">
</head>
<body>

	<!-- Container -->
	<div id="container">
		<!-- Header
		    ================================================== -->
		<header class="clearfix white-header-style fullwidth-with-search">

			<nav class="navbar navbar-expand-lg navbar-light bg-light">
				<div class="container-fluid">

					  <a class="navbar-brand" href="/" style="color:fb646f;font-weight:bold">
                                            <i class="fa fa-map-marker" aria-hidden="true"></i> NEXZOA
					</a>

					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>

					<div class="collapse navbar-collapse" id="navbarSupportedContent">
                                            
						<ul class="navbar-nav mr-auto">
							<li>
								<a class="active" href="index-2.html">Home <i class="fa fa-caret-down" aria-hidden="true"></i></a>
								<ul class="dropdown">
									<li><a href="{{ route('create_event') }}">Create Event</a></li>
									<li><a href="home2.html">Homepage 2</a></li>
									<li><a href="home3.html">Homepage 3</a></li>
									<li><a href="home4.html">Homepage 4</a></li>
								</ul>
							</li>
							<li><a href="portfolio.html">Pages <i class="fa fa-caret-down" aria-hidden="true"></i></a>
								<div class="megadropdown">
									<div class="dropdown-box">
										<span>Explore pages</span>
										<ul class="dropdown-list">
											<li><a href="explore-sidebar-map.html">Explore map sidebar</a></li>
											<li><a href="explore-fullwidth-map.html">Explore fullwidth map</a></li>
											<li><a href="explore-category.html">Explore by Category</a></li>
											<li><a href="explore-fullwidth-map-list.html">Explore list</a></li>
										</ul>
									</div>
									<div class="dropdown-box">
										<span>Listing detail pages</span>
										<ul class="dropdown-list">
											<li><a href="listing-detail-large.html">Listing detail large image</a></li>
											<li><a href="listing-detail-sidebar.html">Listing detail with sidebar</a></li>
											<li><a href="listing-detail-fullwidth.html">Listing detail fullwidth</a></li>
										</ul>
									</div>
									<div class="dropdown-box">
										<span>Simple pages</span>
										<ul class="dropdown-list">
											<li><a href="about.html">About Us</a></li>
											<li><a href="blog.html">Blog</a></li>
											<li><a href="single-post.html">Blog Single Post</a></li>
											<li><a href="error-404.html">404 Error Page</a></li>
										</ul>
									</div>
									<div class="dropdown-box">
										<span>Account pages</span>
										<ul class="dropdown-list">
											<li><a href="add-listing.html">Add listing</a></li>
											<li><a href="sign-page.html">Login &amp; Registration</a></li>
											<li><a href="user-page.html">User page</a></li>
										</ul>
									</div>
								</div>
							</li>
							<li><a href="contact.html">Contact</a></li>
						</ul>
						
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
						<a href="add-listing.html" class="add-list-btn btn-default"><i class="fa fa-plus" aria-hidden="true"></i> Add Listing</a>
					</div>
				</div>
			</nav>
		</header>
		<!-- End Header -->

		<!-- user-detail
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
	
	<script src="{{ asset('eventAdmin/plugins/jQuery/jQuery-2.1.4.min.js') }} "></script>
	<script src="{{ asset('event/js/jquery.migrate.js') }}"></script>
	<script src="{{ asset('event/js/triptip-plugins.min.js') }}"></script>
	<script src="{{ asset('event/js/popper.js') }}"></script>
	<script src="{{ asset('event/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('event/js/jquery.countTo.js') }}"></script>
	<script src="{{ asset('event/js/script.js') }}"></script>
        
    <script src="{{ asset('eventAdmin/plugins/datatables/jquery.dataTables.min.js') }}"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
     <script src="{{ asset('js/bootstrap-datepicker.js') }}"></script>
     <script src="{{ asset('eventAdmin/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('eventAdmin/plugins/colorpicker/bootstrap-colorpicker.min.js') }}"></script>
    <script src="{{ asset('eventAdmin/plugins/timepicker/bootstrap-timepicker.min.js') }}"></script>
    <script src="{{ asset('eventAdmin/plugins/select2/select2.full.min.js') }}"></script>
    @stack('head')
  
    <script>
        
    function changeSelect(event){
        
        var select2Value = $(event.target).val();
        $('.'+event.target.id).val(select2Value);
    }
      $(function () {
        $(".select2").select2();
        $('.datepicker').datepicker({ format: 'mm/dd/yyyy',
          todayHighlight: true,
          autoclose: true,
          });
        $('.datepicker1').datepicker({ format: 'yyyy-mm-dd',
          todayHighlight: true,
          autoclose: true,
          });
        $('#reservation').daterangepicker();
        $('#reservationtime').daterangepicker({timePicker: true, timePickerIncrement: 30, format: 'YYYY-MM-DD h:mm A'});
        $('#reservationtime1').daterangepicker({timePicker: true, timePickerIncrement: 30, format: 'YYYY-MM-DD h:mm A'});
        $(".timepicker").timepicker({
          showInputs: false
        });
      });
      
    </script>
     <script src="https://cdn.ckeditor.com/4.4.3/standard/ckeditor.js"></script>
    <script src="{{ asset('eventAdmin/pluginsbootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}"></script>
    <script>
      $(function () {
        CKEDITOR.replace('editor1');
        $(".textarea").wysihtml5();
      });
    </script>
  <script type="text/javascript">
$(document).ready(function () {
  



            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
                $(this).toggleClass('active');
            });
        });
        scrollToAnchor('{{ !empty($book_id)?$book_id:'' }}');
          function scrollToAnchor(id){
              if(id){
    var aTag = $("#"+id);
    $('html,body').animate({scrollTop: aTag.offset().top},'slow');
              }
}
</script>
<!-- Color Setting -->

       
</body>

</html>