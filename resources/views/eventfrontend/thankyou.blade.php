@extends('layouts.event.layout_event')
@section('contentData')
<br>
<div class="jumbotron text-center" style="background: none">
  <h1 class="display-3">Thank You!</h1>
  <hr>
  <p>
    Having trouble? <a href="">Contact us</a>
  </p>
  <p class="">
      <a href="{{ route('/') }}" class="add-list-btn ">Continue To HomePage</a>
  </p>
</div>
@endsection