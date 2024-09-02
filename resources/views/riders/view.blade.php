@extends('layouts/default')

{{-- Page title --}}
@section('title')

    {{ $rider->name }}
    @parent
@stop

@section('header_right')
    <div class="btn-group pull-right">
        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">{{ trans('button.actions') }}
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><a href="{{ route('riders.edit', ['rider' => $rider->id]) }}">{{ trans('admin/riders/general.edit') }}</a></li>
            <li><a href="{{ route('riders.create') }}">{{ trans('general.create') }}</a></li>
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
                            data-columns="{{ \App\Presenters\RiderPresenter::dataTableLayout() }}"
                            data-cookie-id-table="riderTable"
                            id="riderTable"
                            data-id-table="riderTable"
                            data-export-options='{
                      "fileName": "export-{{ str_slug($rider->name) }}-licenses-{{ date('Y-m-d') }}",
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
                            data-url="{{ route('api.riders.index',['rider_id'=> $rider->id]) }}">

                    </table>

                </div>
            </div>
        </div>
    </div>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
