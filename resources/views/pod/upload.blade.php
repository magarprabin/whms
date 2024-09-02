@extends('layouts/default')
@section('title')
  Upload Page
@stop
@if ((Request::get('company_id')) && ($company))
  {{ $company->name }}
@endif
@push('css')
<link rel="stylesheet" href="{{ url('css/dist/bb_pod.css')}}">
@endpush
@section('content')

<p><img src="{{asset($pod->image)}}" ></p>

<form method="post" action="{{url('/upload_pod').'/'.$pod_id}}" enctype="multipart/form-data">
    @csrf

    <div class="container">
        <label for="">File</label>
        <input type="file" name="image">
    </div>


    <input type="submit" value="Upload">
    
</form>

@stop