@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ $statuslabel->name }} {{ trans('general.assets') }}
    @parent
@stop
@section('header_right')
@if ($statuslabel->id == 13)
  <a href="{{ route('hardware/bulkpod') }}" class="btn btn-info" style="color:#fff">Bulk POD</a>
@endif
@stop
{{-- Page content --}}
@section('content')

    <style>
form#searchForm {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    margin-top: 10px;
}
label.col-md-3.control-label {
    width: 106px;
}
input.select2-search__field {
    min-width: 210px;
}
.required {
    border-right: unset;
}
    </style>

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    @if ($statuslabel->id == 13)
                        <div id="toolbar_search"> 
                            {{ Form::open([
                        'method' => 'POST',
                        'route' => ['statuslabels/'.$statuslabel->id],
                        'class' => 'form',
                        'id' => 'searchForm']) }}

                            @include ('partials.forms.edit.supplier-select-multiple', ['translated_name' => trans('admin/routes/general.supplier'),'fieldname' => 'supplier_id', 'required' => 'true','multiple'=>'true'])
                            @include ('partials.forms.edit.route-select-multiple', ['translated_name' => trans('admin/routes/general.name'),'fieldname' => 'route_id', 'required' => 'true','multiple'=>'true'])
                            @include ('partials.forms.edit.logisticcategory-select-multiple', ['translated_name' => trans('admin/logistic_categories/general.category'),'fieldname' => 'category_id', 'required' => 'true','multiple'=>'true'])
                            <center><button class="btn btn-default" id="tool_search" style="width: 80px">Search</button></center>
                            {{ Form::close() }}
                        </div>
                    @endif
                    {{ Form::open([
                      'method' => 'POST',
                      'route' => ['hardware/bulkedit'],
                      'class' => 'form-inline',
                       'id' => 'bulkForm']) }}
                    <div class="row">
                        <div class="col-md-12">
                            @if (Request::get('status')!='Deleted')
                                <div id="toolbar">
                                    <select name="bulk_actions" class="form-control select2">
                                        <option value="edit">Edit</option>
                                        <option value="delete">Delete</option>
                                        <option value="labels">Generate Labels</option>
                                        @if ($statuslabel->id == 13)
                                            <option value="pod">Generate POD</option>
                                        @endif
                                    </select>
                                    <button class="btn btn-default" id="bulkEdit" disabled>Go</button>
                                </div>
                            @endif

                                <table
                                        data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                                        data-cookie-id-table="assetsListingTable"
                                        data-pagination="true"
                                        data-id-table="assetsListingTable"
                                        data-search="true"
                                        data-side-pagination="server"
                                        data-show-columns="true"
                                        data-show-export="true"
                                        data-show-refresh="true"
                                        data-sort-order="asc"
                                        id="assetsListingTable"
                                        class="table table-striped snipe-table"
                                        data-url="{{route('api.assets.index', ['status_id' => $statuslabel->id]) }}"
                                        data-export-options='{
                              "fileName": "export-assets-{{ str_slug($statuslabel->name) }}-assets-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                                </table>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                    {{ Form::close() }}
                </div><!-- ./box-body -->
            </div><!-- /.box -->
        </div>
    </div>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', [
        'exportFile' => 'assets-export',
        'search' => true,
        'columns' => \App\Presenters\AssetPresenter::dataTableLayout()
    ])

@stop
