@extends('layouts/default')

@section('title0')

@if ((Request::get('company_id')) && ($company))
  {{ $company->name }}
@endif



@if (Request::get('status'))
  @if (Request::get('status')=='Pending')
    {{ trans('general.pending') }}
  @elseif (Request::get('status')=='RTD')
    {{ trans('general.ready_to_deploy') }}
  @elseif (Request::get('status')=='Deployed')
    {{ trans('general.deployed') }}
  @elseif (Request::get('status')=='Undeployable')
    {{ trans('general.undeployable') }}
  @elseif (Request::get('status')=='Deployable')
    {{ trans('general.deployed') }}
  @elseif (Request::get('status')=='Requestable')
    {{ trans('admin/hardware/general.requestable') }}
  @elseif (Request::get('status')=='Archived')
    {{ trans('general.archived') }}
  @elseif (Request::get('status')=='Deleted')
    {{ trans('general.deleted') }}
  @endif
@else
{{ trans('general.all') }}
@endif
{{ trans('general.assets') }}

  @if (Request::has('order_number'))
    : Order #{{ Request::get('order_number') }}
  @endif
@stop

{{-- Page title --}}
@section('title')
Order Shipment List  @parent
@stop

@section('header_right')
  <a href="{{ route('hardware/bulkcheckin') }}" class="btn btn-success btn-bulk-checkin" style="color:#fff">
    First Time Checkin
  </a>
  <a href="{{ route('hardware/bulkcheckoutsys') }}" class="btn btn-info" style="color:#fff" {{ $disabled_checkout }}>Bulk Checkout</a>
  <a href="{{ route('hardware/individualcheckout') }}" class="btn btn-danger" {{ $disabled_checkout }}>Individual Checkout</a>
  <a href="{{ route('hardware/bulkcheckouttag') }}" class="btn btn-tag-checkout" style="color:#fff; background-color: #5A6DE5;" {{ $disabled_checkout }}>Bulk Tag Checkout</a>
  <a href="{{ route('hardware/hubcheckin') }}" class="btn btn-hub-checkin" style="color:#fff;background-color: #800000;">Hub Checkin</a>
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-body">
       
          <div class="row">
            <div class="col-md-12">
              <div style="display: none;" id="status_labels_dropdown">
                <input list="browsers" name="status_label" id="status_labels" type="text" class="form-control form-control" />
                <datalist id="browsers">
                  @foreach($status_labels_lists as $status_labels_list)
                  <option value="{{ $status_labels_list->name }}">
                    @endforeach
                </datalist>
              </div>
              <div style="display: none;" id="locations_dropdown">
                <input list="locations" name="locations" id="locations_id" type="text" class="form-control form-control" />
                <datalist id="locations">
                  @foreach($locations as $location)
                  <option value="{{ $location->name }}">
                    @endforeach
                </datalist>
              </div>
              <div style="display: none;" id="carriers_dropdown">
                <input list="carriers" id="carriers_id" type="text" class="form-control form-control" />
                <datalist id="carriers">
                  @foreach($carriers as $carrier)
                  <option value="{{ $carrier->carrier_name }}">
                    @endforeach
                </datalist>
              </div>
              <input type="hidden" class="form-control status_label_hidden" name="status_label_hidden" placeholder="Status" id="status_label_hidden" value="1">
              <input type="hidden" class="form-control status_label_dropdown" name="status_label_dropdown" placeholder="Status" id="status_label_dropdown" value="">
              @if (Request::get('status')!='Deleted')
              
                 
                    
                    {{-- <div id="toolbar">
                      {{ Form::open([
                        'method' => 'POST',
                        'route' => ['hardware/bulkedit'],
                        'class' => 'form-inline',
                        'id' => 'bulkForm']) }}
                        
                     
                      <label for="bulk_actions"><span class="sr-only">Bulk Actions</span></label>
                      <select name="bulk_actions" class="form-control select2" aria-label="bulk_actions">
                        <option value="tag">Generate Tag</option>
                      </select>
                      
                      <button class="btn btn-primary" id="bulkEdit" disabled>Go</button>
                      {{ Form::close() }} 
                    </div> --}}
                    
                    <div id="toolbar">
                      
                      {{ Form::open([
                        'method' => 'POST',
                        'route' => ['hardware/updatebulkstatus'],
                        'class' => 'form-inline',
                        'id' => 'bulkStatusForm']) }}
                        
                     
                      <label for="status_id">Bulk Status Actions </label>
                      {{ Form::select('status_id', $statuslabel_list , null, array('class'=>'select2 status_id','id'=>'status_select_id', 'aria-label'=>'status_id', 'required' => "required")) }}
                      
                      <button class="btn btn-primary" id="bulkStatusEdit" disabled>Change Status</button>
                      {{ Form::close() }}
                    </div>
                   
              @endif

              <table
                data-advanced-search="true"
                data-click-to-select="true"
                data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                data-cookie-id-table="assetsListingTable"
                data-pagination="true"
                data-id-table="assetsListingTable"
                data-search="true"
                data-side-pagination="server"
                data-show-columns="true"
                data-show-export="true"
                data-show-footer="true"
                data-show-refresh="true"
                data-sort-order="asc"
                data-sort-name="name"
                data-toolbar="#toolbar"
                id="assetsListingTable"
                class="table table-striped snipe-table"
                data-url="{{ route('api.assets.index',
                    array('status' => e(Request::get('status')),
                    'order_number'=>e(Request::get('order_number')),
                    'company_id'=>e(Request::get('company_id')),
                    'status_id'=>e(Request::get('status_id')))) }}"
                data-export-options='{
                "fileName": "export{{ (Request::has('status')) ? '-'.str_slug(Request::get('status')) : '' }}-assets-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
              </table>

            </div><!-- /.col -->
          </div><!-- /.row -->
        
      </div><!-- ./box-body -->
    </div><!-- /.box -->
  </div>
</div>
@stop

@section('moar_scripts')
@include('partials.bootstrap-table-assets')
@stop
