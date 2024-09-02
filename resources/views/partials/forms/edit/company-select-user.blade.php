<!-- Company -->
@if (($snipeSettings->full_multiple_companies_support=='1') && (!Auth::user()->isSuperUser()))
    <!-- full company support is enabled and this user isn't a superadmin -->
    <div class="form-group">
        <label for="name" class="col-md-3 control-label">{{ $translated_name }}<span class="req">*</span></label>
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
        <label for="name" class="col-md-3 control-label">{{ $translated_name }}<span class="req">*</span></label>
        <div class="col-md-6">
            <select class="company_id form-control" name="company_id" id="company_id" required>
                <option value="">--Select--</option>
                @foreach($company_lists as $company_list)
                    @if(!empty($item))
                        @if ($company_list->id == $item->company_id)
                            <option value="{{ $company_list->id }}" selected="selected">
                                {{ $company_list->name }}
                            </option>
                        @else
                            <option value="{{ $company_list->id }}">
                                {{ $company_list->name }}
                            </option>
                        @endif
                    @else
                        <option value="{{ $company_list->id }}">
                            {{ $company_list->name }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>
        {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg"><i class="fa fa-times"></i> :message</span></div>') !!}

    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span></div>') !!}
    </div>

@endif
