@extends('layouts.event.eventapp')
@section('content')
    
<section class="content">

          <!-- SELECT2 EXAMPLE -->
               <div class="box box-info">
            <div class="box-body">
               
              <div class="row">
               <div class="col-xs-12">
                <div class="box-header">
                  <h3 class="box-title">Past Event</h3>
<!--                  <div class="box-tools">
                    <div class="input-group" style="width: 150px;">
                      <input type="text" name="table_search" class="form-control input-sm pull-right" placeholder="Search">
                      <div class="input-group-btn">
                        <button class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>
                      </div>
                    </div>
                  </div>-->
                </div><!-- /.box-header -->
                <div class="box-body table-responsive no-padding">
                  <table class="table table-hover">
                    <tbody><tr>
                      <th>Event ID</th>
                      <th>Event Name</th>
                      <th>Event Type</th>
                      <th>Start Date</th>
                      <th>End Date</th>
                      <th>Privacy</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                    @if(!empty($eventlist) && count($eventlist) > 0)
                    @foreach($eventlist as $key=>$val)
                    <tr>
                      <td>{{$val['event_uid']}}</td>
                      <td>{{$val['event_name']}}</td>
                      <td>{{$val['event_name']}}</td>
                      <td>{{$val['start_date']}}</td>
                      <td>{{$val['end_date']}}</td>
                      @if(!empty($val['event_privacy']) &&  $val['event_privacy'] == 1)
                      <td><span class="label label-danger"></span>Public</td>
                      @else
                      <td><span class="label label-success"></span>Private</td>
                      @endif
                      @if(!empty($val['status']) &&  $val['event_privacy'] == 1)
                      <td><span class="label label-success">Active</span></td>
                      @else
                      <td><span class="label label-danger">InActive</span></td>
                      @endif
                      <td><a href="{{route('update_event',['event_uid'=>$val['event_uid']])}}"> Edit</a>/<a href="{{ route('create_event') }}">Delete</a></td>
                      
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="8">No Record Found</td> 
                    </tr>
                    @endif
                  </tbody></table>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
                </div>
              </div><!-- /.row -->
               @if(count($eventlist) == 0)
              <div class="box-footer">
                  <a href="{{ route('create_event') }}"> <button type="button" class="btn btn-primary">Create event</button> </a>
                  </div>
               @endif
            </div><!-- /.box-body -->
          </div><!-- /.box -->


        </section>
@endsection
  