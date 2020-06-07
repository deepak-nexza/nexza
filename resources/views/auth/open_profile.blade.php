@extends('layouts.event.eventapp')
@section('content')
    
<section class="content">
@if(session()->has('message'))
    <div class="alert alert-success">
        {{ session()->get('message') }}
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
                            {!!
        Form::open(
        array(
        'name' => 'NexzaForms',
        'id' => 'NexzaForms',
        'url'=>route('update_profile'),
        'method'=>'POST',
        'files' => true,
        'autocomplete' => 'off',
        )
        )
        !!}
                <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />  
                 <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Email" class="form-control" value="{{  !empty($user->email)?$user->email:''}}">
                  </div>
                 <div class="form-group">
                    <label>Phone:</label>
                    <input type="text" name="phone" placeholder="Phone" class="form-control" value="{{  !empty($user->contact_number)?$user->contact_number:''}}">
                  </div>
                  <div class="form-group">
                    <label>Profile Image</label>
                    <input type="file" name="profile_image" >
                  </div>
                @if(!empty($user->profile_image))
                  <img style="height:200px;border-radius: 220px;width:200px" class="place-post__image" src="{{asset( 'Eventupload/'. $user->profile_image) }}" >
                @else
                  <img style="height:200px;border-radius: 220px;width:200px" class="place-post__image" src="{{asset( 'img/profile.jpg') }}" >
                @endif
                  
                </div><!-- /.col -->
                <div class="col-md-6">
                 <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" placeholder="First Name" class="form-control" value="{{  !empty($user->first_name)?$user->first_name:''}}">
                  </div>
                 <div class="form-group">
                    <label>Last Name:</label>
                    <input type="text" name="last_name" placeholder="Last Name" class="form-control" value="{{  !empty($user->last_name)?$user->last_name:''}}">
                  </div>
                </div><!-- /.col -->
            </div><!-- /.box-body -->
            <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </div>
                {!! Form::close() !!}
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