@extends('layouts.event.eventapp')
@section('content')
    
<section class="content">
                @if ($errors->any())
		    <div class="alert alert-danger">
		    	<strong>Whoops!</strong> Please correct errors and try again!.
						<br/>
		        <ul>
		            @foreach ($errors->all() as $error)
		                <li>{{ $error }}</li>
		            @endforeach
		        </ul>
		    </div>
		@endif
          <!-- SELECT2 EXAMPLE -->
               <div class="box box-info">
                <div class="box-header with-border">
                  <h3 class="box-title">Update Profile</h3>
                </div>
               <div class="box-body">
               
               
             <div class="row">
                <div class="col-md-6">
                     {{ Form::open(array('url' => route('update_profile'),'method'=>'post')) }}
                <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />  
                 <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Email" class="form-control" value="{{  !empty($ticketDetails->title)?$ticketDetails->title:''}}">
                  </div>
                 <div class="form-group">
                    <label>Phone:</label>
                    <input type="text" name="phone" placeholder="Phone" class="form-control" value="{{  !empty($ticketDetails->title)?$ticketDetails->title:''}}">
                  </div>
                 <div class="form-group">
                    <label>Password</label>
                    <input type="text" name="password" placeholder="Password" class="form-control" value="{{  !empty($ticketDetails->title)?$ticketDetails->title:''}}">
                  </div>
                 <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="text" name="confirm_password" placeholder="confirm password" class="form-control" value="{{  !empty($ticketDetails->title)?$ticketDetails->title:''}}">
                  </div>
                  <div class="form-group">
                    <label>Profile Image</label>
                    <input type="file" name="profile_image" >
                  </div>
                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </div>
                {{ Form::close() }}
                </div><!-- /.col -->
            </div><!-- /.box-body -->
            </div><!-- /.box-body -->
          </div><!-- /.box -->


        </section>
@endsection
  @push('head')
<script src="{{ asset('js/eventbackend/event_ticket_list.js')}}"></script>
<script>
      var messages = {
          _token: "{{ csrf_token() }}",
          listroute: "{!! route('get_event_ticket') !!}",
          gatewayAmt: "{{ config('common.nexzoa_Gateway_fee') }}",
      };
        $('#ticket').DataTable({
          "paging": true,
          "lengthChange": true,
          "searching": true,
          "ordering": true,
          "info": true,
          "autoWidth": false
        });
      </script>
@endpush