@extends('layouts.office')

@section('title')
    <title>Manage Trip Planning</title>
@stop

@section('css')
    @parent
    <link rel="stylesheet" href="{{ asset('css/jquery.timepicker.css') }}">
@stop

@section('style')
    @parent
    <style>
        .grey { background: #f2f2f2; }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-tripplans')

        {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 mb-4 content" id="content">
                <div class="row">
                    <div class="col-md">
                        <h3 class="page-header">Manage Trip Planning</h3>
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

        {{-- run planner -------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-sm-11 col-md-8 col-lg-6 col-xl-4">
                        <h5 class="mt-3">Plan a Day's Trips</h5>
                        <p class="text-muted ml-3">Always run Mornings before Rest of Day.</p>
                        <div class="ml-3">
                            {!! Form::open(['url' => 'office/operations/tripplans/plan']) !!}
                                <div class="mt-2">
                                    {!! Form::text('date', null, ['class' => 'form-control datepicker', 'placeholder' => 'select the date', 'required']) !!}
                                </div>
                                <div class="mt-2">
                                    <label class="control control-radio mt-1">Mornings to School
                                        {!! Form::radio("period", 'am', true) !!}
                                        <span class="control_indicator"></span>
                                    </label>
                                    <label class="control control-radio mt-1">Rest of Day
                                        {!! Form::radio("period", 'day', null) !!}
                                        <span class="control_indicator"></span>
                                    </label>
                                </div>

                                <h5 class="mt-4">Available Vehicles & Attendants</h5>
                                <p class="text-muted"><small>The planner will use whatever vehicles are needed to complete the trips for the day. It assumes the following vehicles & attendants are available.<br><strong>Remove any that are not available for this day.</strong></small></p>

                                <div class="grey mt-3 p-2">
                                    @foreach ( $vehicles as $vehicle )
                                        <div class="d-flex justify-content-between">
                                            <span><strong>{{ $vehicle->model }}:</strong></span>
                                            <label class="control control-checkbox mt-1 ml-3 mb-0">Available
                                                @if ( $vehicle->status == 'active' )
                                                    {!! Form::checkbox("vehicle[$vehicle->id][available]", 'available', true) !!}
                                                @else
                                                    {!! Form::checkbox("vehicle[$vehicle->id][available]", 'available', false) !!}
                                                @endif
                                                <span class="control_indicator"></span>
                                            </label>
                                        </div>
                                    @endforeach
                                    <br>
                                    @foreach ( $attendants as $attendant )
                                        <div class="d-flex justify-content-between">
                                            <span><strong>{{ $attendant->first_name }}:</strong></span>
                                            <label class="control control-checkbox mt-1 ml-3 mb-0">Available
                                                @if ( $attendant->status == 'active' )
                                                    {!! Form::checkbox("attendant[$attendant->id][available]", 'available', true) !!}
                                                @else
                                                    {!! Form::checkbox("attendant[$attendant->id][available]", 'available', false) !!}
                                                @endif
                                                <span class="control_indicator"></span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <hr>
                                {!! Form::submit('Plan Trips', ['class' => 'btn btn-primary mt-1']) !!}
                            {!! Form::close() !!}
                        </div>
                    </div>

                    <div class="col-sm-11 col-md-8 col-lg-5 col-xl-4 offset-lg-1">

        {{-- planning settings -------------------------------------------------------------------------------------}}

                        <h5 class="mt-5">Default Trip Planning Settings</h5>
                        <p class="text-muted ml-3 mb-2"><small>The default settings for trip planning are applied to every trip plan. These setting can be modified and will remain in force until changed again.</small></p>
                        <a class="btn btn-outline-dark ml-3" href="{!! url('office/operations/tripplans/settings') !!}">Review Settings</a>

        {{-- notes ----------------------------------------------------------------------------------------}}

                        <h5 class="mt-5">Notes on Trip Plans</h5>
                        <ul class="text-muted small">
                            <li>The trip plan presented is always up to date with the latest bookings.</li>
                            <li>The morning plan will exclude vehicles not available before 9am.</li>
                            <li>The morning plan will use the Preferred Hout Bay vehicle (see Default Settings) for trips inside Hout Bay unless this vehicle is excluded from Available vehicles.</li>
                            <li>Attendants will only be allocated to trips that fall within their working hours (can be changed in Operations->Attendants).</li>
                        </ul>

                    </div>
                </div>
            </section>
        </div>
    </div>
@stop

@section('script')
    @parent
    <script src="{!! asset('js/jquery.timepicker.min.js') !!}"></script>
@stop


@section('jquery')
    @parent
    <script>
        $(function() {
            $('.time').timepicker({
                'timeFormat': 'H:i',
                'minTime': '06:00',
                'maxTime': '20:00',
                'step': '15'
            });
        });
    </script>
@stop
