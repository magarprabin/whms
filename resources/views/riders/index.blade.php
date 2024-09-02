@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.riders') }}
    @parent
@stop


@section('header_right')
    <a href="{{ route('riders.create') }}" class="btn btn-primary pull-right">
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
                                data-columns="{{ \App\Presenters\RiderPresenter::dataTableLayout() }}"
                                data-cookie-id-table="riderTable"
                                data-pagination="true"
                                data-id-table="riderTable"
                                data-search="true"
                                data-show-footer="true"
                                data-side-pagination="server"
                                data-show-columns="true"
                                data-show-export="true"
                                data-show-refresh="true"
                                data-sort-order="asc"
                                id="riderTable"
                                class="table table-striped snipe-table"
                                data-url="{{ route('api.riders.index') }}"
                                data-export-options='{
              "fileName": "export-riders-{{ date('Y-m-d') }}",
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
    @include ('partials.bootstrap-table-riders',
        ['exportFile' => 'route-export',
        'search' => true,
        'columns' => \App\Presenters\RiderPresenter::dataTableLayout()
    ])
@stop

