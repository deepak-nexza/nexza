@extends('layouts.event.eventapp')
@section('content')
<br>
<br>
<br>
<br>
<section class="container">
<div class="row">
        <div class="col-md-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Your Dashboard!! is here !!</h3>

            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                <div class="col-md-12">
                  <p class="text-center">
                  </p>

                  <div class="chart">
                      <div style="padding:100px"><a href="{{route('create_event')}}"><button type="button" class="btn btn-block btn-danger btn-lg">Create Events</button></a></div>
                  </div>
                  <!-- /.chart-responsive -->
                </div>
                <!-- /.col -->
                </div>
                <!-- /.col -->
              </div>
              <!-- /.row -->
            </div>
            <!-- ./box-body -->
            <div class="box-footer">
              <div class="row">
                <div class="col-sm-6 col-xs-12">
                  <div class="description-block border-right">
                        <h5 class="description-header">{{$close}}</h5>
                    <span class="description-text">Open Events</span>
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col -->
                <div class="col-sm-6 col-xs-12">
                  <div class="description-block border-right">
                        <h5 class="description-header">{{$open}}</h5>
                    <span class="description-text">Close Events</span>
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col -->
                <!-- /.col -->
                  </div>
              <!-- /.row -->
            </div>
            <!-- /.box-footer -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
</section>
<br>
<br>
<br>
<br>
<br>
<br>
@endsection


  