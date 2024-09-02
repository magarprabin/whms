@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.logistic_categories') }}
    @parent
@stop


@section('header_right')
    <a href="{{ route('logistic_categories.create') }}" class="btn btn-primary pull-right">
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
                                data-columns="{{ \App\Presenters\LogisticCategoryPresenter::dataTableLayout() }}"
                                data-cookie-id-table="logisticCategoryTable"
                                data-pagination="true"
                                data-id-table="logisticCategoryTable"
                                data-search="true"
                                data-show-footer="true"
                                data-side-pagination="server"
                                data-show-columns="true"
                                data-show-export="true"
                                data-show-refresh="true"
                                data-sort-order="asc"
                                id="logisticCategoryTable"
                                class="table table-striped snipe-table"
                                data-url="{{ route('api.logistic_categories.index') }}"
                                data-export-options='{
              "fileName": "export-logistic-categories-{{ date('Y-m-d') }}",
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
    @include ('partials.bootstrap-table-logistic-categories',
        ['exportFile' => 'logisticCategory-export',
        'search' => true,
        'columns' => \App\Presenters\LogisticCategoryPresenter::dataTableLayout()
    ])
@stop

