@extends('layouts.event.admin_layout_event')
@section('contentData')

<section class="explore">
    <br>
    <br>
    <br>

    <div class="container">
      
        <div class="row">
            <div class="col-lg-12">
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
                </div>
                </div>
            <div class="row">
                <!-- SELECT2 EXAMPLE -->
    <div class="col-sm-6">

                <div class="box-header with-border">
                    <h3 class="box-title" style="font-weight: 700">Create Event</h3>
                </div>

            </div>
            <div class="col-sm-6">

                    <button type="submit" class="btn btn-primary" style="float:right;cursor:pointer;background: #fb646f">Next</button>

            </div>
        </div>

                {!!
                Form::open(
                array(
                'name' => 'NexzaForms',
                'id' => 'NexzaForms',
                'url'=>route('save_event'),
                'method'=>'POST',
                'files' => true,
                'autocomplete' => 'off',
                'class'=>'formElement otp',
                 Form::pkey() => [
                    'event_id' => !empty($eventDetail->event_id)?$eventDetail->event_id:'',
                    ],
                )
                )
                !!}
        
        <br>
        

                <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />  
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Event Heading</label>
                            <input placeholder="Event Heading" type="text" value="{{ !empty($eventDetail->event_name)?$eventDetail->event_name:old('event_name')}}" name="event_name" class="form-control my-colorpicker1 colorpicker-element " required>
                        </div>
                        <input type="hidden" value="{{ !empty($eventDetail->event_type)?$eventDetail->event_type:''}}" name="event_type"value="" class="event_type_id">
                        <div class="form-group">
                            <label>Event Type</label>
                            <select class="form-control select2 select2-hidden-accessible" onchange="changeSelect(event)" id="event_type_id"  style="width: 100%;" tabindex="-1" aria-hidden="true" required>
                                <option> Select Event Type</option>
                                @foreach($eventlist as $item)
                                <option value="{{$item['id']}}" {{ ((isset($eventDetail->event_type) && $eventDetail->event_type== $item['id'])? "selected":"") }}>{{$item['name']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" value="{{ !empty($eventDetail->country_id)?$eventDetail->country_id:''}}" name="country_id"value="" class="country_id">
                        <div class="form-group">
                            <label>Country</label>
                            <select class="form-control select2 select2-hidden-accessible" id="country_id" onchange="changeSelect(event)"   style="width: 100%;" tabindex="-1" aria-hidden="true" required>
                                <option> Select Country</option>
                                @foreach($country_list as $item)
                                <option value="{{$item['countries_id']}}" {{ ((isset($eventDetail->country_id) && $eventDetail->country_id== $item['countries_id'])? "selected":"") }}>{{$item['countries_name']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="state_id" value="{{ !empty($eventDetail->state_id)?$eventDetail->state_id:''}}" class="state_id">
                        <div class="form-group">
                            <label>State</label>
                            <select class="form-control select2  select2-hidden-accessible" id="state_id"  onchange="changeSelect(event)" style="width: 100%;" tabindex="-1" aria-hidden="true" required>
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
                            <input type="text" placeholder="http://www.example.com" value="{{ !empty($eventDetail->site_url)?$eventDetail->site_url:old('websiteurl')}}" name="websiteurl" class="form-control my-colorpicker1 colorpicker-element" required>
                        </div>
                        @endif
                        
                        <div class="form-group">
                            <label for="exampleInputFile">Event Poster:</label>
                            <input type="file" name ='banner_image' @if(empty($eventDetail->banner_image)) required @endif>
                            <p class="help-block">.jpg,.png</p>
                        </div>
                    </div><!-- /.col -->
                    <div class="col-md-6">

                       
                        <input type="hidden" value="{{ !empty($eventDetail->event_privacy)?$eventDetail->event_privacy:''}}" name="event_privacy" value="" class="event_privacy">
                        <div class="form-group">
                            <label>Select Event Privacy</label>
                            <select class="form-control select2 select2-hidden-accessible" id='event_privacy' onchange="changeSelect(event)" style="width: 100%;" tabindex="-1" aria-hidden="true" required>
                                @foreach($mode as $item=>$val)
                                <option value="{{$item}}"  {{ ((isset($eventDetail->event_privacy) && $eventDetail->event_privacy== $item)? "selected":"") }}>{{$val}}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="status" value="{{ !empty($eventDetail->status)?$eventDetail->status:''}}" class="status">
                        <div class="form-group">
                            <label>Select status</label>
                            <select class="form-control select2 select2-hidden-accessible" id='status' onchange="changeSelect(event)" style="width: 100%;" tabindex="-1" aria-hidden="true" required>
                                <option value="">Select Status</option>
                                <option value="1"  {{ ((isset($eventDetail->status) && $eventDetail->status== 1)? "selected":"") }} >Active</option>
                                <option value="0"  {{ ((isset($eventDetail->status) && $eventDetail->status== 0)? "selected":"") }} >InActive</option>
                            </select>
                        </div>
                         <div class="form-group">
                            <label>Event Start/End Date:</label>
                            <input type="text" name='event_duration' value="{{ !empty($eventDetail->event_duration)?$eventDetail->event_duration:old('event_duration')}}" class="form-control my-colorpicker1" id="reservationtime" required>
                        </div>
                        <div class="form-group">
                            <label>Please fill minimum amount of event ticket:</label>
                            <div class="input-group">
                                <input type="text"  name="min_amount" class="form-control" value="{{  !empty($eventDetail->price)? $eventDetail->price:old('min_amount')  }}" placeholder="INR" required>
                            </div><!-- /.input group -->
                        </div><!-- /.form group --> 
                        <div class="form-group">
                            <label>Event Venue\Location:</label>
                            <div class="input-group">
                                <textarea type="text"  name="event_location" cols="70" rows="4"  class="form-control" required>{{  !empty($eventDetail->event_location)? $eventDetail->event_location:old('event_location') }}</textarea>
                            </div><!-- /.input group -->
                        </div><!-- /.form group --> 
                    </div><!-- /.col -->
                   
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>GST paid by:( For This Event )</label>
                            <br>
                            <label class="radio-inline"><br>
                                <input type="radio" name="gst" {{ ((isset($eventDetail->gst) && $eventDetail->gst== 1)? "checked":"") }} value="1" required>   Owner
                            </label><br>
                            <label class="radio-inline">
                                <input type="radio" name="gst" {{ ((isset($eventDetail->gst) && $eventDetail->gst== 2)? "checked":"") }} value="2" required>   Customer
                            </label>
                        </div><!-- /.form group -->

<!--
                        <div class="box-header">
                            <h3 class="box-title">Event Description</small></h3>
                        </div>
                        <div class="box-body pad">
                            <textarea id="editor1" name="description"  rows="10" cols="80" style="visibility: hidden; display: none;">  
                    {{ !empty($eventDetail->description)?html_entity_decode($eventDetail->description):old('description')}}
                            </textarea>
                        </div>-->



                    </div>
                </div><!-- /.row -->
                <div class="box-footer">
                      
                      <div class="row">
                          <div class="col-md-12">
                              <button type="submit" class="btn btn-primary" style="float:right;cursor:pointer;background: #fb646f">Next</button>
                              
                          </div>
                          </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
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