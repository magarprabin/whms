@extends('layouts/default')

{{-- Page title --}}

@section('title')
    @parent
@stop

{{-- Page content --}}
@section('content')
    <h1 class="order_detail" style="text-align: center; font-size: 5em; margin-top: -50px; margin-bottom: 5px; font-weight: bold; color: #1a5c82;">{{ trans('general.current_order_detail') }}</h1>
    <style>
        @media only screen and (max-width: 600px) {
            h1, h3{
                font-size: 2em !important;
                line-height: 50px !important;
                font-weight: 300;
            }
            .inner{
                height: 350px !important;
            }
        }
        .row{
            margin-right: -15px !important;
            margin-left: -40px !important;
        }
        .main-sidebar, .main-header{
            display: none !important;
        }
        .skin-blue .wrapper{
            background-color: #ecf0f5 !important;
        }
        .bg-teal{
            background-color: #7b4674 !important;
            border-radius: 25px !important;
        }
        .bg-orange{
            background-color: rgb(193 140 49) !important;
            border-radius: 25px !important;
        }
        h1{
            font-family: unset !important;
        }
        h3{
            font-family: serif;
        }
        .inner{
            height: 350px !important;
        }

    </style>

    <div class="row">
        <!-- panel -->
        <div class="col-lg-6 col-xs-12">
                <!-- small box -->
                <div class="small-box bg-teal">
                    <div class="inner">

                        <table style="border-collapse: collapse;">
                            <tbody>
                            <tr>
                                <th scope="row"><h1 style="line-height: 50px;font-weight: 600;font-size: 4em;">{{ trans('general.total_order') }}&nbsp;</h1></th>
                                <td><h1 style="line-height: 50px;font-weight: 600;font-size: 4em;">: {{ $result['total'] }}</h1></td>
                            </tr>
                            <tr>
                                <th scope="row"><h3 style="line-height: 50px;">{{ trans('general.open') }}&nbsp;</h3></th>
                                <td><h3 style="line-height: 50px;">: {{ $result['open'] }}</h3></td>
                            </tr>
                            <tr>
                                <th scope="row"><h3 style="line-height: 50px;">{{ trans('general.paid') }}&nbsp;</h3></th>
                                <td><h3 style="line-height: 50px;">: {{ $result['paid'] }}</h3></td>
                            </tr>
                            <tr>
                                <th scope="row"><h3 style="line-height: 50px;">{{ trans('general.complete') }}&nbsp;</h3></th>
                                <td><h3 style="line-height: 50px;">: {{ $result['complete'] }}</h3></td>
                            </tr>
                            <tr>
                                <th scope="row"><h3 style="line-height: 50px;">{{ trans('general.cancelled') }}&nbsp;</h3></th>
                                <td><h3 style="line-height: 50px;">: {{ $result['cancelled'] }}</h3></td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
        </div>

        <div class="col-lg-6 col-xs-12">
            <!-- small box -->
            <div class="small-box bg-orange">
                <div class="inner">

                    <table style="border-collapse: collapse;">
                        <tbody>
                        <tr>
                            <th scope="row"><h1 style="line-height: 60px;font-weight: 600;font-size: 4em;">{{ trans('general.total_order_value') }}&nbsp;</h1></th>
                            @if ($result['total_order_value'])
                                <td><h1 style="line-height: 60px;font-weight: 600;font-size: 5em;">: रु {{ $result['total_order_value'] }}</h1></td>
                            @else
                                <td><h1 style="line-height: 60px;font-weight: 600;font-size: 4em;">: रु 0.00</h1></td>
                            @endif
                        </tr>
                        <tr>
                            <th scope="row"><h3 style="line-height: 60px;">{{ trans('general.paid_amount') }}&nbsp;</h3></th>
                            @if ($result['paid_amount'])
                                <td><h3 style="line-height: 60px;">: रु {{ $result['paid_amount'] }}</h3></td>
                            @else
                                <td><h3 style="line-height: 60px;">: रु 0.00</h3></td>
                            @endif
                        </tr>
                        <tr>
                            <th scope="row"><h3 style="line-height: 60px;">{{ trans('general.cancelled_amount') }}&nbsp;</h3></th>
                            @if ($result['cancelled_amount'])
                                <td><h3 style="line-height: 60px;">: रु {{ $result['cancelled_amount'] }}</h3></td>
                            @else
                                <td><h3 style="line-height: 60px;">: रु 0.00</h3></td>
                            @endif
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <h1 class="order_detail" style="text-align: center; font-size: 5em; font-weight: bold; color: #1a5c82;">{{ trans('general.previous_order_detail') }}</h1>
    <div class="row">
        <!-- panel -->
        <div class="col-lg-6 col-xs-12">
            <!-- small box -->
            <div class="small-box bg-orange">
                <div class="inner">

                    <table style="border-collapse: collapse;">
                        <tbody>
                        <tr>
                            <th scope="row"><h1 style="line-height: 50px;font-weight: 600;font-size: 4em;">{{ trans('general.total_order') }}&nbsp;</h1></th>
                            <td><h1 style="line-height: 50px;font-weight: 600;font-size: 4em;">: {{ $result_p['total'] }}</h1></td>
                        </tr>
                        <tr>
                            <th scope="row"><h3 style="line-height: 50px;">{{ trans('general.open') }}&nbsp;</h3></th>
                            <td><h3 style="line-height: 50px;">: {{ $result_p['open'] }}</h3></td>
                        </tr>
                        <tr>
                            <th scope="row"><h3 style="line-height: 50px;">{{ trans('general.paid') }}&nbsp;</h3></th>
                            <td><h3 style="line-height: 50px;">: {{ $result_p['paid'] }}</h3></td>
                        </tr>
                        <tr>
                            <th scope="row"><h3 style="line-height: 50px;">{{ trans('general.complete') }}&nbsp;</h3></th>
                            <td><h3 style="line-height: 50px;">: {{ $result_p['complete'] }}</h3></td>
                        </tr>
                        <tr>
                            <th scope="row"><h3 style="line-height: 50px;">{{ trans('general.cancelled') }}&nbsp;</h3></th>
                            <td><h3 style="line-height: 50px;">: {{ $result_p['cancelled'] }}</h3></td>
                        </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>

        <div class="col-lg-6 col-xs-12">
            <!-- small box -->
            <div class="small-box bg-teal">
                <div class="inner">

                    <table style="border-collapse: collapse;">
                        <tbody>
                        <tr>
                            <th scope="row"><h1 style="line-height: 60px;font-weight: 600;font-size: 4em;">{{ trans('general.total_order_value') }}&nbsp;</h1></th>
                            @if ($result_p['total_order_value'])
                                <td><h1 style="line-height: 60px;font-weight: 600;font-size: 4em;">: रु {{ $result_p['total_order_value'] }}</h1></td>
                            @else
                                <td><h1 style="line-height: 60px;font-weight: 600;font-size: 5em;">: रु 0.00</h1></td>
                            @endif
                        </tr>
                        <tr>
                            <th scope="row"><h3 style="line-height: 60px;">{{ trans('general.paid_amount') }}&nbsp;</h3></th>
                            @if ($result_p['paid_amount'])
                                <td><h3 style="line-height: 60px;">: रु {{ $result_p['paid_amount'] }}</h3></td>
                            @else
                                <td><h3 style="line-height: 60px;">: रु 0.00</h3></td>
                            @endif
                        </tr>
                        <tr>
                            <th scope="row"><h3 style="line-height: 60px;">{{ trans('general.cancelled_amount') }}&nbsp;</h3></th>
                            @if ($result_p['cancelled_amount'])
                                <td><h3 style="line-height: 60px;">: रु {{ $result_p['cancelled_amount'] }}</h3></td>
                            @else
                                <td><h3 style="line-height: 60px;">: रु 0.00</h3></td>
                            @endif
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

