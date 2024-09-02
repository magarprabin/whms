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
<style>
    .input-group {
        padding-left: 0px !important;
    }
    blink {
      animation: 1s linear infinite condemned_blink_effect;
      color: #ff0000;
    }

    #invalid-tag{
        color: #ff0000;
        font-weight: bold;
    }

    #valid-tag{
        color: #008000;
        font-weight: bold;
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
    .req {
        color: red;
    }
</style>

<div class="row">
    <div class="col-md-9">
    <form class="form-horizontal" method="post" action="" autocomplete="off" id="scan-specific-form">
    <!-- CSRF Token -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="box box-default">

        <div class="box-body">
            <div class="form-group">
                <label class="col-sm-3 control-label">Inventory Tag</label>
                <div class="col-md-3">
                    <input type="text" name="inventory_tag" id="inventory_tag" class="inventory_tag form-control" value="">
                    <input type="hidden" name="inventory_number" id="inventory_number" class="inventory_number form-control" value="">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="search btn btn-success btn-sm">Check</button>
                </div>
                <div class="col-md-1" id="loading-gif" style="display: none;">
                    <img src="{{URL::asset('/uploads/assets/loading-waiting.gif')}}" style="height:50px;">
                </div>
                <div class="col-md-3">
                    <span id="invalid-tag"></span>
                    <span id="valid-tag"></span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">{{ trans('admin/accessories/general.accessory_name') }}</label>
                <div class="col-md-6">
                    <p class="form-control-static"><input id="accessory-name" class="form-control" readonly/></p>
                    <input type="hidden" id="category-name" class="form-control" readonly/>
                </div>
            </div>

         <!-- order number -->
         <div class="form-group {{ $errors->has('order_number') ? 'error' : '' }}">
             <label for="order_number" class="col-md-3 control-label">
                 {{ trans('general.order_number') }}<span class="req">*</span>
             </label>
             <div class="col-md-7">
                 <input class="col-md-6 form-control" id="order_number" name="order_number">
                 {!! $errors->first('order_number', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
             </div>
         </div>

          <!-- User -->

          @include ('partials.forms.edit.user-select-scan', ['translated_name' => trans('general.select_user'), 'fieldname' => 'assigned_to'])
          <!-- Note -->
          <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
            <label for="note" class="col-md-3 control-label">{{ trans('admin/hardware/form.notes') }}</label>
            <div class="col-md-7">
              <textarea class="col-md-6 form-control" id="note" name="note"></textarea>
              {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
            </div>
          </div>
       </div>
        <div class="box-footer">
            <a class="btn btn-link" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
            <div class="pull-right">
                <button type="submit" class="btn btn-primary btn-scan-specific"><i class="fa fa-check icon-white" aria-hidden="true"></i> Save</button>
                <div class="col-md-1" id="loading-gif-submit" style="display: none;">
                    <img src="{{URL::asset('/uploads/assets/loading-waiting.gif')}}" style="height:50px;">
                </div>
            </div>
        </div>
    </div> <!-- .box.box-default -->
  </form>
  </div> <!-- .col-md-9-->
</div> <!-- .row -->
@stop
@section('moar_scripts')
<script type="text/javascript">
    $(".btn-scan-specific").attr('disabled',true);
    $(".search").click(function( event ) {
        $("#loading-gif").show();
        var inventory_tag = $("#inventory_tag").val();
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
                    $("#inventory_tag").val('');
                    $(".btn-scan-specific").attr('disabled',true);
                    $("#invalid-tag").html(response.message);
                    $("#loading-gif").hide();
                    setTimeout(function() { 
                        $("#invalid-tag").html('');
                    }, 3000);
                }
                else{
                    $("#valid-tag").html('Valid Inventory Tag');
                    $("#loading-gif").hide();
                    $("#accessory-name").val(response.accessory_name);
                    $("#category-name").val(response.category_name);
                    $("#inventory_number").val(response.inventory_number);
                    $(".btn-scan-specific").attr('disabled',false);
                    setTimeout(function() { 
                        $("#valid-tag").html('');
                    }, 3000);
                }
            }
        });
        return false;
    });

    //Scan Specific form submission
    $("#scan-specific-form").submit(function( event ) {
        $(".btn-scan-specific").attr('disabled',true);
        $("#loading-gif-submit").show();
    });
</script>
@stop