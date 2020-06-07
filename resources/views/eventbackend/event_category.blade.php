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
                  <h3 class="box-title">Add Event Category</h3>
                </div>
            <div class="box-body">
                 {!!
        Form::open(
        array(
        'name' => 'NexzaForms',
        'id' => 'NexzaForms',
        'url'=>route('save_event_category'),
        'method'=>'POST',
        'files' => true,
        'autocomplete' => 'off',
        'class'=>'formElement otp',
        )
        )
        !!}
               @if(!empty($catList['id']))
               <input type="hidden" value="{{ $catList['id'] }}" name="e_id" >
               @endif
            <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />  
              <div class="row">
                <div class="col-md-6">
                    <input type="hidden" name="status" value="{{ !empty($catList['status'])?$catList['status']:'' }}"  class="event_type">
                     <div class="form-group">
                    <label>Select status</label>
                    <select class="form-control select2 select2-hidden-accessible" id='event_type' onchange="changeSelect(event)" style="width: 100%;" tabindex="-1" aria-hidden="true">
                        <option>Select Status</option>
                        <option value="1"  {{ ((isset($catList['status']) && $catList['status']== 1)? "selected":"") }} >Active</option>
                        <option value="2"  {{ ((isset($catList['status']) && $catList['status']== 2)? "selected":"") }}>InActive</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Event Category Name</label>
                    <input type="text" name="name" placeholder="Event category" value="{{ !empty($catList['name'])?$catList['name']:'' }}" class="form-control">
                  </div>
                    
                </div><!-- /.col -->
              </div><!-- /.row -->
              <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </div>
                {!! Form::close() !!}
            </div><!-- /.box-body -->
          </div><!-- /.box -->


        </section>
@endsection
  @push('head')
<script src="{{ asset('js/eventbackend/currency.min.js')}}"></script>
<script src="{{ asset('js/eventbackend/event_ticket.js')}}"></script>
<script>
      var messages = {
          _token: "{{ csrf_token() }}",
          currencyAmt: "{{ config('common.nexzoa_per') }}",
          gatewayAmt: "{{ config('common.nexzoa_Gateway_fee') }}",
          checkTicket: "{!! route('check_ticket') !!}",
      };
      </script>
@endpush