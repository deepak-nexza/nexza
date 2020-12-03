@extends('layouts.event.eventapp')
@section('content')
    
<section class="content">

          <!-- SELECT2 EXAMPLE -->
               <div class="box box-info">
            <div class="box-body">
               
              <div class="row">
               <div class="col-xs-12">
                <div class="box-header">
                  <h3 class="box-title">Candidates</h3>
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
                  <table class="table tab   le-hover">
                    <tbody><tr>
                      <th>Event Name</th>
                      <th>Ticket Title</th>
                      <th>Candidate Name</th>
                      <th>Phone</th>
                      <th>Email</th>
                      <th>Address</th>
                      <th>Message</th>
                      <th>Date</th>
                    </tr>
                    @if(!empty($orderDetails) && count($orderDetails) > 0)
                    @foreach($orderDetails as $key=>$val)
                    <tr>
                    <td>{{$val['event_name']}}</td>
                    <td>{{$val['title']}}</td>
                    <td>{{$val['full_name']}}</td>
                    <td>{{$val['phone']}}</td>
                    <td>{{$val['email']}}</td>
                    <td>{{$val['address']}}</td>
                    <td>{{$val['message']}}</td>
                    <td>{{ \Carbon\Carbon::parse($val['created_at'])->format('j F, Y h:i a') }}</td>
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
               @if(count($orderDetails) == 0)
              <div class="box-footer">
                  <a href="{{ route('create_event') }}"> <button type="button" class="btn btn-primary">Create event</button> </a>
                  </div>
               @endif
            </div><!-- /.box-body -->
          </div><!-- /.box -->


        </section>
@endsection
  