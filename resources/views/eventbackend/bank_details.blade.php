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
                  <h3 class="box-title">Update Bank Details</h3>
                </div>
               <div class="box-body">
               
             <div class="row">
                <div class="col-md-6">
                            {!!
        Form::open(
        array(
        'name' => 'accountForm',
        'id' => 'accountForm',
        'url'=>route('save_account_details'),
        'method'=>'POST',
        'autocomplete' => 'off',
        )
        )
        !!}
                 <div class="form-group">
                    <label>Account Number</label>
                    <input type="text" name="account_number" placeholder="Account Number" class="form-control" value="">
                  </div>
                 <div class="form-group">
                    <label>Confirm Account Number:</label>
                    <input type="text" name="confirm_account_number" placeholder="Confirm Account Number" class="form-control" value="">
                  </div>
                  <div class="form-group">
                    <label>Account Holder Name</label>
                    <input type="text" name="account_name" placeholder="Name..." class="form-control" value="">
                  </div>
                  <div class="form-group">
                    <label>IFSC Code</label>
                    <input type="text" name="ifsc_code" placeholder="IFSC Code" class="form-control" value="">
                  </div>
                  <div class="form-group">
                    <label>Gst Number(optional)</label>
                   <input type="text" name="gst_number" placeholder="Gst Number" class="form-control" value="">
                  </div>
                
                  
                </div><!-- /.col -->
                <div class="col-md-6">
             <ul class="list-group">
  <li class="list-group-item d-flex justify-content-between align-items-center">
    Account Number
    <span class="badge badge-primary badge-pill">{{  !empty($user->account_number)?$user->account_number:'1230xxxxxxxx2345'}}</span>
  </li>
  <li class="list-group-item d-flex justify-content-between align-items-center">
    Account Holder Name
    <span class="badge badge-primary badge-pill">{{  !empty($user->account_name)?$user->account_name:'XYZ ABC'}}</span>
  </li>
  <li class="list-group-item d-flex justify-content-between align-items-center">
    Ifsc Code
    <span class="badge badge-primary badge-pill">{{  !empty($user->ifsc_code)?$user->ifsc_code:'IFC0001234'}}</span>
  </li>
</ul>
                </div><!-- /.col -->
            </div><!-- /.box-body -->
            <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </div>
                {!! Form::close() !!}
            </div><!-- /.box-body -->
          </div><!-- /.box -->


        </section>
@endsection
  @push('head')
<script src="{{ asset('js/eventbackend/event_ticket_list.js')}}"></script>
<script>
      var messages = {
          _token: "{{ csrf_token() }}",
          listroute: "{!! route('get_event_ticket') !!}",
          gatewayAmt: "{{ config('common.nexzoa_Gateway_fee') }}",
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