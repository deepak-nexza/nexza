

<!DOCTYPE html>
<html>
<head>
<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
</head>
<body>

    <div class="widget-header widget-header-large">
        <a class="navbar-brand" href="/" style="color:#fb646f;font-weight:bold">
            <i class="fa fa-map-marker" aria-hidden="true"></i> NEXZOA
        </a>
        <br>
        <br>
        <p>{{ !empty($order['email'])?$order['email']:null }}</p>
        <p>{{ !empty($order['phone'])?$order['phone']:null }}</p>
        <span >{{ !empty($order['created_at'])?\Carbon\Carbon::parse($order['created_at'])->format('j F, Y '):null }}</span><br>

        <span class="invoice-info-label">Invoice:</span>

        <span class="red">{{ !empty($order['order_id'])?$order['order_id']:null }}</span>


        <div class="widget-toolbar hidden-480">
            <a href="#" class="print">
                <i class="ace-icon fa fa-print"></i>
            </a>
        </div>
    </div>
<table >
    <thead>
        <tr>
            <th class="center">#</th>
            <th>Event Name</th>
            <th class="hidden-xs">Name</th>
            <th class="hidden-480">Email</th>
            <th class="hidden-480">Phone</th>
            <th class="hidden-480">Address</th>
        </tr>
    </thead>
    <tbody>
        @if(sizeof($candidates) > 0)
        @foreach($candidates as $key => $val)
        @php $ticketHelp = \Helpers::getTicketDetails(['ticket_id'=>$val->ticket_id,'user_id'=>$val->user_id]) @endphp
        <tr>
            <td class="center">{{++$key}}</td>

            <td>
                {{$ticketHelp['title']}}
            </td>
            <td>
                {{$val['full_name']}}
            </td>
            <td class="hidden-xs">
                {{$val['email']}}
            </td>
            <td class="hidden-480">{{$val['phone']}} </td>
            <td>{{$val['address']}}</td>
        </tr>
        @endforeach
        @endif


    </tbody>
</table>
    <div class="row">
        <div class="col-sm-12 pull-right">
            <h4 class="pull-right">
                Total amount :
                <span class="red">INR {{$order['order_amt']}}</span>
            </h4>
        </div>
    </div>
    <div class="space-12"></div>
    <div class="well">
        Thank You.
    </div>

</body>
</html>

		

						
                    