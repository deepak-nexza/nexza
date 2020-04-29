@extends('layouts.event.eventapp')
@section('content')
    
<section class="content">

          <!-- SELECT2 EXAMPLE -->
               <div class="box box-info">
                <div class="box-header with-border">
                  <h3 class="box-title">Create Event</h3>
                </div>
            <div class="box-body">
               
                <form>
              <div class="row">
                <div class="col-md-6">
                 <div class="form-group">
                    <label>Ticket Heading</label>
                    <input type="text" name="event_name" placeholder="Event Name" class="form-control my-colorpicker1 colorpicker-element">
                  </div>
                    
                     <div class="form-group">
                    <label>Ticket Type</label>
                    <select class="form-control select2 select2-hidden-accessible" style="width: 100%;" tabindex="-1" aria-hidden="true">
                      <option selected="selected">Event Type</option>
                      <option>Paid</option>
                      <option>Free</option>
                    </select>
                  </div>
                      <div class="form-group">
                    <label>Country</label>
                    <select class="form-control select2 select2-hidden-accessible" style="width: 100%;" tabindex="-1" aria-hidden="true">
                      <option selected="selected">India</option>
                     
                    </select>
                  </div>
                    <div class="form-group">
                    <label>Logo Image on ticket Receipt</label>
                    <input type="file" name="event_name" placeholder="Event Name" class="form-control">
                  </div>
                    
                </div><!-- /.col -->
                <div class="col-md-6">
                    <div class="form-group">
                    <label>Booking Amount per individual:</label>
                    <input type="text" name="event_name" placeholder="INR" class="form-control my-colorpicker1 colorpicker-element">
                  </div>
                    <div class="form-group">
                    <label>Event Start/End Date:</label>
                    <input type="text" placeholder="Event Start Or End Date" class="form-control my-colorpicker1" id="reservationtime">
                  </div>
                     <div class="form-group">
                    <label>
                      <div class="icheckbox_minimal-blue checked" aria-checked="false" aria-disabled="false" style="position: relative;"><input type="checkbox" class="minimal" checked="" style="position: absolute; opacity: 0;"><ins class="iCheck-helper" style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255); border: 0px; opacity: 0;"></ins></div>
                    </label>
                  </div>
                </div><!-- /.col -->
                <div class="col-md-12">
                    <label>Event Description</label>
                    <div class="box-body pad">
                        <textarea  name="Ticket Description" rows="10" cols="80" placeholder="Ticket Description........"></textarea>
                </div>
                </div>
              </div><!-- /.row -->
              <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </div>
                </form>
            </div><!-- /.box-body -->
          </div><!-- /.box -->


        </section>
@endsection
  