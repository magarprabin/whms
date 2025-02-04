<!-- Company -->
@if (($snipeSettings->full_multiple_companies_support=='1') && (!Auth::user()->isSuperUser()))
    <!-- full company support is enabled and this user isn't a superadmin -->
    <div class="form-group">
    {{ Form::label($fieldname, $translated_name, array('class' => 'col-md-3 control-label')) }}
        <div class="col-md-6">
            <input type="hidden" name="{{ $fieldname }}" value="{{ $company_id = 1 }}">
            <select class="js-data-ajax"  disabled="true" data-endpoint="companies" data-placeholder="{{ trans('general.select_company') }}" name="{{ $fieldname }}" style="width: 100%" id="company_select" aria-label="{{ $fieldname }}">
                @if ($company_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
                    <option value="{{ $company_id }}" selected="selected" role="option" aria-selected="true"  role="option">
                        {{ (\App\Models\Company::find($company_id)) ? \App\Models\Company::find($company_id)->name : '' }}
                    </option>
                @else
{{--                    <option value="" role="option">{{ trans('general.select_company') }}</option>--}}
                    <option value="{{ $company_id = 1 }}" selected="selected">
                    {{ (\App\Models\Company::find($company_id)) ? \App\Models\Company::find($company_id)->name : '' }}
                @endif
            </select>
        </div>
    </div>

@else
    <!-- full company support is enabled or this user is a superadmin -->
    <div id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}">
        {{ Form::label($fieldname, $translated_name, array('class' => 'col-md-3 control-label')) }}
        <div class="col-md-6">
            <input type="hidden" name="{{ $fieldname }}" value="{{ $company_id = 1 }}">
            <select disabled="true" class="js-data-ajax" data-endpoint="companies" data-placeholder="{{ trans('general.select_company') }}" name="{{ $fieldname }}" style="width: 100%" id="company_select">
                @if ($company_id = Request::old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
                    <option value="{{ $company_id = 1 }}" selected="selected">
                        {{ (\App\Models\Company::find($company_id)) ? \App\Models\Company::find($company_id)->name : '' }}
                    </option>
                @else
{{--                    <option value="1">{{ trans('general.select_company') }}</option>--}}
                    <option value="{{ $company_id = 1 }}" selected="selected">
                        {{ (\App\Models\Company::find($company_id)) ? \App\Models\Company::find($company_id)->name : '' }}
                    </option>
                @endif
            </select>
        </div>
        {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg"><i class="fa fa-times"></i> :message</span></div>') !!}

    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span></div>') !!}
    </div>

@endif
