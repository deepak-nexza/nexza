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
                  <h3 class="box-title">Create Event</h3>
                </div>
            <div class="box-body">
               
               {!!
        Form::open(
        array(
        'name' => 'NexzaForms',
        'id' => 'NexzaForms',
        'url'=>!empty($eventUid)?route('update_event'):route('save_event'),
        'method'=>'POST',
        'files' => true,
        'autocomplete' => 'off',
        'class'=>'formElement otp',
        )
        )
        !!}
         @if(!empty($eventUid))
               <input type="hidden" value="{{ $eventUid }}" name="event_uid" >
               @endif
               
            <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />  
            <div class="row">
                <div class="col-md-6">
                 <div class="form-group">
                    <label>Event Heading</label>
                    <input placeholder="Event Heading" type="text" value="{{ !empty($eventDetail->event_name)?$eventDetail->event_name:old('event_name')}}" name="event_name" class="form-control my-colorpicker1 colorpicker-element">
                  </div>
                    <input type="hidden" value="{{ !empty($eventDetail->event_type)?$eventDetail->event_type:''}}" name="event_type"value="" class="event_type_id">
                    <div class="form-group">
                    <label>Event Type</label>
                    <select class="form-control select2 select2-hidden-accessible" onchange="changeSelect(event)" id="event_type_id"  style="width: 100%;" tabindex="-1" aria-hidden="true">
                        <option> Select Event Type</option>
                        @foreach($eventlist as $item)
                        <option value="{{$item['id']}}" {{ ((isset($eventDetail->event_type) && $eventDetail->event_type== $item['id'])? "selected":"") }}>{{$item['name']}}</option>
                    @endforeach
                    </select>
                  </div>
                     <input type="hidden" value="{{ !empty($eventDetail->country_id)?$eventDetail->country_id:''}}" name="country_id"value="" class="country_id">
                 <div class="form-group">
                    <label>Country</label>
                    <select class="form-control select2 select2-hidden-accessible" id="country_id" onchange="changeSelect(event)"  style="width: 100%;" tabindex="-1" aria-hidden="true">
                    <option> Select Country</option>
                        @foreach($country_list as $item)
                        <option value="{{$item['countries_id']}}" {{ ((isset($eventDetail->country_id) && $eventDetail->country_id== $item['countries_id'])? "selected":"") }}>{{$item['countries_name']}}</option>
                    @endforeach
                    </select>
                  </div>
                     <input type="hidden" name="state_id" value="{{ !empty($eventDetail->state_id)?$eventDetail->state_id:''}}" class="state_id">
                    <div class="form-group">
                    <label>State</label>
                    <select class="form-control select2  select2-hidden-accessible" id="state_id"  onchange="changeSelect(event)" style="width: 100%;" tabindex="-1" aria-hidden="true">
                        <option value="">select state</option>
                        @if(!empty($eventDetail->state_id))
                        @foreach($state_list as $key=>$val)
                        <option value="{{ $val['stateid'] }}"  {{ ((isset($eventDetail->state_id) && $eventDetail->state_id== $val['stateid'])? "selected":"") }}>{{$val['statename']}}</option>
                        @endforeach
                        @endif
                    </select>
                  </div>
                     @if(!empty(auth()->user()->is_admin))
                     <div class="form-group">
                    <label>Website Url</label>
                    <input type="text" placeholder="http://www.example.com" value="{{ !empty($eventDetail->site_url)?$eventDetail->site_url:old('websiteurl')}}" name="websiteurl" class="form-control my-colorpicker1 colorpicker-element">
                  </div>
                     @endif
                   <div class="form-group">
                      <label for="exampleInputFile">Event Poster:</label>
                      <input type="file" name ='banner_image' >
                      <p class="help-block">.jpg,.png</p>
                    </div>
                </div><!-- /.col -->
                <div class="col-md-6">
                  
                    <div class="form-group">
                    <label>Event Start/End Date:</label>
                    <input type="text" name='event_duration' value="{{ !empty($eventDetail->event_duration)?$eventDetail->event_duration:old('event_duration')}}" class="form-control my-colorpicker1" id="reservationtime">
                  </div>
                    <input type="hidden" value="{{ !empty($eventDetail->event_privacy)?$eventDetail->event_privacy:''}}" name="event_privacy" value="" class="event_privacy">
                    <div class="form-group">
                    <label>Select Event Privacy</label>
                    <select class="form-control select2 select2-hidden-accessible" id='event_privacy' onchange="changeSelect(event)" style="width: 100%;" tabindex="-1" aria-hidden="true">
                       @foreach($mode as $item=>$val)
                        <option value="{{$item}}"  {{ ((isset($eventDetail->event_privacy) && $eventDetail->event_privacy== $item)? "selected":"") }}>{{$val}}</option>
                        @endforeach
                    </select>
                  </div>
                     <input type="hidden" name="status" value="{{ !empty($eventDetail->status)?$eventDetail->status:''}}" class="status">
                    <div class="form-group">
                    <label>Select status</label>
                    <select class="form-control select2 select2-hidden-accessible" id='status' onchange="changeSelect(event)" style="width: 100%;" tabindex="-1" aria-hidden="true">
                        <option value="">Select Status</option>
                        <option value="1"  {{ ((isset($eventDetail->status) && $eventDetail->status== 1)? "selected":"") }} >Active</option>
                        <option value="0"  {{ ((isset($eventDetail->status) && $eventDetail->status== 0)? "selected":"") }} >InActive</option>
                    </select>
                  </div>
                     <div class="form-group">
                      <label>Please fill minimum amount of event ticket:</label>
                      <div class="input-group">
                          <input type="text"  name="min_amount" class="form-control" value="{{  !empty($eventDetail->price)? $eventDetail->price:old('min_amount')  }}" placeholder="INR">
                      </div><!-- /.input group -->
                    </div><!-- /.form group --> 
                     <div class="form-group">
                      <label>Event Venue\Location:</label>
                      <div class="input-group">
                          <textarea type="text"  name="event_location" cols="70" rows="4"  class="form-control">{{  !empty($eventDetail->event_location)? $eventDetail->event_location:old('event_location') }}</textarea>
                      </div><!-- /.input group -->
                    </div><!-- /.form group --> 
                </div><!-- /.col -->
                <div class="col-md-12">
                    @if(!empty($eventDetail->banner_image))
                    <div class="form-group">
                        <img class="img-responsive" src="{{ asset(config('common.uploadDir').'/'.$eventDetail->banner_image)}}" style="width:1200px;height:300px">
                    </div><!-- /.form group --> 
                    @endif
                </div>
                <div class="col-md-12">
                      <div class="form-group">
                            <label>GST paid by:( For This Event )</label>
                            <br>
                            <label class="radio-inline">
                                <input type="radio" name="gst" {{ ((isset($eventDetail->gst) && $eventDetail->gst== 1)? "checked":"") }} value="1" >Owner
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="gst" {{ ((isset($eventDetail->gst) && $eventDetail->gst== 2)? "checked":"") }} value="2">Customer
                            </label>
                    </div><!-- /.form group -->
   
                    
                    <div class="box-header">
                    <h3 class="box-title">Event Description</small></h3>
                    </div>
                    <div class="box-body pad">
                    <textarea id="editor1" name="description"  rows="10" cols="80" style="visibility: hidden; display: none;">  
                    {{ !empty($eventDetail->description)?html_entity_decode($eventDetail->description):old('description')}}
                    </textarea>
                </div>
                   
                    
                    
                </div>
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
<script src="{{ asset('js/eventbackend/backend_event.js')}}"></script>
  <script>
      var messages = {
          _token: "{{ csrf_token() }}",
          city_route: "{!! route('statelist') !!}",
          state_route: "{!! route('stateindividual') !!}",
      };
      $(function () {
          
          
        });
      </script>
@endpush