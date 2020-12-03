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
                  <table class="table table-hover">
                    <tbody><tr>
                      <th>Order ID</th>
                      <th>Transaction ID</th>
                      <th>Event Name</th>
                      <th>Payment Method</th>
                      <th>Order Amount</th>
                      <th>Order Date</th>
                      <th>Payment Status</th>
                      <th>Action</th>
                    </tr>
                    @if(!empty($bookings) && count($bookings) > 0)
                    @foreach($bookings as $key=>$val)
                    <tr>
                      <td>{{$val['order_id']}}</td>
                      <td>{{$val['transaction_id']}}</td>
                      <td>{{$val['event_name']}}</td>
                      <td>{{$val['methods']}}</td>
                      <td>INR  {{$val['order_amt']}}</td>
                      <td>{{ \Carbon\Carbon::parse($val['created_at'])->format('j F, Y h:i a') }}</td>
                      @if(!empty($val['paystatus']) &&  $val['paystatus'] == 1)
                      <td><span class="label label-success">Successful</span></td>
                      @else
                      <td><span class="label label-danger">Pending</span></td>
                      @endif
                      <td><a href="{{route('get_order_details',['order_id'=>$val['order_id']])}}"> Order Details </a></td>
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
               @if(count($bookings) == 0)
              <div class="box-footer">
                  <a href="{{ route('create_event') }}"> <button type="button" class="btn btn-primary">Create event</button> </a>
                  </div>
               @endif
            </div><!-- /.box-body -->
          </div><!-- /.box -->


        </section>
@endsection
  