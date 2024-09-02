@extends('layouts/default')

{{-- Page title --}}
@section('title')
History Report
@parent
@stop

{{-- Page content --}}
@section('content')
<link rel="stylesheet" type="text/css" href="https://harvesthq.github.io/chosen/chosen.css">
<div class="form-group">
    <form name="" method="post" id="search_history">
    <div class="row">
        <div class="col-md-2">
            <label for="action_date_from">Action Date From</label>
            <input type="text" name="action_date_from" class="action_date_from form-control datepicker" id="action_date_from" value="{{ $current_date }}">
        </div>
        <div class="col-md-2">
            <label for="action_date_to">Action Date To</label>
            <input type="text" name="action_date_to" class="action_date_to form-control datepicker" id="action_date_to" value="{{ $current_date }}">
        </div>
        <div class="col-md-2">
            <label for="vendor_id">Vendor</label>
            <select name="vendor_id" class="vendor_id form-control" id="vendor_id">
                <option value="">--Select--</option>
                @foreach($vendors as $vendor)
                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="shipment_number">Shipment Number</label>
            <select name="shipment_number" class="shipment_number form-control chosen-select" id="shipment_number">
                <option value="">--Select--</option>
                @foreach($shipment_numbers as $shipment_number)
                <option value="{{ $shipment_number->barcode_number }}">{{ $shipment_number->barcode_number }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1" style="margin-top: 25px;">
            <input type="submit" name="show_history" class="show_history btn btn-success btn-sm" id="show_history" value="Show">
        </div>
        <div class="col-md-2">
            <br>
            <a id="exportReport" href="javascript:void" class="btn btn-sm btn-info export-button pull-right" data-name="Report" data-table="#report">Export to Excel</a>
        </div>
    </div>
    </form>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-body" id="report_export">
                <table class="table table-striped snipe-table">
                    <thead>
                        <tr>
                            <th>SN</th>
                            <th class="">Shipment Number</th>
                            <th>Product Name</th>
                            <th>Vendor</th>
                            <th class="">User</th>
                            <th class="">Action Type</th>
                            <th class="">Action Date</th>
                            <th class="">Notes</th>
                            <th class="">Status</th>
                        </tr>
                    </thead>
                    <tbody id="history-data">
                        @php $i = 1; @endphp
                        @foreach($action_logs as $action_log)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>
                               {{ $action_log->barcode_number }} 
                            </td>
                            <td>
                               {{ $action_log->product_name }} 
                            </td>
                            <td>
                               {{ $action_log->vendor_name }} 
                            </td>
                            <td>
                                {{ $action_log->first_name }}&nbsp;{{ $action_log->last_name }}
                            </td>
                            <td>
                                {{ $action_log->action_type }} 
                            </td>
                            <td>
                                {{ date('Y-m-d H:i:s',strtotime($action_log->action_date)) }}
                            </td>
                            <td>
                                {{ $action_log->note }} 
                            </td>
                            <td>
                                {{ $action_log->label_name }} 
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop


@section('moar_scripts')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://harvesthq.github.io/chosen/chosen.jquery.js" type="module"></script>
<script type="module" src="https://harvesthq.github.io/chosen/docsupport/init.js"></script>
<script type="text/javascript">
    $(function(){
        $('.chosen-select').chosen(); 
    });
    $(document).ready(function(){
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd'
        });
        //form submission of filter
        $("#search_history").submit(function( event ) {
            console.log("i am");
            var data = $('#search_history').serializeArray();
            $('.show_history').val('Searching...');;
            $.ajax({
                type:'POST',
                url:"/reports/history",
                data:data,
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                success:function(response){
                    if(response.status == 'error'){
                        $('.show_history').val('Show');
                    }
                    else{
                        $('.show_history').val('Show');
                        $("#history-data").html(response.log_data);
                    }
                },
                error: function(error) {
                    console.log(error);
                    console.log('error');
                    return false;
                }
            });
            return false;
        });
        //export data to excel
        $("#exportReport").click(function(e){
            var table = $('#report_export').html();
            var myBlob =  new Blob( [table] , {type:'data:application/vnd.ms-excel'});
            var url = window.URL.createObjectURL(myBlob);
            var a = document.createElement("a");
            document.body.appendChild(a);
            a.href = url;
            a.download = "history_report.xls";
            a.click();
        });
    });
</script>
@stop
