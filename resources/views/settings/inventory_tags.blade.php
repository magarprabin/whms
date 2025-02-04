@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Update Inventory Tag Settings
    @parent
@stop

@section('header_right')
    <a href="{{ route('settings.index') }}" class="btn btn-primary"> {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')

    <style>
        .checkbox label {
            padding-right: 40px;
        }
    </style>


    {{ Form::open(['method' => 'POST', 'files' => false, 'autocomplete' => 'off', 'class' => 'form-horizontal', 'role' => 'form' ]) }}
    <!-- CSRF Token -->
    {{csrf_field()}}

    <div class="row">
        <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">


            <div class="panel box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">
                        <i class="fa fa-list-ol"></i> Inventory Tags
                        </h4>
                </div>
                <div class="box-body">


                    <div class="col-md-11 col-md-offset-1">

                        <!-- auto ids -->
                        <div class="form-group">
                            <div class="col-md-5">
                                {{ Form::label('auto_increment_inventory', trans('admin/settings/general.auto_increment_inventory')) }}
                            </div>
                            <div class="col-md-7">
                                {{ Form::checkbox('auto_increment_inventory', '1', old('auto_increment_inventory', $setting->auto_increment_inventory),array('class' => 'minimal', 'aria-label'=>'auto_increment_inventory')) }}
                                {{ trans('admin/settings/general.enabled') }}
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-5">
                                {{ Form::label('next_auto_inventory_tag_base', trans('admin/settings/general.next_auto_inventory_tag_base')) }}
                            </div>
                            <div class="col-md-7">
                                {{ Form::text('next_auto_inventory_tag_base', old('next_auto_inventory_tag_base', $setting->next_auto_inventory_tag_base), array('class' => 'form-control', 'style'=>'width: 150px;', 'aria-label'=>'next_auto_inventory_tag_base')) }}
                                {!! $errors->first('next_auto_inventory_tag_base', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                            </div>
                        </div>


                        <!-- auto prefix -->
                        <div class="form-group {{ $errors->has('auto_increment_inventory_prefix') ? 'error' : '' }}">
                            <div class="col-md-5">
                                {{ Form::label('auto_increment_inventory_prefix', trans('admin/settings/general.auto_increment_inventory_prefix')) }}
                            </div>
                            <div class="col-md-7">
                                @if ($setting->auto_increment_inventory == 1)
                                    {{ Form::text('auto_increment_inventory_prefix', old('auto_increment_inventory_prefix', $setting->auto_increment_inventory_prefix), array('class' => 'form-control', 'style'=>'width: 150px;', 'aria-label'=>'auto_increment_inventory_prefix')) }}
                                    {!! $errors->first('auto_increment_inventory_prefix', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                                @else
                                    {{ Form::text('auto_increment_inventory_prefix', old('auto_increment_inventory_prefix', $setting->auto_increment_inventory_prefix), array('class' => 'form-control', 'disabled'=>'disabled', 'style'=>'width: 150px;', 'aria-label'=>'auto_increment_inventory_prefix')) }}
                                @endif
                            </div>
                        </div>

                        <!-- auto zerofill -->
                        <div class="form-group {{ $errors->has('inventory_zerofill_count') ? 'error' : '' }}">
                            <div class="col-md-5">
                                {{ Form::label('inventory_zerofill_count', trans('admin/settings/general.inventory_zerofill_count')) }}
                            </div>
                            <div class="col-md-7">
                                {{ Form::text('inventory_zerofill_count', old('inventory_zerofill_count', $setting->inventory_zerofill_count), array('class' => 'form-control', 'style'=>'width: 150px;', 'aria-label'=>'inventory_zerofill_count')) }}
                                {!! $errors->first('inventory_zerofill_count', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
                            </div>
                        </div>

                    </div>

                </div> <!--/.box-body-->
                <div class="box-footer">
                    <div class="text-left col-md-6">
                        <a class="btn btn-link text-left" href="{{ route('settings.index') }}">{{ trans('button.cancel') }}</a>
                    </div>
                    <div class="text-right col-md-6">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-check icon-white" aria-hidden="true"></i> {{ trans('general.save') }}</button>
                    </div>

                </div>
            </div> <!-- /box -->
        </div> <!-- /.col-md-8-->
    </div> <!-- /.row-->

    {{Form::close()}}

@stop
