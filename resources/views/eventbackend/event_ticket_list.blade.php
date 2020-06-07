@extends('layouts.event.eventapp')
@section('content')
    
<section class="content">
    @if(session()->has('message'))
    <div class="alert alert-success">
        {{ session()->get('message') }}
    </div>
@endif
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
               <div class="box box-info">
                <div class="box-header with-border">
                  <h3 class="box-title">Ticket List</h3>
                </div>
            <div class="box-body">
                <button style="background:red"><a  href="{{ route('event_ticket') }}" style="color:white">Add Event Tickets</a></button>
            <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />  
            
            <div class="row">
                <div class="col-md-6">
                  <input type="hidden" name="event_type" value="{{ (!empty($ticketDetails->event_id)? $ticketDetails->event_id :"") }}"  class="event_type">
                     <div class="form-group">
                    <label>Select Your Event</label>
                    <select class="form-control select2 select2-hidden-accessible" id='event_type'  style="width: 100%;" tabindex="-1" aria-hidden="true">
                        <option>Select Your Event</option>
                        @foreach($eventlist as $item=>$val)
                        <option value="{{$val->event_id}}" {{ (!empty($ticketDetails->event_id)? "selected":"") }}>{{$val->event_name}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>
              </div>
            <div class="row">
                <div class="col-md-12">
               
                    <div id="example1_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="dataTables_length" id="example1_length">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div id="example1_filter" class="dataTables_filter">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <table id="ticket" class="table table-bordered table-striped dataTable" role="grid" aria-describedby="example1_info">
                    <thead>
                      <tr role="row">
                          <th class="sorting_asc" tabindex="0" aria-controls="ticket" rowspan="1" colspan="1" aria-label="Rendering engine: activate to sort column descending" aria-sort="ascending" style="width: 181px;">EventID</th>
                          <th  tabindex="1" aria-controls="ticket" rowspan="1" colspan="1" aria-label="Browser: activate to sort column ascending" style="width: 223px;">Event Name</th>
                          <th class="sorting" tabindex="2" aria-controls="ticket" rowspan="1" colspan="1" aria-label="Platform(s): activate to sort column ascending" style="width: 197px;">Ticket Title</th>
                          <th class="sorting" tabindex="3" aria-controls="ticket" rowspan="1" colspan="1" aria-label="Engine version: activate to sort column ascending" style="width: 155px;">Start Date</th>
                          <th class="sorting" tabindex="4" aria-controls="ticket" rowspan="1" colspan="1" aria-label="CSS grade: activate to sort column ascending" style="width: 112px;">End Date</th>
                          <th class="sorting" tabindex="5" aria-controls="ticket" rowspan="1" colspan="1" aria-label="CSS grade: activate to sort column ascending" style="width: 112px;">Booking Price</th>
                          <th  tabindex="6" aria-controls="ticket" rowspan="1" colspan="1" aria-label="CSS grade: activate to sort column ascending" style="width: 112px;">Ticket Status</th>
                          <th  tabindex="7" aria-controls="ticket" rowspan="1" colspan="1"  style="width: 112px;">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="recorFill"></tbody>
                    </table>
                 </div>
                </div>
               </div>
                 </div>
              </div>
            <!--
              <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </div>
                {{ Form::close() }}
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