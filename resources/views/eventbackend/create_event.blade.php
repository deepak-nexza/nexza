@extends('layouts.event.admin_layout_event')
@section('contentData')

<section class="add-listing">
    <div class="add-listing__title-box">
        <div class="container">
            <h1 class="add-listing__title">
                Add Event Now
            </h1>
        </div>
    </div>
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
    <div class="user-detail__scroll-menu-box">
        <div class="container">
            <ul class="user-detail__scroll-menu navigate-section">
                <li>
                    <a class="active" href="#general-info">General Information</a>
                </li>
                <li>
                    <a href="#location-box">Location</a>
                </li>
                <li>
                    <a href="#opening-box">Opening Time</a>
                </li>
                <li>
                </li>
                <li>
                    <a href="#social-box">Social Networks</a>
                </li>
            </ul>
        </div>
    </div>
        {!!
        Form::open(
        array(
        'name' => 'NexzaFormsEventone',
        'id' => 'NexzaFormsEventone',
        'url'=>route('save_event'),
        'method'=>'POST',
        'files' => true,
        'autocomplete' => 'off',
        'class'=>'add-listing__form otp',
        Form::pkey() => [
        'event_id' => !empty($eventDetail->event_id)?$eventDetail->event_id:'',
        ],
        )
        )
        !!}

        <div class="container">

            <!-- form box -->
            <div class="add-listing__form-box" id="general-info">

                <h2 class="add-listing__form-title">
                    General Information:
                </h2>

                <div class="add-listing__form-content">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="add-listing__label" for="list-title">
                                Event Heading:
                            </label>
                            <input class="form-control classRequired"  value="{{ !empty($eventDetail->event_name)?$eventDetail->event_name:old('event_name')}}" name="event_name" placeholder="Event Heading" type="text" />
                        </div>
                        <div class="col-md-6">
                            <label class="add-listing__label" for="category">
                                Event Type:
                            </label>
                            <input type="hidden" value="{{ !empty($eventDetail->event_type)?$eventDetail->event_type:''}}" name="event_type"value="" class="event_type_id">
                            <select class="add-listing__input js-example-basic-multiple classRequired" onchange="changeSelect(event)" name="category" id="event_type_id">
                                <option value=""> Select Event Type</option>
                                @foreach($eventlist as $item)
                                <option value="{{$item['id']}}" {{ ((!empty($eventDetail->event_type) && $eventDetail->event_type== $item['id'])? "selected":"") }}>{{$item['name']}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <label class="add-listing__label" for="price">
                                Select Event Privacy:
                            </label>
                            <input type="hidden" value="{{ !empty($eventDetail->event_privacy)?$eventDetail->event_privacy:''}}" name="event_privacy" value="" class="event_privacy">
                            <select class="add-listing__input js-example-basic-multiple selection"  id="event_privacy" onchange="changeSelect(event)">
                                @foreach($mode as $item=>$val)
                                <option value="{{$item}}"  {{ ((!empty($eventDetail->event_privacy) && $eventDetail->event_privacy== $item)? "selected":"") }}>{{$val}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="add-listing__label" for="price">
                                Select status:
                            </label>
                            <input type="hidden" name="status" value="{{ !empty($eventDetail->status)?$eventDetail->status:''}}" class="status">
                            <select class="add-listing__input js-example-basic-multiple classRequired" id='status' onchange="changeSelect(event)" >
                                <option value="">Select Status</option>
                                <option value="1"  {{ ((!empty($eventDetail->status) && $eventDetail->status== 1)? "selected":"") }} >Active</option>
                                <option value="0"  {{ ((!empty($eventDetail->status) && $eventDetail->status== 0)? "selected":"") }} >InActive</option>
                            </select>
                        </div>
                        @if(!auth()->user()->is_admin)
                        <div class="col-md-4">
                            <label class="add-listing__label" for="price">
                                Please fill minimum amount of event ticket:
                            </label>
                            <input type="text"  name="min_amount" class="form-control" value="{{  !empty($eventDetail->price)? $eventDetail->price:old('min_amount')  }}" placeholder="INR" required>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
					
					<!-- form box -->
                        <div class="add-listing__form-box" id="location-box">

                            <h2 class="add-listing__form-title">
                                Location:
                            </h2>

                            <div class="add-listing__form-content">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="add-listing__label" for="price">
                                            Country:
                                        </label>
                                        <input type="hidden" value="{{ !empty($eventDetail->country_id)?$eventDetail->country_id:''}}" name="country_id"value="" class="country_id">
                                        <select class="add-listing__input js-example-basic-multiple"  id="country_id" onchange="changeSelect(event)">
                                            <option> Select Country</option>
                                            @foreach($country_list as $item)
                                            <option value="{{$item['countries_id']}}" {{ ((!empty($eventDetail->country_id) && $eventDetail->country_id== $item['countries_id'])? "selected":"") }}>{{$item['countries_name']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="add-listing__label" for="price">
                                            State:
                                        </label>
                                        <input type="hidden" value="{{ !empty($eventDetail->state_id)?$eventDetail->state_id:''}}" name="state_id"value="" class="state_id">
                                        <select class="add-listing__input js-example-basic-multiple" id="state_id"  onchange="changeSelect(event)">
                                            <option value="">select state</option>
                                            @if(!empty($eventDetail->state_id))
                                            @foreach($state_list as $key=>$val)
                                            <option value="{{ $val['stateid'] }}"  {{ ((!empty($eventDetail->state_id) && $eventDetail->state_id== $val['stateid'])? "selected":"") }}>{{$val['statename']}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="add-listing__label" for="price">
                                            City:
                                        </label>
                                        <input type="hidden" value="{{ !empty($eventDetail->city_id)?$eventDetail->city_id:''}}" name="city_id"value="" class="city_id">
                                        <select class="add-listing__input js-example-basic-multiple Required" id="city_id"  onchange="changeSelect(event)">
                                            <option value="">select City</option>
                                            @if(!empty($eventDetail->city_id))
                                            @foreach($city_list as $key=>$val)
                                            <option value="{{ $val['city_id'] }}"  {{ ((!empty($eventDetail->city_id) && $eventDetail->city_id== $val['city_id'])? "selected":"") }}>{{$val['name']}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="add-listing__label" for="price">
                                            Address:
                                        </label>
                                        <textarea class="add-listing__textarea Required" style="height: 3em;" name="event_location" id="description" placeholder="Describe the listing" >{{  !empty($eventDetail->event_location)? $eventDetail->event_location:old('event_location') }}</textarea>
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>
					
					<!-- form box -->
                                <div class="add-listing__form-box" id="opening-box">

                                    <h2 class="add-listing__form-title">
                                        Opening Time:
                                    </h2>

                                    <div class="add-listing__form-content">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label class="add-listing__label" for="price">
                                                    Event Start/End Date:
                                                </label>
                                                <input type="text" name='event_duration' value="{{ !empty($eventDetail->event_duration)?$eventDetail->event_duration:old('event_duration')}}" class="form-control my-colorpicker1" id="reservationtime" required>
                                            </div>
                                        </div>
                                        <br>
                                    </div>

                                </div>


                                <!-- form box -->
                                <div class="add-listing__form-box" id="social-box">

                                    <h2 class="add-listing__form-title">
                                        Social Networks:
                                    </h2>

                                    <div class="add-listing__form-content">
                                        <div class="row">
                                            <div class="col-md-3 col-sm-6">
                                                <label class="add-listing__label" for="facebook">
                                                    Facebook <span>(optional)</span>:
                                                </label>
                                                <input class="add-listing__input" type="text" name="facebook" value="{{ !empty($eventDetail->facebook)?$eventDetail->facebook:old('facebook')}}" id="facebook" placeholder="Facebook URL" />
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <label class="add-listing__label" for="twitter">
                                                    Twitter <span>(optional)</span>:
                                                </label>
                                                <input class="add-listing__input" type="text" name="twitter" value="{{ !empty($eventDetail->twitter)?$eventDetail->twitter:old('twitter')}}" id="twitter" placeholder="Twitter URL" />
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <label class="add-listing__label" for="youtube">
                                                    YouTube <span>(optional)</span>:
                                                </label>
                                                <input class="add-listing__input" type="text" name="youtube" value="{{ !empty($eventDetail->youtube)?$eventDetail->youtube:old('youtube')}}" id="youtube" placeholder="YouTube URL" />
                                            </div>
                                            <div class="col-md-3 col-sm-6">
                                                <label class="add-listing__label" for="pinterest">
                                                    Instagram <span>(optional)</span>:
                                                </label>
                                                <input class="add-listing__input" type="text" name="instagram" value="{{ !empty($eventDetail->instagram)?$eventDetail->instagram:old('instagram')}}" id="pinterest" placeholder="Pinterest URL" />
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                @if(empty(auth()->user()->is_admin))
                                <div class="add-listing__form-box" id="social-box">

                                    <div class="add-listing__form-content">
                                        <div class="row">

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>GST paid by:( For This Event )</label>
                                                    <br>
                                                    <label class="radio-inline"><br>
                                                        <input type="radio" name="gst" {{ ((!empty($eventDetail->gst) && $eventDetail->gst== 1)? "checked":"") }} value="1" required>   Owner
                                                    </label><br>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="gst" {{ ((!empty($eventDetail->gst) && $eventDetail->gst== 2)? "checked":"") }} value="2" required>   Customer
                                                    </label>
                                                </div><!-- /.form group -->
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                @else
                                <div class="form-group">
                                    <label>Website Url</label>
                                    <input type="text" placeholder="http://www.example.com" value="{{ !empty($eventDetail->site_url)?$eventDetail->site_url:old('websiteurl')}}" name="websiteurl" class="form-control my-colorpicker1 colorpicker-element">
                                    </div>
                                @endif

                                <div class="center-button">
                                    <button class="add-listing__submit" type="submit">
                                        <i class="fa fa-paper-plane" aria-hidden="true"></i>
                                        Preview and Submit Listing
                                    </button>
                                </div>

</div>

{!! Form::close() !!}
</section>
@endsection
@push('head')
<script src="{{ asset('js/eventbackend/backend_event.js')}}"></script>
<script>
                      var messages = {
                          _token: "{{ csrf_token() }}",
                          city_route: "{!! route('statelist') !!}",
                          state_route: "{!! route('stateindividual') !!}",
                          getCity: "{!! route('get_city_list') !!}",
                      };
                      
</script>
@endpush