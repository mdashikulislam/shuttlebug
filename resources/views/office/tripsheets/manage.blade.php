@extends('layouts.office')

@section('title')
    <title>Manage Trip Sheets</title>
@stop

@section('css')
    @parent
@stop

@section('style')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-tripsheets')

        {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 mb-4 content" id="content">
                <div class="row">
                    <div class="col-md">
                        <h3 class="page-header">Manage Trip Sheets</h3>
                    </div>

                    <div class="col-md">
                        @if ( session()->has('confirm') )
                            <div class="alert-success alert-temp">{{ session('confirm') }}</div>
                        @elseif ( session()->has('warning') )
                            <div class="alert-warning alert-temp">{{ session('warning') }}</div>
                        @elseif ( session()->has('danger') )
                            <div class="alert-danger alert-temp">{{ session('danger') }}</div>
                        @endif
                    </div>
                </div>
                <hr class="mt-2">

                <div class="row">
                    <div class="col-sm-11 col-md-8 col-lg-6 col-xl-4">

        {{-- summary trip sheet -------------------------------------------------------------------------------}}

                        <h5 class="mt-5">Trip Sheet Summary</h5>
                        <div class="ml-3">
                            {!! Form::open(['url' => 'office/operations/tripsheets/summary']) !!}
                                <div class="mt-2 w-75">
                                    {!! Form::text('date', null, ['class' => 'form-control datepicker', 'placeholder' => 'click for date', 'required']) !!}
                                </div>
                                {!! Form::submit('View Summary', ['class' => 'btn btn-primary mt-3']) !!}
                            {!! Form::close() !!}
                        </div>

        {{-- home heroes schedules ------------------------------------------------------------------------------}}

                        <h5 class="mt-5">Homeheroes Schedule & Statement</h5>
                        <a class="btn btn-primary ml-3" href="{!! url('homeheroes/') !!}">Schedule</a>
                        <a class="btn btn-primary ml-3" href="{!! url('hh/statement/') !!}">Statement</a>

        {{-- vehicle trip sheet ------------------------------------------------------------------------------}}

                        <h5 class="mt-5">Vehicle Trip Sheet</h5>
                        <a class="btn btn-primary ml-3" href="{!! url('tripsheets/') !!}">Trip Sheet</a>
                    </div>
                </div>
            </section>
        </div>
    </div>
@stop

@section('script')
    @parent
@stop


@section('jquery')
    @parent
    <script>
        $(function() {
            let element = '.datepicker';

            $(element).datepicker('destroy');
            $(element).datepicker({
                format:         'yyyy-mm-dd',
                autoclose:      true,
                weekStart:      1,
                // startDate:      '2019-01-01',
                startView:      'days',
                todayHighlight: 'true',
            });
        });
    </script>
@stop