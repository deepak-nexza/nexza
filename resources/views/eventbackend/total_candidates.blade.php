@extends('layouts.event.eventapp')
@section('content')
    
<section class="content">
<div class="box">
                <div class="box-header">
                  <h3 class="box-title">Candidates Registered</h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <div id="example1_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                      <div class="row">
                <div class="col-md-6">
                     <div class="form-group">
                         <label >Select Your Event</label>
                    <select style="margin-top:5px" class="form-control select2 select2-hidden-accessible" id='event_type'  style="width: 100%;" tabindex="-1" aria-hidden="true">
                        <option >Select Your Event</option>
                        @foreach($eventList as $item=>$val)
                        <option value="{{$val->event_id}}" >{{$val->event_name}}</option>
                        @endforeach
                    </select>
                  </div>
                </div>
              </div>
                      <br>
                     <div class="row">
                         <div class="col-sm-12">
                             
                        <table id="example1" class="table table-bordered table-striped dataTable" role="grid" aria-describedby="example1_info">
                    <thead>
                      <tr role="row">
                          <th class="sorting_desc" tabindex="0" aria-controls="example1"      aria-label="Rendering engine: activate to sort column ascending" aria-sort="descending" style="width: 172px;">Event Name</th>
                          <th class="sorting" tabindex="0" aria-controls="example1"      aria-label="Browser: activate to sort column ascending" style="width: 218px;">Ticket Title</th>
                          <th class="sorting" tabindex="0" aria-controls="example1"      aria-label="Platform(s): activate to sort column ascending" style="width: 201px;">Candidate Name</th>
                          <th class="sorting" tabindex="0" aria-controls="example1"  aria-label="Engine version: activate to sort column ascending" style="width: 149px;">Phone</th>
                          <th class="sorting" tabindex="0" aria-controls="example1"      aria-label="Engine version: activate to sort column ascending" style="width: 149px;">Email  </th>
                          <th class="sorting" tabindex="0" aria-controls="example1"      aria-label="Engine version: activate to sort column ascending" style="width: 75px;">Address</th>
                          <th class="sorting" tabindex="0" aria-controls="example1"      aria-label="Engine version: activate to sort column ascending" style="width: 75px;">Message</th>
                          <th class="sorting" tabindex="0" aria-controls="example1"      aria-label="Engine version: activate to sort column ascending" style="width: 75px;">Date</th>
                    </thead>
                    <tbody class="recorFill">
                      @if(!empty($orderDetails) && count($orderDetails) > 0)
                    @foreach($orderDetails as $key=>$val)
                    <tr role="row" class="odd">
                    <td>{{$val['event_name']}}</td>
                    <td>{{$val['title']}}</td>
                    <td>{{$val['full_name']}}</td>
                    <td>{{$val['phone']}}</td>
                    <td>{{$val['email']}}</td>
                    <td>{{substr($val['address'],0,100)}}</td>
                    <td>{{substr($val['message'],0,100)}}</td>
                    <td>{{ \Carbon\Carbon::parse($val['created_at'])->format('j F, Y h:i a') }}</td>
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
                     </div>
                </div><!-- /.box-body -->
              </div>
          <!-- SELECT2 EXAMPLE -->
             
          </div><!-- /.box -->


        </section>
@endsection
    @push('head')
<script src="{{ asset('js/eventbackend/candidates_total.js')}}"></script>
<script>
      var messages = {
          _token: "{{ csrf_token() }}",
          candidateList: "{!! route('candidates_event') !!}",
      };
       
      </script>
      <script>
      $(function () {
        $("#example1").DataTable();
        $('#example2').DataTable({
          "paging": true,
          "lengthChange": false,
          "searching": true,
          "ordering": true,
          "info": true,
          "autoWidth": true
        });
      });
    </script>
@endpush