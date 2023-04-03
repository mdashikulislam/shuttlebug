@extends('layouts.front')

@section('title')
    <title>Driver Login</title>
@stop

@section('css')
    @parent
    <link rel="stylesheet" href="{!! asset('css/front.css') !!}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-datepicker3.standalone.min.css') }}">
@stop

@section('content')
    <div class="container">
        <div class="row justify-content-center align-items-center" style="height:100vh">
            <div class="col-sm-8 col-md-6 col-lg-5 col-xl-4">
                <div class="text-center">
                    <h1>Shuttle Bug</h1>
                    <h1>Vehicle Login</h1>
                    <hr>
                    @if ( session()->has('danger') )
                        <div class="alert alert-danger alert-temp">{{ session('danger') }}</div>
                    @endif
                    <div class="pt-3">
                        {!! Form::open(['url' => 'tripsheets/triplog', 'id' => 'loginform']) !!}
                            <div class="form-group">
                                {!! Form::label('code', 'Your Code') !!}
                                {!! Form::text('code', null, ['class' => "form-control", 'autofocus', 'id' => 'code', 'required']) !!}
                            </div>

                            {{--<div class="form-group">--}}
                                {{--{!! Form::label('reg', 'Your Vehicle') !!}--}}
                                {{--{!! Form::select('reg', ['' => 'Select'] +$vehicles, null, ['class' => "form-control custom-select", 'required']) !!}--}}
                            {{--</div>--}}

                            <div class="form-group d-none d-xl-block">
                                {!! Form::label('date', 'Trip Sheet Date') !!}
                                {!! Form::text('date', null, ['class' => "form-control datepicker", 'id' => 'date']) !!}
                            </div>

                            <div class="form-group mt-5">
                                {!! Form::submit('Login', ['class' => 'btn btn-primary btn-block btn-block', 'id' => 'loginbtn']) !!}
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
@parent
<script src="{!! asset('js/bootstrap-datepicker.min.js') !!}"></script>
@stop


@section('jquery')
@parent
    <script>
        let holidays = @php echo json_encode(Cache::get('holidays'.now()->year)); @endphp

        $(function() {
            // standard datepicker
            $('.datepicker').datepicker({
                format:         'yyyy-mm-dd',
                autoclose:      true,
                weekStart:      1,
                todayHighlight: 'true',
                beforeShowDay: function (date) {
                    let calendar_date = (date.getFullYear() + '-0' + (date.getMonth() + 1) + '-0' + date.getDate()).replace(/-0(\d\d)/g, '-$1');
                    let search_index = $.inArray(calendar_date, holidays);
                    if ( search_index !== -1 || $.inArray(date.getDay(), [0,6]) !== -1 ) {
                        return {classes: 'mask-cal-dates'};
                    }
                }
            });

            {{-- submit login --}}
            // $('#loginbtn').on('click', function() {
{{--                $.post("{!! url('/tripsheets/triplog') !!}", $('#loginform').serialize());--}}
                // window.location.href = '/tripsheets/triplog/' + $('#code').val() + '/' + $('#date').val();
            // });
        });
    </script>
@stop