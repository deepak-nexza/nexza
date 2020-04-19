@extends('layouts.event.eventapp')
@section('content')

          <div class="row">
            <!-- left column -->
            <div class="col-md-6">
              <!-- general form elements -->
              <div class="box box-primary">
                <!-- form start -->
                <form role="form">
                  <div class="box-body">
                    <div class="form-group">
                      <label for="exampleInputEmail1">Event Name</label>
                      <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputPassword1">Event Type</label>
                      <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Password">
                    </div>
                      <div class="form-group">
                      <label for="exampleInputEmail1">Event Date</label>
                      <input type="text" class="form-control" id="datetimepicker" placeholder="Enter email">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputPassword1">Event Time</label>
                      <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Password">
                    </div>
                    
                  </div><!-- /.box-body -->

                </form>
              </div><!-- /.box -->

              <!-- Form Element sizes -->
              

              <!-- Input addon -->

            </div><!--/.col (left) -->
            <!-- right column -->
            <div class="col-md-6">
              <!-- Horizontal Form -->
              <div class="box box-info">
                <!-- form start -->
                <form class="form-horizontal">
                  <div class="box-body">
                   <div class="form-group">
                      <label for="exampleInputPassword1">Venue Location</label>
                      <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Password">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputPassword1">Contact Number</label>
                      <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Password">
                    </div>
                      <div class="form-group">
                      <label for="exampleInputPassword1">Email Address:</label>
                      <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Password">
                    </div>
                       <div class="form-group">
                      <label for="exampleInputEmail1">Event Registration Fee</label>
                      <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
                    </div>
                  </div><!-- /.box-body -->
                  <div class="box-footer">
                  </div><!-- /.box-footer -->
                </form>
              </div><!-- /.box -->
            </div><!--/.col (right) -->
            <div class="col-md-12">
                <div class="form-group">
                      <label for="exampleInputFile">Event Image</label>
                      <input type="file" id="exampleInputFile">
                      <p class="help-block">Example block-level help text here.</p>
                    </div>
                   
                <div class="form-group">
                      <label for="exampleInputPassword1">Description</label>
                      <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Password">
                    </div>
                 <div class="checkbox">
                      <label>
                        <input type="checkbox">T&C
                      </label>
                    </div>
                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </div>
                
            </div>
          </div>   <!-- /.row -->
    @endsection
@section('ActionName')
Create Events
@endsection