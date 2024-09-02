@extends('layouts/default')

{{-- Page title --}}
@section('title')

    {{ $logisticCategory->name }}
    @parent
@stop

@section('header_right')
    <div class="btn-group pull-right">
        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">{{ trans('button.actions') }}
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><a href="{{ route('logistic_categories.edit', ['route' => $logisticCategory->id]) }}">{{ trans('admin/logistic_categories/general.edit') }}</a></li>
            <li><a href="{{ route('logistic_categories.create') }}">{{ trans('general.create') }}</a></li>
        </ul>
    </div>
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-body">

                    <table
                            data-columns="{{ \App\Presenters\LogisticCategoryPresenter::dataTableLayout() }}"
                            data-cookie-id-table="routeTable"
                            id="routeTable"
                            data-id-table="routeTable"
                            data-export-options='{
                      "fileName": "export-{{ str_slug($logisticCategory->name) }}-licenses-{{ date('Y-m-d') }}",
                      "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                      }'
                            data-pagination="true"
                            data-search="true"
                            data-show-footer="true"
                            data-side-pagination="server"
                            data-show-columns="true"
                            data-show-export="true"
                            data-show-refresh="true"
                            data-sort-order="asc"
                            class="table table-striped snipe-table"
                            data-url="{{ route('api.logistic_categories.index',['logisticCategory_id'=> $logisticCategory->id]) }}">

                    </table>

                </div>
            </div>
        </div>
    </div>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
