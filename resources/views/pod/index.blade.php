@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/pod/general.pod') }}
    @parent
@stop


@section('header_right')
    <a href="{{ route('pod.create') }}" class="btn btn-primary pull-right">
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
                                data-columns="{{ \App\Presenters\PodPresenter::dataTableLayout() }}"
                                data-cookie-id-table="podTable"
                                data-pagination="true"
                                data-id-table="podTable"
                                data-search="true"
                                data-show-footer="true"
                                data-side-pagination="server"
                                data-show-columns="true"
                                data-show-export="true"
                                data-show-refresh="true"
                                data-sort-order="asc"
                                id="podTable"
                                class="table table-striped snipe-table"
                                data-url="{{ route('api.pod.index') }}"
                                data-export-options='{
              "fileName": "export-pod-{{ date('Y-m-d') }}",
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
        'columns' => \App\Presenters\PodPresenter::dataTableLayout()
    ])
@stop

