<!-- partials/forms/edit/submit.blade.php -->

<div class="box-footer text-right pull-right">
    <a class="btn btn-link text-left" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
    <button type="submit" class="btn btn-primary btn-create-new-inventory"><i class="fa fa-check icon-white" aria-hidden="true"></i> {{ trans('general.save') }}</button>
    <div class="col-md-1" id="loading-gif-submit" style="display: none;">
      <img src="{{URL::asset('/uploads/assets/loading-waiting.gif')}}" style="height:50px;">
    </div>
</div>
<!-- / partials/forms/edit/submit.blade.php -->