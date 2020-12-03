<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('page_title')</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
     <link rel="stylesheet" href="{{ asset('eventAdmin/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('eventAdmin/dist/css/AdminLTE.min.css') }}">
    <link rel="stylesheet" href="{{ asset('eventAdmin/dist/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('eventAdmin/dist/css/skins/_all-skins.min.css') }}">
    <link rel="stylesheet" href="{{ asset('eventAdmin/plugins/datatables/dataTables.bootstrap.css') }}">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="{{ asset('eventAdmin/plugins/daterangepicker/daterangepicker-bs3.css') }}">
    <link rel="stylesheet" href="{{ asset('css/date_picker.css') }}">
    <link rel="stylesheet" href="{{ asset('eventAdmin/plugins/timepicker/bootstrap-timepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('eventAdmin/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('eventAdmin/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">') }}">
  </head>
  <body class="hold-transition skin-blue layout-top-nav">
    <div class="wrapper">

      <header class="main-header">
        <nav class="navbar navbar-static-top">
          <div class="container">
            <div class="navbar-header">
              <a href="{{ route('home') }}" class="navbar-brand"><b>EventZ</b></a>
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                <i class="fa fa-bars"></i>
              </button>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
              <ul class="nav navbar-nav">
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Event <span class="caret"></span></a>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="{{ route('create_event') }}">Create Event</a></li>
                     <li class="divider"></li>
                    <li><a href="{{ route('upcoming_event')}}">Upcoming Events</a></li>
                     <li class="divider"></li>
                    <li><a href="{{ route('past_event')}}">Past Events</a></li>
                      </ul>
                </li>
              </ul>
              <ul class="nav navbar-nav">
                <li class="dropdown">
                  <a href="{{ route('list_event_ticket')}}" class="" >Tickets</a>
<!--                  <ul class="dropdown-menu" role="menu">
                    <li><a href="{{ route('event_ticket')}}">Create Ticket</a></li>
                    <li class="divider"></li>
                    <li><a href="{{ route('list_event_ticket')}}">Event Tickets</a></li>
                      </ul>-->
                </li>
              </ul>
                <ul class="nav navbar-nav">
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Candidates <span class="caret"></span></a>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="{{ route('event_orders') }}">Event Orders</a></li>
                     <li class="divider"></li>
                    <li><a href="{{ route('candidates_registered')}}">Candidates</a></li>
                     <li class="divider"></li>
                    <li><a href="{{ route('past_event')}}">Pending Registrations</a></li>
                      </ul>
                </li>
              </ul>
                <ul class="nav navbar-nav">
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Bank Details <span class="caret"></span></a>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="{{ route('bank_details') }}">Add Bank</a></li>
                     <li class="divider"></li>
                    <li><a href="{{ route('paymentDashboard') }}">Payment Status</a></li>
                     <li class="divider"></li>
                    <li><a href="{{ route('requestpayment') }}">Request For Payment</a></li>
                     <li class="divider"></li>
                   
                      </ul>
                </li>
              </ul>
                  <ul class="nav navbar-nav">
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">User Account<span class="caret"></span></a>
                  <ul class="dropdown-menu" role="menu">
                    <li><a href="{{ route('profile') }}">Profile</a></li>
<!--                     <li class="divider"></li>
                    <li><a href="#">Event Bookings</a></li>
                     <li class="divider"></li>
                    <li><a href="#">Past Events</a></li>-->
                      </ul>
                </li>
                @if(!empty(auth()->user()->is_admin))
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown">Masters<span class="caret"></span></a>
                   
                  <ul class="dropdown-menu" role="menu">
                
                    <li><a href="{{ route('eventCategory_list') }}">Event Category</a></li>
                     <li class="divider"></li>
                  
                      </ul>
                   
                </li>
                @endif
              </ul>
            </div><!-- /.navbar-collapse -->
            <!-- Navbar Right Menu -->
              <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                  <li class="dropdown user user-menu">
                    <!-- Menu Toggle Button -->
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                      <!-- The user image in the navbar-->
                @if(!empty(auth()->user()->profile_image))
                  <img  class="user-image" src="{{asset( 'Eventupload/'. auth()->user()->profile_image) }}" >
                @else
                  <img  class="user-image" src="{{asset( 'img/profile.jpg') }}" >
                @endif
                      <!-- hidden-xs hides the username on small devices so only the image appears. -->
                      <span class="hidden-xs">{{auth()->user()->email}}</span>
                    </a>
                    <ul class="dropdown-menu">
                      <!-- The user image in the menu -->
                      <li class="user-header">
                            @if(!empty(auth()->user()->profile_image))
                  <img  class="img-circle" src="{{asset( 'Eventupload/'. auth()->user()->profile_image) }}" >
                @else
                  <img  class="img-circle" src="{{asset( 'img/profile.jpg') }}" >
                @endif
                        <p>
                          {{ auth()->user()->email }}
                          <small>Member since {{ auth()->user()->created_at }}</small>
                        </p>
                      </li>
                      <!-- Menu Body -->
                      <!-- Menu Footer-->
                      <li class="user-footer">
                        <div class="pull-left">
                          <a href="#" class="btn btn-default btn-flat">Profile</a>
                        </div>
                        
                        <div class="pull-right">
                          <a href="{{ route('logout') }}" class="btn btn-default btn-flat">Sign out</a>
                        </div>
                      </li>
                    </ul>
                  </li>
                </ul>
              </div><!-- /.navbar-custom-menu -->
          </div><!-- /.container-fluid -->
        </nav>
      </header>
        
          <!-- Full Width Column -->
      <div class="content-wrapper">
        <div class="container">
          <!-- Content Header (Page header) -->
        @yield('content')
          <!-- Main content --> 
        </div><!-- /.container -->
      </div><!-- /.content-wrapper -->
         <footer class="main-footer">
        <div class="container">
          <div class="pull-right hidden-xs">
          </div>
          <strong>Copyright &copy; 2020 <a href="">Nexzoa</a>.</strong> All rights reserved.
        </div><!-- /.container -->
      </footer>
    </div><!-- ./wrapper -->

   <script src="{{ asset('eventAdmin/plugins/jQuery/jQuery-2.1.4.min.js') }} "></script>
    <script src="{{ asset('eventAdmin/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('eventAdmin/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('eventAdmin/plugins/datatables/dataTables.bootstrap.min.js') }}"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
     <script src="{{ asset('js/bootstrap-datepicker.js') }}"></script>
     <script src="{{ asset('eventAdmin/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('eventAdmin/plugins/colorpicker/bootstrap-colorpicker.min.js') }}"></script>
    <script src="{{ asset('eventAdmin/plugins/timepicker/bootstrap-timepicker.min.js') }}"></script>
    <script src="{{ asset('eventAdmin/plugins/select2/select2.full.min.js') }}"></script>
    <script src="https://malsup.github.io/min/jquery.blockUI.min.js" ></script>  
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
        var date = new Date();
        date.setDate(date.getDate()+3);
        $('.relaseDate').datepicker({ format: 'yyyy-mm-dd',
          todayHighlight: true,
           startDate:date,
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
  </body>
</html>

        