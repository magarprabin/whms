@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.vehicles') }}
    @parent
@stop


@section('header_right')
    <a href="{{ route('vehicles.create') }}" class="btn btn-primary pull-right">
        {{ trans('general.create') }}</a>
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-body">
                    <div class="table-responsive">

                        <table
                                data-columns="{{ \App\Presenters\VehiclePresenter::dataTableLayout() }}"
                                data-cookie-id-table="vehicleTable"
                                data-pagination="true"
                                data-id-table="vehicleTable"
                                data-search="true"
                                data-show-footer="true"
                                data-side-pagination="server"
                                data-show-columns="true"
                                data-show-export="true"
                                data-show-refresh="true"
                                data-sort-order="asc"
                                id="vehicleTable"
                                class="table table-striped snipe-table"
                                data-url="{{ route('api.vehicles.index') }}"
                                data-export-options='{
              "fileName": "export-vehicles-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
                        </table>
                    </div>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div>
    </div>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table-vehicles',
        ['exportFile' => 'vehicle-export',
        'search' => true,
        'columns' => \App\Presenters\VehiclePresenter::dataTableLayout()
    ])
@stop

