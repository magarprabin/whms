@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.routes') }}
@parent
@stop


@section('header_right')
<a href="{{ route('routes.create') }}" class="btn btn-primary pull-right">
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
            data-columns="{{ \App\Presenters\RoutePresenter::dataTableLayout() }}"
            data-cookie-id-table="routeTable"
            data-pagination="true"
            data-id-table="routeTable"
            data-search="true"
            data-show-footer="true"
            data-side-pagination="server"
            data-show-columns="true"
            data-show-export="true"
            data-show-refresh="true"
            data-sort-order="asc"
            id="routeTable"
            class="table table-striped snipe-table"
            data-url="{{ route('api.routes.index') }}"
            data-export-options='{
              "fileName": "export-routes-{{ date('Y-m-d') }}",
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
  @include ('partials.bootstrap-table-routes',
      ['exportFile' => 'route-export',
      'search' => true,
      'columns' => \App\Presenters\RoutePresenter::dataTableLayout()
  ])
@stop

