<div id="assigned_user" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}">
    {{ Form::label($fieldname, $translated_name, array('class' => 'col-md-3 control-label')) }}
    <div class="col-md-7{{  ((isset($required)) && ($required=='true')) ? ' required' : '' }}">
        <select class="js-data-ajax multiple-select" data-endpoint="filtered-categories" data-placeholder="{{ trans('general.select_category') }}" name="{{ $fieldname.'[]' }}" style="width: 100%" id="category_select" aria-label="{{ $fieldname }}" @if(isset($multiple)) multiple="multiple" @endif>
        @if (isset($item) && isset($item->category_id))
                @foreach(explode(',',$item->category_id) as $key=>$category_id)
                    <option value="{{ $category_id }}" selected="selected" role="option" aria-selected="true"  role="option">
                        {{ (\App\Models\Category::find($category_id)) ? \App\Models\Category::find($category_id)->name : '' }}
                    </option>
                @endforeach
            @else
                <option value=""  role="option">{{ trans('general.select_category') }}</option>
            @endif
        </select>
    </div>

    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span></div>') !!}
</div>

