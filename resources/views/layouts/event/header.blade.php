<html lang="en" class="no-js">
<head>
	<title>Nexzoa</title>

	<meta charset="utf-8">

	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link rel="stylesheet" href="{{ asset('event/css/triptip-assets.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('event/css/style.css') }}">

</head>
<body>

	<!-- Container -->
	<div id="container">
            
		<!-- Header
		    ================================================== -->
		<header class="clearfix white-header-style">

			<nav class="navbar navbar-expand-lg navbar-light bg-light">
				<div class="container">

                                    <a class="navbar-brand" href="/" style="color:fb646f;font-weight:bold">
                                            <i class="fa fa-map-marker" aria-hidden="true"></i> NEXZOA
						<img src="images/logo-black.png" alt="">
					</a>

					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>

					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						<ul class="navbar-nav mr-auto">
							<li><a href="portfolio.html">Featured Events <i class="" aria-hidden="true"></i></a>
							</li>
							<li><a href="contact.html">Event Gallery</a></li>
							<li><a href="contact.html">About Us</a></li>
							<li><a href="contact.html">Contact</a></li>
						</ul>
                                            
						<ul class="navbar-nav ml-auto right-list">
                                                      @if (Auth::guest())
                    <li><a href="{{ route('login') }}"><i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i> Sign In</a></li>
                     <li><a href="{{ route('create_profile') }}"><i class="fa fa-user-o" aria-hidden="true"></i> Register</a></li>
                        @else
                       <li><a href="{{ route('create_event') }}"><i class="fa fa-user-o" aria-hidden="true"></i> My Account</a>
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