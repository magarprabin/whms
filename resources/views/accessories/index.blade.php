@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.accessories') }}
@parent
@stop

@section('header_right')
    @can('create', \App\Models\Accessory::class)
      <a href="{{ route('scan/accessory') }}" class="btn btn-success" style="color: #fff;"> {{ trans('general.scan_specific') }}</a>
        <a href="{{ route('scanall/accessory') }}" class="btn btn-info" style="color: #fff;margin-right: 5px;"> {{ trans('general.scan_all') }}</a>
        <a href="{{ route('accessories.create') }}" class="btn btn-primary pull-right" style="color: #fff;"> {{ trans('general.create') }}</a>
    @endcan
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-body">
        <div class="row">
            <div class="col-md-12">
              <div id="toolbar">
                      {{ Form::open([
                        'method' => 'POST',
                        'route' => ['accessories/bulkgenerate'],
                        'class' => 'form-inline',
                        'id' => 'bulkForm']) }}
                        
                     
                      <label for="bulk_actions"><span class="sr-only">Bulk Actions</span></label>
                      <select name="bulk_actions" class="form-control select2" aria-label="bulk_actions">
                        <option value="labels">{{ trans_choice('button.generate_labels', 2) }}</option>
                      </select>
                      
                      <button class="btn btn-primary" id="bulkEdit" disabled>Go</button>
                      {{ Form::close() }}   
                    </div>
        

            <table
                data-columns="{{ \App\Presenters\AccessoryPresenter::dataTableLayout() }}"
                data-cookie-id-table="accessoriesTable"
                data-pagination="true"
                data-id-table="accessoriesTable"
                data-search="true"
                data-side-pagination="server"
                data-show-columns="true"
                data-show-export="true"
                data-show-refresh="true"
                data-show-footer="true"
                data-sort-order="asc"
                id="accessoriesTable"
                class="table table-striped snipe-table"
                data-url="{{route('api.accessories.index') }}"
                data-export-options='{
                    "fileName": "export-accessories-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                    }'>
          </table>
        
      </div>
    </div>
  </div>
</div>

@stop

@section('moar_scripts')
@include ('partials.bootstrap-table-inventory')
@stop
