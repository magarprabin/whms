
@extends('layouts/edit-form', [
    'createText' => trans('admin/hardware/form.create'),
    'updateText' => trans('admin/hardware/form.update'),
    'topSubmit' => true,
    'helpText' => trans('help.assets'),
    'helpPosition' => 'right',
    'formAction' => ($item->id) ? route('hardware.update', ['hardware' => $item->id]) : route('hardware.store'),
])


{{-- Page content --}}

@section('inputFields')

    @include ('partials.forms.edit.company-select', ['translated_name' => trans('general.company'), 'fieldname' => 'company_id'])

    @include ('partials.forms.edit.product-similar')
  <!-- Asset Tag -->
  <div class="form-group {{ $errors->has('asset_tag') ? ' has-error' : '' }}">
    <label for="asset_tag" class="col-md-3 control-label">{{ trans('admin/hardware/form.tag') }}</label>

      <!-- we are editing an existing asset -->
      @if  ($item->id)
          <div class="col-md-7 col-sm-12{{  (\App\Helpers\Helper::checkIfRequired($item, 'asset_tag')) ? ' required' : '' }}">
          <input class="form-control" type="text" name="asset_tags[1]" id="asset_tag" value="{{ Request::old('asset_tag', $item->asset_tag) }}" data-validation="required">
              {!! $errors->first('asset_tags', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
              {!! $errors->first('asset_tag', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
          </div>
      @else
          <!-- we are creating a new asset - let people use more than one asset tag -->
          <div class="col-md-7 col-sm-12{{  (\App\Helpers\Helper::checkIfRequired($item, 'asset_tag')) ? ' required' : '' }}">
              <input class="form-control" type="text" name="asset_tags[1]" id="asset_tag" value="{{ Request::old('asset_tag', \App\Models\Asset::autoincrement_asset()) }}" data-validation="required">
              {!! $errors->first('asset_tags', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
              {!! $errors->first('asset_tag', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
          </div>
          <div class="col-md-2 col-sm-12">
              <button class="add_field_button btn btn-default btn-sm">
                  <i class="fa fa-plus"></i>
              </button>
          </div>
      @endif
  </div>
    <?php /*@include ('partials.forms.edit.serial', ['fieldname'=> 'serials[1]', 'translated_serial' => trans('admin/hardware/form.serial')]) */?>

    <div class="input_fields_wrap"></div>


    @include ('partials.forms.edit.model-select', ['translated_name' => trans('admin/hardware/form.model'), 'fieldname' => 'model_id', 'field_req' => true])
    <div class="model_id_wrap"></div>
    <div id='custom_fields_content'>
        <!-- Custom Fields -->
        @if ($item->model && $item->model->fieldset)
        <?php $model=$item->model; ?>
        @endif
        @if (Request::old('model_id'))
        <?php $model=\App\Models\AssetModel::find(Request::old('model_id')); ?>
        @elseif (isset($selected_model))
        <?php $model=$selected_model; ?>
        @endif
        @if (isset($model) && $model)
        @include("models/custom_fields_form",["model" => $model])
        @endif
    </div>

  @include ('partials.forms.edit.status', [ 'required' => 'true'])
  @if (!$item->id)
      @include ('partials.forms.checkout-selector', ['user_select' => 'true','asset_select' => 'true', 'location_select' => 'true', 'style' => 'display:none;'])
      @include ('partials.forms.checkout-selector-picked', ['user_select' => 'true','asset_select' => 'true', 'location_select' => 'true', 'style' => 'display:none;'])
      @include ('partials.forms.edit.user-select', ['translated_name' => trans('admin/hardware/form.checkout_to'), 'fieldname' => 'assigned_user', 'style' => 'display:none;', 'required' => 'false'])
      @include ('partials.forms.edit.asset-select', ['translated_name' => trans('admin/hardware/form.checkout_to'), 'fieldname' => 'assigned_asset', 'style' => 'display:none;', 'required' => 'false'])
      @include ('partials.forms.edit.location-select', ['translated_name' => trans('admin/hardware/form.checkout_to'), 'fieldname' => 'assigned_location', 'style' => 'display:none;', 'required' => 'false'])
  @elseif (($item->assignedTo) && ($item->deleted_at==''))
      <!-- This is an asset and it's currently deployed, so let them edit the expected checkin date -->
      @include ('partials.forms.edit.datepicker', ['translated_name' => trans('admin/hardware/form.expected_checkin'),'fieldname' => 'expected_checkin'])
  @endif

  @include ('partials.forms.edit.product_name', ['translated_name' => trans('admin/hardware/form.name')])
  <div class="product_name_wrap"></div>
  <?php /*@include ('partials.forms.edit.purchase_date')*/?>
  @include ('partials.forms.edit.supplier-select', ['translated_name' => trans('general.supplier'), 'fieldname' => 'supplier_id'])
  @include ('partials.forms.edit.product_order_number')
  <div class="product_order_number_wrap"></div>
    <?php
    $currency_type=null;
    if ($item->id && $item->location) {
        $currency_type = $item->location->currency;
    }
    ?>
  <?php /*@include ('partials.forms.edit.purchase_cost', ['currency_type' => $currency_type])
  @include ('partials.forms.edit.warranty')*/?>

  @include ('partials.forms.edit.notes')

  <?php /*@include ('partials.forms.edit.location-select', ['translated_name' => trans('admin/hardware/form.default_location'), 'fieldname' => 'rtd_location_id'])*/?>


    <!-- Image -->
    @if ($item->image)
    <div class="form-group {{ $errors->has('image_delete') ? 'has-error' : '' }}">
        <label class="col-md-3 control-label" for="image_delete">{{ trans('general.image_delete') }}</label>
        <div class="col-md-5">
            <label class="control-label" for="image_delete">
            <input type="checkbox" value="1" name="image_delete" id="image_delete" class="minimal" {{ Request::old('image_delete') == '1' ? ' checked="checked"' : '' }}>
            {!! $errors->first('image_delete', '<span class="alert-msg">:message</span>') !!}
            </label>
            <div style="margin-top: 0.5em">
                <img src="{{ Storage::disk('public')->url(app('assets_upload_path').e($item->image)) }}" class="img-responsive" />
            </div>
        </div>
    </div>
    @endif

@include ('partials.forms.edit.product-image-upload')
<div class="product_image_wrap"></div>
@stop

@section('moar_scripts')



<script nonce="{{ csrf_token() }}">


    var transformed_oldvals={};

    function fetchCustomFields() {
        //save custom field choices
        var oldvals = $('#custom_fields_content').find('input,select').serializeArray();
        for(var i in oldvals) {
            transformed_oldvals[oldvals[i].name]=oldvals[i].value;
        }

        var modelid = $('#model_select_id').val();
        if (modelid == '') {
            $('#custom_fields_content').html("");
        } else {

            $.ajax({
                type: 'GET',
                url: "{{url('/') }}/models/" + modelid + "/custom_fields",
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                _token: "{{ csrf_token() }}",
                dataType: 'html',
                success: function (data) {
                    $('#custom_fields_content').html(data);
                    $('#custom_fields_content select').select2(); //enable select2 on any custom fields that are select-boxes
                    //now re-populate the custom fields based on the previously saved values
                    $('#custom_fields_content').find('input,select').each(function (index,elem) {
                        if(transformed_oldvals[elem.name]) {
                            $(elem).val(transformed_oldvals[elem.name]).trigger('change'); //the trigger is for select2-based objects, if we have any
                        }

                    });
                }
            });
        }
    }

    function user_add(status_id) {

        if (status_id != '') {
            $(".status_spinner").css("display", "inline");
            $.ajax({
                url: "{{url('/') }}/api/v1/statuslabels/" + status_id + "/deployable",
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    $(".status_spinner").css("display", "none");
                    $("#selected_status_status").fadeIn();
                    var status = $('#status_select_id').val();
                    if (data == true) {
                        if(status == '1'){
                            $("#assignto_selector_picked").show();
                            $("#assignto_selector").hide();
                            $("#assigned_user").show();
                        }
                        else if(status == '5'){
                            $("#assignto_selector_picked").hide();
                            $("#assignto_selector").hide();
                            $("#assigned_user").hide();
                        }
                        else{
                            $("#assignto_selector_picked").hide();
                            $("#assignto_selector").show();
                            $("#assigned_user").show();
                        }

                        $("#selected_status_status").removeClass('text-danger');
                        $("#selected_status_status").removeClass('text-warning');
                        $("#selected_status_status").addClass('text-success');
                        $("#selected_status_status").html('<i class="fa fa-check"></i> That status is deployable. This asset can be checked out.');


                    } else {
                        $("#assignto_selector").hide();
                        $("#selected_status_status").removeClass('text-danger');
                        $("#selected_status_status").removeClass('text-success');
                        $("#selected_status_status").addClass('text-warning');
                        $("#selected_status_status").html('<i class="fa fa-warning"></i> That asset status is not deployable. This asset cannot be checked out. ');
                    }
                }
            });
        }
    }


    $(function () {
        //grab custom fields for this model whenever model changes.
        $('#model_select_id').on("change", fetchCustomFields);

        //initialize assigned user/loc/asset based on statuslabel's statustype
        user_add($(".status_id option:selected").val());

        //whenever statuslabel changes, update assigned user/loc/asset
        $(".status_id").on("change", function () {
            user_add($(".status_id").val());
        });

    });


    // Add another asset tag + serial combination if the plus sign is clicked
    $(document).ready(function() {

        var max_fields      = 100; //maximum input boxes allowed
        var wrapper         = $(".input_fields_wrap"); //Fields wrapper
        var add_button      = $(".add_field_button"); //Add button ID
        var x               = 1; //initial text box count
        var wrapper_product_name = $(".product_name_wrap");
        var image_field = $(".product_image_wrap");
        var order_number_field = $(".product_order_number_wrap");

        $(add_button).click(function(e){ //on add input button click

            e.preventDefault();

            var auto_tag        = $("#asset_tag").val().replace(/[^\d]/g, '');
            var box_html        = '';
            var product_name_html        = '';
            var image_html = '';
            var order_number_html = '';
			const zeroPad 		= (num, places) => String(num).padStart(places, '0');

            // Check that we haven't exceeded the max number of asset fields
            if (x < max_fields) {

                if (auto_tag!='') {
                     auto_tag = zeroPad(parseInt(auto_tag) + parseInt(x),auto_tag.length);
                } else {
                     auto_tag = '';
                }

                x++; //text box increment

                box_html += '<span class="fields_wrapper">';
                box_html += '<div class="form-group"><label for="asset_tag" class="col-md-3 control-label">{{ trans('admin/hardware/form.tag') }} ' + x + '</label>';
                box_html += '<div class="col-md-7 col-sm-12 required">';
                box_html += '<input type="text"  class="form-control" name="asset_tags[' + x + ']" value="{{ (($snipeSettings->auto_increment_prefix!='') && ($snipeSettings->auto_increment_assets=='1')) ? $snipeSettings->auto_increment_prefix : '' }}'+ auto_tag +'" data-validation="required">';
                box_html += '</div>';
                box_html += '<div class="col-md-2 col-sm-12">';
                box_html += '<a href="#" class="remove_field btn btn-default btn-sm" tag_id="'+x+'"><i class="fa fa-minus"></i></a>';
                box_html += '</div>';
                box_html += '</div>';
                box_html += '</div>';
                box_html += '</span>';
                $(wrapper).append(box_html);
                var similar = $("input[type='radio'][name='similar_name']:checked").val();
                if(similar == '0'){
                    //for product name @author prabinthapamagar
                    product_name_html += '<div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">';
                    product_name_html += '<label for="name" class="col-md-3 control-label">Product Name ' + x + '</label>';
                    product_name_html += '<div class="col-md-7 col-sm-12{{  (\App\Helpers\Helper::checkIfRequired($item, 'name')) ? ' required' : '' }}">';
                    product_name_html += '<input class="form-control" type="text" name="product_name[' + x + ']" aria-label="name" id="product_name'+x+'" value="{{ old('name', $item->name) }}"{!!  (\App\Helpers\Helper::checkIfRequired($item, 'name')) ? ' data-validation="required" required' : '' !!} />';
                    product_name_html += '{!! $errors->first('name', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}';
                    product_name_html += '</div>';
                    product_name_html += '</div>';
                    $(wrapper_product_name).append(product_name_html);
                }
                //Image Upload
                image_html += '<div class="form-group imag">';
                image_html += '<label class="col-md-3 control-label" for="image">Upload Image' + x + '</label>';
                image_html += '<div class="col-md-9">';
                image_html += '<input type="file" id="image' + x + '" name="image' + x + '" aria-label="image" class="sr-only">';
                image_html += '<label class="btn btn-default" aria-hidden="true">';
                image_html += '{{ trans('button.select_file')  }}';
                image_html += '<input type="file" name="image' + x + '" class="js-uploadFile-add" id="uploadFile' + x + '" data-maxsize="{{ \App\Helpers\Helper::file_upload_max_size() }}" accept="image/gif,image/jpeg,image/webp,image/png,image/svg,image/svg+xml" style="display:none; max-width: 90%" aria-label="image" aria-hidden="true">';
                image_html += '</label>';
                image_html += '<span class="label label-default" id="uploadFile' + x + '-info"></span>';
                image_html += '<p class="help-block" id="uploadFile' + x + '-status">{{ trans('general.image_filetypes_help', ['size' => \App\Helpers\Helper::file_upload_max_size_readable()]) }}</p>';
                image_html += `{!! $errors->first('image', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}`;
                image_html += '</div>';
                image_html += '<div class="col-md-4 col-md-offset-3" aria-hidden="true">';
                image_html += '<img id="uploadFile' + x + '-imagePreview" style="max-width: 200px; display: none;" alt="Uploaded image thumbnail">';
                image_html += '</div>';
                image_html += '</div>';
                $(image_field).append(image_html);
                if(similar == '0'){
                    //for product order number @author prabinthapamagar
                    order_number_html += '<div class="form-group {{ $errors->has('order_number') ? ' has-error' : '' }}">';
                    order_number_html += '<label for="order_number" class="col-md-3 control-label">Order Number ' + x + '</label>';
                    order_number_html += '<div class="col-md-7 col-sm-12">';
                    order_number_html += '<input class="form-control order_number" type="text" name="order_number[' + x + ']" aria-label="order_number" id="order_number'+x+'" value="{{ old('order_number', $item->order_number) }}" />';
                    order_number_html += '{!! $errors->first('order_number', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}';
                    order_number_html += '</div>';
                    order_number_html += '</div>';
                    $(order_number_field).append(order_number_html);
                }
                function htmlEntities(str) {
                    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                }
                function formatBytes(bytes) {
                    if(bytes < 1024) return bytes + " Bytes";
                    else if(bytes < 1048576) return(bytes / 1024).toFixed(2) + " KB";
                    else if(bytes < 1073741824) return(bytes / 1048576).toFixed(2) + " MB";
                    else return(bytes / 1073741824).toFixed(2) + " GB";
                }
                function readURL(input, $preview) {
                    if (input.files && input.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            $preview.attr('src', e.target.result);
                        };
                        reader.readAsDataURL(input.files[0]);
                    }
                }
                $(".js-uploadFile-add").bind("change",function(){
                    var $this = $(this);
                    var id = '#' + $this.attr('id');
                    var status = id + '-status';
                    var $status = $(status);
                    $status.removeClass('text-success').removeClass('text-danger');
                    $(status + ' .goodfile').remove();
                    $(status + ' .badfile').remove();
                    $(status + ' .previewSize').hide();
                    $(id + '-info').html('');

                    var max_size = $this.data('maxsize');
                    var total_size = 0;

                    for (var i = 0; i < this.files.length; i++) {
                        total_size += this.files[i].size;
                        $(id + '-info').append('<span class="label label-default">' + htmlEntities(this.files[i].name) + ' (' + formatBytes(this.files[i].size) + ')</span> ');
                    }

                    console.log('Max size is: ' + max_size);
                    console.log('Real size is: ' + total_size);
                    if (total_size > max_size) {
                        $status.addClass('text-danger').removeClass('help-block').prepend('<i class="badfile fa fa-times"></i> ').append('<span class="previewSize"> Upload is ' + formatBytes(total_size) + '.</span>');
                    } else {

                        $status.addClass('text-success').removeClass('help-block').prepend('<i class="goodfile fa fa-check"></i> ');
                        var $preview =  $(id + '-imagePreview');
                        readURL(this, $preview);
                        $preview.fadeIn();
                    }

                });
            // We have reached the maximum number of extra asset fields, so disable the button
            } else {
                $(".add_field_button").attr('disabled');
                $(".add_field_button").addClass('disabled');
            }
        });

        $(wrapper).on("click",".remove_field", function(e){ //user clicks on remove text
            $(".add_field_button").removeAttr('disabled');
            $(".add_field_button").removeClass('disabled');
            e.preventDefault();
            console.log(x);

            $(this).parent('div').parent('div').parent('span').remove();
            x--;
        })
        $(wrapper).on("click",".remove_field", function(e){ //user clicks on remove text
            $(".add_field_button").removeAttr('disabled');
            $(".add_field_button").removeClass('disabled');
            e.preventDefault();
            console.log(x);
            var tag_id = $(this).attr('tag_id');
            $(this).parent('div').parent('div').parent('span').remove();
            $("#image"+tag_id).parent('div').parent('div').remove();
            var similar = $("input[type='radio'][name='similar_name']:checked").val();
            if(similar == '0'){
                $("#product_name"+tag_id).parent('div').parent('div').remove();
                $("#order_number"+tag_id).parent('div').parent('div').remove();
            }
            x--;
        });

        //add rule form submission
        $("#create-form").submit(function( event ) {
            //validation for mandatory order number entry
            for (i = 1; i <= x; i++) {
                var order_number = $("#order_number"+i).val();
                if(order_number == ''){
                    alert("Order Number required");return false;
                }
            } 
        });
    });


</script>
@stop
