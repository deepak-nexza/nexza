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
                <!-- SELECT2 EXAMPLE -->
             

                {!!
                Form::open(
                array(
                'name' => 'NexzaForms',
                'id' => 'NexzaForms',
                'url'=>route('save_desc'),
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
                <div class="row">
                    <div class="col-sm-6">

                        <div class="box-header with-border">
                            <h3 class="box-title" style="font-weight: 700">Complete Description Of Your Event</h3>
                        </div>

                    </div>
                </div>
                <br>
            
                <div class="add-listing__form-box" id="gallery-box">

						<h2 class="add-listing__form-title">
							Gallery:
						</h2>

						<div class="add-listing__form-content">
								<input class="" type="file" name="banner_image" id="imgInp" onchange="showLocalImage(this.value)" >
						</div>

					</div>
                @php $imag = !empty($eventDetail->banner_image)?$eventDetail->banner_image:'' @endphp
                <div class="row imgclass {{ !empty($eventDetail->banner_image)?'':'hidden' }} ">
                    <div class="col-md-12">
                        <div class="form-group">
                            <img class="img-responsive" id="blah" src="{{ asset(config('common.uploadDir').'/'.$imag)}}" style="width:1100px;height:300px;border:10px solid black">
                        </div><!-- /.form group --> 
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="box-header">
                            <h3 class="box-title">Event Description</small></h3>
                        </div>
                        <div class="box-body pad">
                            <textarea id="editor1" name="description"  rows="10" cols="80" style="visibility: hidden; display: none;">  
                    {{ !empty($eventDetail->description)?html_entity_decode($eventDetail->description):''}}
                            </textarea>
                        </div>

                    </div><!-- /.col -->


                </div><!-- /.row -->
                <div class="box-footer">

                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{route('create_event',['event_id'=>$eventDetail->event_id])}}"><button type="button" class="btn btn-primary" style="float:left;cursor:pointer;">Back</button></a>

                        </div>
                        <div class="col-md-6">
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
function readURL(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    
    reader.onload = function(e) {
        $('.imgclass').removeClass('hidden');
      $('#blah').attr('src', e.target.result);
    }
    
    reader.readAsDataURL(input.files[0]); // convert to base64 string
  }
}

$("#imgInp").change(function() {
  readURL(this);
});
</script>
@endpush