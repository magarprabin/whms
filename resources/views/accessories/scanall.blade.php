@extends('layouts/default')

{{-- Page title --}}
@section('title')
     {{ trans('admin/accessories/general.scan') }}
@parent
@stop
@section('header_right')
<a href="{{ URL::previous() }}" class="btn btn-primary pull-right">
  {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')
<style type="text/css">
    blink {
  animation: 1s linear infinite condemned_blink_effect;
  color: #ff0000;
}

#invalid-tag{
    color: #ff0000;
}

@keyframes condemned_blink_effect {
  0% {
    visibility: hidden;
  }
  50% {
    visibility: hidden;
  }
  100% {
    visibility: visible;
  }
}
</style>

<div class="row">
    <div class="col-md-9">
        <form class="form-horizontal" method="post" action="" autocomplete="off" id="scan-all-form">
        <!-- CSRF Token -->
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <div class="row">
    <div class="col-md-12">
        <label for="inventory_tag" class="col-md-2">Inventory Tag</label>
        <div class="col-md-3">
            <input type="text" name="inventory_tag_search" class="form-control inventory_tag_search" id="inventory_tag_search">
        </div>
        <div class="col-md-2">
            <button type="submit" class="search btn btn-success">Show</button>
        </div>
        <div class="col-md-1" id="loading-gif" style="display: none;">
            <img src="{{URL::asset('/uploads/assets/loading-waiting.gif')}}" style="height:50px;">
        </div>
        <div class="col-md-3">
            <span id="invalid-tag"></span>
        </div>
    </div>
</div>
<div class="box-body">
        <div class="box box-default">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tag Number</th>
                            <th>Name</th>
                            <th>User</th>
                            <th>Notes</th>
                            <th>#</th>
                        </tr>
                    </thead>
                    <tbody id="inventory_tag_data" class="barcode_scan">
                    </tbody>
                </table>
            </div>
            <div class="box-footer">
                <a class="btn btn-link" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
                <input type="hidden" name="assigned_to" class="form-control" value="{{ $user_id }}">
                <input type="hidden" name="assigned_to_name" id="assigned_to_name" class="form-control" value="{{ \App\Models\User::find($user_id)->present()->fullName }}" readonly>
                <div class="pull-right">
                    <button type="submit" class="btn btn-primary btn-scan-all" disabled><i class="fa fa-check icon-white" aria-hidden="true"></i> Submit</button>
                    <div class="col-md-1 pull-right" id="loading-gif-submit" style="display: none;">
                        <img src="{{URL::asset('/uploads/assets/loading-waiting.gif')}}" style="height:50px;">
                    </div>
                </div>
            </div>
        </div> <!-- .box.box-default -->
    </div>
        </form>
    </div> <!-- .col-md-9-->
</div> <!-- .row -->
@stop
@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
    $(document).ready(function() {
        var x = 0;
        var add_button = $(".add_barcode_inventory");
        var wrapper = $(".barcode_scan");
        const shipment_tag_search = [];
        //get inventory information by tag number
        $(".search").click(function( event ) {
            $("#loading-gif").show();
            var inventory_tag = $("#inventory_tag_search").val();
            if(shipment_tag_search.includes(inventory_tag)){
                $("#inventory_tag_search").val('');
                if(x == '0'){
                    $(".btn-scan-all").attr('disabled',true);
                }
                $("#invalid-tag").html('Shipment Tag already scanned');
                $("#loading-gif").hide();
                setTimeout(function() { 
                    $("#invalid-tag").html('');
                }, 3000);
                return false;
            }
            shipment_tag_search.push(inventory_tag);
            var csrf_token = "{{ csrf_token() }}";
            $.ajax({
                type:'POST',
                url: "{{url('/') }}/accessories/inventorydata",
                data:{inventory_tag:inventory_tag},
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                _token: "{{ csrf_token() }}",
                success:function(response){
                    if(response.status == 'error'){
                        $("#inventory_tag_search").val('');
                        if(x == '0'){
                            $(".btn-scan-all").attr('disabled',true);
                        }
                        $("#invalid-tag").html(response.message);
                        $("#loading-gif").hide();
                        setTimeout(function() { 
                            $("#invalid-tag").html('');
                        }, 3000);
                    }
                    else{
                        $("#inventory_tag_search").val('');
                        $(".btn-scan-all").attr('disabled',false);
                        $("#loading-gif").hide();
                        x++;
                        var inventory_tag = response.inventory_tag;
                        var inventory_number = response.inventory_number;
                        var quantity = response.quantity;
                        var inventory_name = response.accessory_name;
                        var user_name = $('#assigned_to_name').val();
                        var newrow = '<tr>';
                        newrow += '<td>';
                        newrow += '<input type="hidden" name="accessory_id['+x+']" id="inventory_number_'+x+'" class="inventory_number form-control" value="'+inventory_number+'" inventory_id = "'+x+'">';
                        newrow += '<input type="hidden" name="qty['+x+']" value="'+quantity+'" id="qty'+x+'">';
                        newrow += '<input type="text" name="inventory_tag['+x+']" class="form-control inventory_tag'+x+'" shipment_sn="'+x+'" value="'+inventory_tag+'">';
                        newrow += '</td>';
                        newrow += '<td>';
                        newrow += '<input type="text" name="inventory_name['+x+']" class="form-control inventory_name'+x+'" shipment_sn="'+x+'" value="'+inventory_name+'">';
                        newrow += '</td>';
                        newrow += '<td>';
                        newrow += user_name;
                        newrow += '</td>';
                        newrow += '<td>';
                        newrow += '<textarea class="col-md-6 form-control" id="note" name="note['+x+']"></textarea>';
                        newrow += '</td>';
                        newrow += '</tr>';
                        $("#inventory_tag_data").append(newrow);
                    }
                }
            });
            return false;
        });
        //add new row for adding inventory
        $(add_button).click(function(e){ //on add input button click

            e.preventDefault();
            x++;
            var inventory_html = '';
            inventory_html += '<tr>';
            inventory_html += '<td>';
            inventory_html += '<input type="hidden" name="accessory_id[]" id="inventory_number_'+x+'" class="inventory_number form-control" value="" inventory_id = "'+x+'">';
            inventory_html += '<input type="hidden" name="qty[]" value="" id="qty'+x+'">';
            inventory_html += '<input type="text" name="inventory_tag['+x+']" id="inventory_tag_'+x+'" class="inventory_tag form-control" value="" tag_id = "'+x+'">';
            inventory_html += '</td>';
            inventory_html += '<td>';
            inventory_html += '<input type="text" name="inventory_name['+x+']" id="inventory_name_'+x+'" class="inventory_name form-control" value="" readonly>';
            inventory_html += '</td>';
            inventory_html += '<td>';
            inventory_html += '<input type="text" name="assigned_to_name" class="form-control" value="{{ \App\Models\User::find($user_id)->present()->fullName }}" readonly>';
            inventory_html += '</td>';
            inventory_html += '<td>';
            inventory_html += '<textarea class="col-md-6 form-control" id="note" name="note['+x+']"></textarea>';
            inventory_html += '</td>';
            inventory_html += '<td>';
            inventory_html += '<a href="#" class="remove_field btn btn-default btn-sm" tag_id="'+x+'"><i class="fa fa-minus"></i></a>';
            inventory_html += '</td>';
            inventory_html += '</tr>';
            $(wrapper).append(inventory_html);
            $(".inventory_tag").keyup(function( event ) {
                var inventory_tag = $(this).val();
                var csrf_token = "{{ csrf_token() }}";
                var id = $(this).attr('tag_id');
                $.ajax({
                    type:'POST',
                    url: "{{url('/') }}/accessories/inventorydata",
                    data:{inventory_tag:inventory_tag},
                    headers: {
                        "X-Requested-With": 'XMLHttpRequest',
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    _token: "{{ csrf_token() }}",
                    success:function(response){
                        if(response.status == 'error'){
                            alert('Zero available quantity for '+inventory_tag+'');
                            $('#inventory_tag_'+id).val('');
                        }
                        else{
                            $('#inventory_name_'+id).val(response.accessory_name);
                            $('#inventory_tag_'+id).val(response.inventory_tag);
                            $('#qty'+id).val(response.quantity);
                        }
                    }
                });
            });
        });
        
        //remove added row
        $(wrapper).on("click",".remove_field", function(e){ //user clicks on remove text
            e.preventDefault();
            console.log(x);
            var tag_id = $(this).attr('tag_id');
            document.getElementsByTagName("tr")[x].remove();
            x--;
        });

        //Scan All form submission
        $("#scan-all-form").submit(function( event ) {
            $(".btn-scan-all").attr('disabled',true);
            $("#loading-gif-submit").show();
        });
    });
</script>
@stop