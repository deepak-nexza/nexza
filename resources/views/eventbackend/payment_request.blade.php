@extends('layouts.event.eventapp')
@section('content')
    
<section class="content">
@if(session()->has('message'))
    <div class="alert alert-success">
        {{ session()->get('message') }}
    </div>
@endif
          <!-- SELECT2 EXAMPLE -->
               <div class="box box-info">
                <div class="box-header with-border">
                  <h3 class="box-title">Request Payment</h3>
                </div>
               <div class="box-body">
               
             <div class="row">
                <div class="col-md-6">
                            {!!
        Form::open(
        array(
        'name' => 'accountForm',
        'id' => 'accountForm',
        'url'=>route('save_payment_request'),
        'method'=>'POST',
        'autocomplete' => 'off',
        )
        )
        !!}
                 <div class="form-group">
                    <label>Amount</label>
                    <input type="text" name="req_amount" id="amount_id" placeholder="Amount" class="form-control" value="">
                  </div>
                 <div class="form-group">
                    <label>Schedule On Date</label>
                    <input type="text" name="release_date" id="datepicker" placeholder="Schedule to release" class="form-control relaseDate" value="">
                  </div>
                 <button type="submit" class="btn btn-primary">Submit</button>
                </div><!-- /.col -->
                <div class="col-md-6">
             <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-rupee"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Wallet Amount</span>
              <span class="info-box-number">1,410</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
        </div>
                  <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-rupee"></i></span>

            <div class="info-box-content">
              <span class="info-box-text">Nexzoa Amount</span>
              <span class="info-box-number">1,410</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
                      
                </div><!-- /.col -->
                
                 <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-rupee"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Amount to be paid<br> by nexzoa</span>
                <span class="info-box-number" id="paymentpending">1,410</span>
            </div>
            <!-- /.info-box-content -->
          </div>
          <!-- /.info-box -->
        </div>
                      
                </div><!-- /.col -->
                
                
                </div><!-- /.col -->
            </div><!-- /.box-body -->
                {!! Form::close() !!}
            </div><!-- /.box-body -->
                <div class="box-footer">
                     <table class="table table-bordered table-sm">
    <thead>
      <tr>
          
        <th>#</th>
        <th>Requested Amount</th>
        <th>Released By</th>
        <th>Created On</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
     @if(!empty($payDetails) && count($payDetails) > 0)
                    @foreach($payDetails as $key=>$val)
                    <tr role="row" class="odd">
                    <td>{{++$key}}</td>
                    <td>{{$val['req_amt']}}</td>
                    <td>{{ \Carbon\Carbon::parse($val['req_date'])->format('j F, Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($val['created_at'])->format('j F, Y h:i a') }}</td>
                    @if(!empty($val['payment_status']) &&  $val['payment_status'] == 1)
                      <td><span class="label label-success">Successful</span></td>
                      @else
                      <td><span class="label label-danger">Pending</span></td>
                      @endif
                    
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="8">No Record Found</td> 
                    </tr>
                    @endif
      
    </tbody>
  </table>
                  </div>
            
          </div><!-- /.box -->


        </section>
@endsection
  @push('head')
   <script src="{{ asset('js/eventbackend/currency.min.js')}}"></script>
<script src="{{ asset('event/js/payment.js')}}"></script>
<script>
      var messages = {
          _token: "{{ csrf_token() }}",
          listroute: "{!! route('get_event_ticket') !!}",
          gatewayAmt: "{{ config('common.nexzoa_Gateway_fee') }}",
          amt: "1234",
      };
        $('#ticket').DataTable({
          "paging": true,
          "lengthChange": true,
          "searching": true,
          "ordering": true,
          "info": true,
          "autoWidth": false
        });
      </script>
@endpush