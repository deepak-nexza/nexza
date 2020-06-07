@extends('layouts.event.eventapp')
@section('content')
    
<section class="content">
    @if(session()->has('message'))
    <div class="alert alert-success">
        {{ session()->get('message') }}
    </div>
@endif
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
                  <h3 class="box-title">Event Category List</h3>
                </div>
            <div class="box-body">
            
            <div class="row">
                <div class="col-md-12">
                    <button style="background:red"><a  href="{{ route('eve_cat') }}" style="color:white">Add Event Category</a></button>
                    <table class="table table-bordered">
                  <thead>                  
                    <tr>
                      <th style="width: 10px">#</th>
                      <th>Name</th>
                      <th>status</th>
                      <th style="width: 40px">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                       @foreach($catList as $key=>$val)
                    <tr>
                      <td>{{$key}}</td>
                      <td>{{$val->name}}</td>
                      <td>  
                          @if(!empty($val['status']) &&  $val['status'] == 1)
                      <span class="label label-success">Active</span>
                      @else
                      <span class="label label-danger">InActive</span>
                      @endif
                      </td>
                      <td><span class="badge bg-danger"><a href="{{route('eve_cat',['e_id'=>$val->id])}}">Edit</a>/<a href="{{route('del_eve_list',['e_id'=>$val->id])}}">Delete</a></span></td>
                    </tr>
                    @endforeach
                    
                  </tbody>
                </table>
                </div>
              </div>
            <!--
              <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </div>
                {{ Form::close() }}
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