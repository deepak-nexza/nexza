<!DOCTYPE html>
 <html lang="en">

<!-- Mirrored from expert-themes.com/html/eventrox/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 28 Nov 2019 13:10:19 GMT -->
<head>
<meta charset="utf-8">
<title>EventZ</title>
<!-- Stylesheets -->
<link href="{{ asset('event/css/bootstrap.css') }}" rel="stylesheet">
<link href="{{ asset('event/css/style.css') }}" rel="stylesheet">
<link href="{{ asset('event/css/responsive.css') }}" rel="stylesheet">
<!--Color Switcher Mockup-->
<link href="{{ asset('event/css/color-switcher-design.css') }}" rel="stylesheet">
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- DataTables -->
<link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
<link rel="icon" href="images/favicon.png" type="image/x-icon">
@yield('cssData')   
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">

<!--[if lt IE 9]><script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.js"></script><![endif]-->
<!--[if lt IE 9]><script src="js/respond.js"></script><![endif]-->

</head>

<body>

<div class="page-wrapper">
 	


    <!-- Main Header-->
    <header class="main-header">
        <div class="main-box">
        	<div class="auto-container clearfix">
            	<div class="logo-box">
                	<div class="logo"><a href="index.php">
					<h2>Eventz</h2></a></div>
                </div>
               	
                <!--Nav Box-->
                <div class="nav-outer clearfix">
                    <!--Mobile Navigation Toggler-->
                    <div class="mobile-nav-toggler"><span class="icon flaticon-menu"></span></div>
                    <!-- Main Menu -->
                    <nav class="main-menu navbar-expand-md navbar-light">
                        <div class="navbar-header">
                            <!-- Togg le Button -->      
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="icon flaticon-menu-button"></span>
                            </button>
                        </div>

                        <div class="collapse navbar-collapse clearfix" id="navbarSupportedContent">
                            <ul class="navigation clearfix">
                                <li class="current dropdown"><a href="event-gallery.php">Event Gallery</a>
                                    
                                </li>
                                <li class="dropdown"><a href="">About</a>
                                    
                                </li>
                               <!--  <li class="dropdown"><a href="speakers.html">Speakers</a>
                                    <ul>
                                        <li><a href="speakers.html">Speakers</a></li>
                                        <li><a href="speakers-detail.html">Speakers Detail</a></li>
                                    </ul>
                                </li> -->
                                <li class="dropdown"><a href="upcomings.php">Schedule</a>
                                   
                                </li>
                              
                                <li><a href="contact.php">Contact</a></li>
                                  <!-- Authentication Links -->
                            </ul>
                        </div>
                    </nav>
                    <!-- Main Menu End-->

                    <!-- Outer box -->
                    <div class="outer-box">
                        <!--Search Box-->
                        <div class="search-box-outer">
                            <div class="search-box-btn"><span class="flaticon-search"></span></div>
                        </div>
                        @if (Auth::guest())
                        <div class="btn-box">
                            <a href="" class="theme-btn btn-style-one" data-toggle="modal" data-target="#modalLRForm"><span class="btn-title">Create Event</span></a>
                        </div>
                        @else
                             <!-- Button Box -->
                        <div class="btn-box">
                            <a href="" class="theme-btn btn-style-one" ><span class="btn-title">My Account</span></a>
                            <a href="{{ route('logout') }}" class="theme-btn btn-style-one" ><span class="btn-title">Logout</span></a>
                        </div>
                        @endif 
                       
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu  -->
        <div class="mobile-menu">
            <div class="menu-backdrop"></div>
            <div class="close-btn"><span class="icon flaticon-cancel-1"></span></div>
            
            <!--Here Menu Will Come Automatically Via Javascript / Same Menu as in Header-->
            <nav class="menu-box">
                <div class="nav-logo"><a href="index.html"><img src="images/logo-2.png" alt="" title=""></a></div>
                
                <ul class="navigation clearfix"><li><a href="" class="theme-btn btn-style-one" data-toggle="modal" data-target="#modalLRForm"><span class="btn-title">Create Event</span></a></li><!--Keep This Empty / Menu will come through Javascript--></ul>
            </nav>
        </div><!-- End Mobile Menu -->
    </header>
    <!--End Main Header -->
    
    
    @section('cssData')
<link rel="stylesheet" href="{{ asset('eventAdmin//dist/css/AdminLTE.min.css') }}">
@endsection
    
    
    
    
   
