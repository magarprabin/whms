@extends('layouts/edit-form', [
    'createText' => trans('admin/logistic_categories/general.create') ,
    'updateText' => trans('admin/logistic_categories/general.update'),
    'helpPosition'  => 'right',
    'helpText' => trans('help.logistic_categories'),
    'topSubmit'  => 'true',
    'formAction' => (isset($item->id)) ? route('logistic_categories.update', ['logistic_category' => $item->id]) : route('logistic_categories.store'),
])

@section('inputFields')

    @include ('partials.forms.edit.name', ['translated_name' => trans('admin/logistic_categories/general.name')])
    @include ('partials.forms.edit.category-select-multiple', ['translated_name' => trans('admin/logistic_categories/general.category'),'fieldname' => 'category_id', 'required' => 'true','multiple'=>'true'])
    @include('partials.forms.edit.route-status',['translated_name'=>trans('general.status'),'field_name'=>'status','required'=>'true','statuslabel_list'=>['1'=>'Active','0'=>'Inactive']])
@stop

@section('content')
    @parent

    @if ($snipeSettings->default_eula_text!='')
        <!-- Modal -->
        <div class="modal fade" id="eulaModal" tabindex="-1" role="dialog" aria-labelledby="eulaModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h2 class="modal-title" id="eulaModalLabel">{{ trans('admin/settings/general.default_eula_text') }}</h2>
                    </div>
                    <div class="modal-body">
                        {{ \App\Models\Setting::getDefaultEula() }}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('button.cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop