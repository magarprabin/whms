@extends('layouts/edit-form', [
    'createText' => trans('admin/accessories/general.create') ,
    'updateText' => trans('admin/accessories/general.update'),
    'helpPosition'  => 'right',
    'helpText' => trans('help.accessories'),
    'formAction' => (isset($item->id)) ? route('accessories.update', ['accessory' => $item->id]) : route('accessories.store'),
])

{{-- Page content --}}
@section('inputFields')

@include ('partials.forms.edit.company-select', ['translated_name' => trans('general.company'), 'fieldname' => 'company_id'])

<!-- Inventory Tag -->
<div class="form-group {{ $errors->has('inventory_tag') ? ' has-error' : '' }}">
    <label for="inventory_tag" class="col-md-3 control-label">{{ trans('general.inventory_tag') }}</label>

    <!-- we are editing an existing inventory -->
    @if  ($item->id)
        <div class="col-md-7 col-sm-12{{  (\App\Helpers\Helper::checkIfRequired($item, 'inventory')) ? ' required' : '' }}">
            <input readonly class="form-control" type="text" name="inventory_tags[1]" id="inventory_tag" value="{{ Request::old('inventory_tag', $item->inventory_tag) }}" data-validation="required">
            {!! $errors->first('inventory_tags', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
            {!! $errors->first('inventory_tag', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
        </div>
    @else
    <!-- we are creating a new inventory - let people use more than one asset tag -->
        <div class="col-md-7 col-sm-12{{  (\App\Helpers\Helper::checkIfRequired($item, 'inventory_tag')) ? ' required' : '' }}">
            <input readonly class="form-control" type="text" name="inventory_tags[1]" id="inventory_tag" value="{{ Request::old('inventory_tag', \App\Models\Accessory::autoincrement_inventory()) }}" data-validation="required">
            {!! $errors->first('inventory_tags', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
            {!! $errors->first('inventory_tag', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
        </div>
        <div class="col-md-2 col-sm-12">
            <button class="add_inventory_button btn btn-default btn-sm">
                <i class="fa fa-plus"></i>
            </button>
        </div>
    @endif
</div>
<div class="input_inventory_wrap"></div>

@include ('partials.forms.edit.name', ['translated_name' => trans('admin/accessories/general.accessory_name')])
<input type="hidden" name="category_id" value="{{ $item->category_id }}">
@include ('partials.forms.edit.supplier-select-inventory', ['translated_name' => trans('general.supplier'), 'fieldname' => 'supplier_id'])
{{--@include ('partials.forms.edit.manufacturer-select', ['translated_name' => trans('general.manufacturer'), 'fieldname' => 'manufacturer_id'])--}}
@include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'location_id'])
@include ('partials.forms.edit.model_number')
{{--@include ('partials.forms.edit.order_number')--}}
@include ('partials.forms.edit.purchase_date')
{{--@include ('partials.forms.edit.purchase_cost')--}}
{{--@include ('partials.forms.edit.quantity')--}}
{{--@include ('partials.forms.edit.minimum_quantity')--}}


<!-- Image -->
@if ($item->image)
    <div class="form-group {{ $errors->has('image_delete') ? 'has-error' : '' }}">
        <label class="col-md-3 control-label" for="image_delete">{{ trans('general.image_delete') }}</label>
        <div class="col-md-5">
            {{ Form::checkbox('image_delete') }}
            <img src="{{  Storage::disk('public')->url('accessories/'.e($item->image)) }}" class="img-responsive" />
            {!! $errors->first('image_delete', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
        </div>
    </div>
@endif

@include ('partials.forms.edit.notes')

@include ('partials.forms.edit.image-upload')
@stop

@section('moar_scripts')

<script nonce="{{ csrf_token() }}">
    // Add another inventory tag + serial combination if the plus sign is clicked
    $(document).ready(function() {
        var max_fields      = 100; //maximum input boxes allowed
        var wrapper         = $(".input_inventory_wrap"); //Fields wrapper
        var add_button      = $(".add_inventory_button"); //Add button ID
        var x               = 1; //initial text box count
        $(add_button).click(function(e){ //on add input button click
            e.preventDefault();
            var auto_tag        = $("#inventory_tag").val().replace(/[^\d]/g, '');
            var box_html        = '';
            const zeroPad       = (num, places) => String(num).padStart(places, '0');
            // Check that we haven't exceeded the max number of asset fields
            if (x < max_fields) {
                if (auto_tag!='') {
                    auto_tag = zeroPad(parseInt(auto_tag) + parseInt(x),auto_tag.length);
                } else {
                    auto_tag = '';
                }
                x++; //text box increment
                box_html += '<span class="fields_wrapper">';
                box_html += '<div class="form-group"><label for="inventory_tag" class="col-md-3 control-label">{{ trans('general.inventory_tag') }} ' + x + '</label>';
                box_html += '<div class="col-md-7 col-sm-12 required">';
                box_html += '<input readonly type="text"  class="form-control" name="inventory_tags[' + x + ']" value="{{ (($snipeSettings->auto_increment_inventory_prefix!='') && ($snipeSettings->auto_increment_inventory=='1')) ? $snipeSettings->auto_increment_inventory_prefix : '' }}'+ auto_tag +'" data-validation="required">';
                box_html += '</div>';
                box_html += '<div class="col-md-2 col-sm-12">';
                box_html += '<a href="#" class="remove_field btn btn-default btn-sm"><i class="fa fa-minus"></i></a>';
                box_html += '</div>';
                box_html += '</div>';
                box_html += '</div>';
                {{--box_html += '<div class="form-group"><label for="serial" class="col-md-3 control-label">{{ trans('admin/hardware/form.serial') }} ' + x + '</label>';--}}
                {{--box_html += '<div class="col-md-7 col-sm-12">';--}}
                // box_html += '<input type="text"  class="form-control" name="serials[' + x + ']">';
                box_html += '</div>';
                box_html += '</div>';
                box_html += '</span>';
                $(wrapper).append(box_html);
                // We have reached the maximum number of extra asset fields, so disable the button
            } else {
                $(".add_inventory_button").attr('disabled');
                $(".add_inventory_button").addClass('disabled');
            }
        });
        $(wrapper).on("click",".remove_field", function(e){ //user clicks on remove text
            $(".add_inventory_button").removeAttr('disabled');
            $(".add_inventory_button").removeClass('disabled');
            e.preventDefault();
            console.log(x);
            $(this).parent('div').parent('div').parent('span').remove();
            x--;
        })
    });
</script>

@stop