@extends('layouts.office')

@section('title')
    <title>Trip Settings</title>
@stop

@section('css')
    @parent
    <link rel="stylesheet" href="{{ asset('css/jquery.timepicker.css') }}">
@stop

@section('style')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-tripplans')

        {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 mb-4 content" id="content">
                <div class="row">
                    <div class="col-md">
                        <h3 class="page-header">Manage Trip Settings</h3>
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
                    <div class="col-sm-11 col-md-8 col-lg-6 col-xl-6">
                        <p class="text-muted">These are the settings used by the trip planner. They can be revised here to improve or alter how the planner allocates vehicles to the bookings.</p>

            {{-- form ----------------------------------------------------------------------------------------}}

                        {!! Form::model($settings, ['url' => ['office/operations/tripplans/update', $settings->id], 'id' => 'capture']) !!}

                    {{-- fixed schedule ------------------------------------------------------------------------}}

                            {{--<h6 class="mt-4"><strong>Fixed Schedule</strong></h6>--}}
                            {{--<p class="text-muted small ml-3">This only allows bookings at selected venues at fixed times between noon and 4pm. The venues and times can be set below under <u>Scheduled Venues</u>. Set to false to allow any venue at any time.</p>--}}
                            {{--<div class="row">--}}
                                {{--<div class="col-4 ml-3">--}}
                                    {{--Default: <em class="ml-2">true</em>--}}
                                {{--</div>--}}
                                {{--<div class="col-4">--}}
                                    {{--{!! Form::select('fixed_schedule',[0 => 'false', 1 => 'true'], null, ['class' => "form-control form-control-sm custom-select"]) !!}--}}
                                {{--</div>--}}
                                {{--<div class="col-4"></div>--}}
                            {{--</div>--}}

                    {{-- fixed venues -----------------------------------------------------------------------}}

                        {{--<h6 class="mt-4"><strong>Scheduled Venues</strong> <span class="text-info small">(for webmaster use to optimise planning)</span></h6>--}}
                        {{--<p class="text-muted small ml-3">This only applies if <u>Fixed Schedules</u> is set to <u>true</u>.--}}
                            {{--<br>Allocate fixed times to specific venues.--}}
                            {{--<br>To pre-allocated a vehicle to a venue/time, add a vehicle else leave blank.--}}
                            {{--<br>Bookings will only be accepted at these venues and times (between noon and 4pm).--}}
                            {{--<br><strong>Llandudno & International : </strong> setting time to half-hour (eg. 14:30) will accept bookings between 14:15 - 14:30.--}}
                        {{--</p>--}}
                        {{--<div class="row">--}}
                            {{--<div class="col-3 text-center"><em>vehicle</em></div>--}}
                            {{--<div class="col-5 text-center"><em>venue</em></div>--}}
                            {{--<div class="col-2"><em>time</em></div>--}}
                            {{--<div class="col-2"><em>limit</em></div>--}}
                        {{--</div>--}}
                        {{--<div class="row">--}}
                            {{--<div class="col-11 ml-3">--}}
                                {{--@for ( $n = 0; $n <= 15; $n++ )--}}
                                    {{--<div class="row mb-1">--}}
                                        {{--<div class="col-3">--}}
                                            {{--{!! Form::select("fixed_venues[$n][vehicle]", ['' => ' '] +$vehicles, null, ['class' => "form-control form-control-sm custom-select"]) !!}--}}
                                        {{--</div>--}}
                                        {{--<div class="col-5">--}}
                                            {{--{!! Form::select("fixed_venues[$n][loc]", ['' => ' '] +$schools, null, ['class' => "form-control form-control-sm custom-select"]) !!}--}}
                                        {{--</div>--}}
                                        {{--<div class="col-2">--}}
                                            {{--{!! Form::text("fixed_venues[$n][time]", null, ['class' => "form-control form-control-sm time"]) !!}--}}
                                        {{--</div>--}}
                                        {{--<div class="col-2">--}}
                                            {{--{!! Form::text("fixed_venues[$n][limit]", null, ['class' => "form-control form-control-sm"]) !!}--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--@endfor--}}
                            {{--</div>--}}
                        {{--</div>--}}

                    {{-- bus route ---------------------------------------------------------------------------------}}

                        {{--<h6 class="mt-4"><strong>Bus Route</strong></h6>--}}
                        {{--<p class="text-muted small ml-3">This only applies if <u>Fixed Schedules</u> is set to <u>false</u>.<br>A bus route will interrupt vehicles multiple times to make them available for a pickup, whereas a normal route will interrupt a vehicle only once.</p>--}}
                        {{--<div class="row">--}}
                            {{--<div class="col-4 ml-3">--}}
                                {{--Default: <em class="ml-2">true</em>--}}
                            {{--</div>--}}
                            {{--<div class="col-4">--}}
                                {{--{!! Form::select('bus_route',[0 => 'false', 1 => 'true'], null, ['class' => "form-control form-control-sm custom-select"]) !!}--}}
                            {{--</div>--}}
                            {{--<div class="col-4"></div>--}}
                        {{--</div>--}}

                    {{-- preferred morning vehicle ------------------------------------------------------------}}

                            <h6 class="mt-4"><strong>Preferred Morning Vehicle</strong></h6>
                            <p class="text-muted small ml-3">Select the vehicle to use for early morning trips.<br>
                                Multiple vehicles will be used if there are lifts in more than one zone.</p>
                            <div class="row">
                                <div class="col-4 ml-3">
                                    Default: <em class="ml-2">Innova</em>
                                </div>
                                <div class="col-4">
                                    {!! Form::select("pref_am_vehicle", ['' => ' ', 'sb' => 'Innova', 'hh' => 'Home Heroes'], $settings->pref_am_vehicle, ['class' => "form-control form-control-sm custom-select"]) !!}
                                </div>
                                <div class="col-4"></div>
                            </div>

                    {{-- preferred late vehicle ------------------------------------------------------------}}

                        <h6 class="mt-4"><strong>Preferred Late Vehicle</strong></h6>
                        <p class="text-muted small ml-3">Select the vehicle to use for lifts after 5:30pm.<br>
                            Multiple vehicles will be used if there are lifts in more than one zone.</p>
                        <div class="row">
                            <div class="col-4 ml-3">
                                Default: <em class="ml-2">Homeheroes</em>
                            </div>
                            <div class="col-4">
                                {!! Form::select("pref_pm_vehicle", ['' => ' ', 'sb' => 'Innova', 'hh' => 'Home Heroes'], $settings->pref_pm_vehicle, ['class' => "form-control form-control-sm custom-select"]) !!}
                            </div>
                            <div class="col-4"></div>
                        </div>

                    {{-- passenger limit ---------------------------------------------------------------------------}}

                            <h6 class="mt-4"><strong>6 Seater Passenger Limit</strong></h6>
                            <p class="text-muted small ml-3">
                                6 Seater vehicles can be limited to carry passengers for up to 4 destinations ( 5 or 6 passengers depending on the number of siblings aboard ) or to carry maximum passengers ( up to 6 destinations ).<br>
                                Maximum passengers means fewer vehicles will be required at the pickup but the trip duration will be longer (up to 1 hour).<br>
                                Limiting the vehicle to 4 destinations might require two vehicles at the pickup but the trip duration will be shorter (typically 40 - 45min).
                            <div class="row">
                                <div class="col-4 ml-3">
                                    Default: <em class="ml-2">4</em>
                                </div>
                                <div class="col-4">
                                    {!! Form::select("passenger_limit", ['4' => '4', '6' => '6'], null, ['class' => "form-control form-control-sm custom-select"]) !!}
                                </div>
                                <div class="col-3"><small>destinations</small></div>
                            </div>

                    {{-- vehicle waiting ---------------------------------------------------------------------------}}

                            <h6 class="mt-4"><strong>Vehicle Waiting</strong></h6>
                            <p class="text-muted small ml-3">The max time (minutes) a vehicle will wait at a venue pending another pickup at the same venue.</p>
                            <div class="row">
                                <div class="col-4 ml-3">
                                    Default: <em class="ml-2">15</em>
                                </div>
                                <div class="col-4">
                                    {!! Form::text('vehicle_wait', $settings->vehicle_wait / 60, ['class' => "form-control form-control-sm"]) !!}
                                </div>
                                <div class="col-3"><small>minutes</small></div>
                            </div>

                    {{-- home delay ---------------------------------------------------------------------------}}

                            <h6 class="mt-4"><strong>Home Delay</strong></h6>
                            <p class="text-muted small ml-3">The delay (minutes) when picking up or dropping off passengers at home or xmural. (ie. the time taken to 'hand-over' or load the passenger.)</p>
                            <div class="row">
                                <div class="col-4 ml-3">
                                    Default: <em class="ml-2">3</em>
                                </div>
                                <div class="col-4">
                                    {!! Form::text('home_delay', $settings->home_delay / 60, ['class' => "form-control form-control-sm"]) !!}
                                </div>
                                <div class="col-3"><small>minutes</small></div>
                            </div>

                    {{-- school dropoff delay -----------------------------------------------------------------------}}

                            <h6 class="mt-4"><strong>School Drop off Delay</strong></h6>
                            <p class="text-muted small ml-3">The delay (minutes) when dropping off passengers at a school. (ie. the time taken to 'hand-over' the passenger/s.)</p>
                            <div class="row">
                                <div class="col-4 ml-3">
                                    Default: <em class="ml-2">5</em>
                                </div>
                                <div class="col-4">
                                    {!! Form::text('school_dodelay', $settings->school_dodelay / 60, ['class' => "form-control form-control-sm"]) !!}
                                </div>
                                <div class="col-3"><small>minutes</small></div>
                            </div>

                    {{-- school pickup delay -----------------------------------------------------------------------}}

                            <h6 class="mt-4"><strong>School Pickup Delay</strong></h6>
                            <p class="text-muted small ml-3">The delay (minutes) when picking up passengers at school. (ie. the time taken to load the passengers.) This will vary according to the number of passengers being picked up.</p>
                            <div class="row">
                                <div class="col-4 ml-3">
                                    Defaults:<br>
                                    <span class="ml-5">1 pass: </span><em class="ml-2">3</em><br>
                                    <span class="ml-5">2 pass: </span><em class="ml-2">4</em><br>
                                    <span class="ml-5">3 pass: </span><em class="ml-2">5</em><br>
                                    <span class="ml-5">4 pass: </span><em class="ml-2">6</em><br>
                                    <span class="ml-5">5 pass: </span><em class="ml-2">7</em><br>
                                    <span class="ml-5">6 pass: </span><em class="ml-2">8</em>
                                </div>
                                <div class="col-4">
                                    <div class="row">
                                        @foreach ( $settings->school_pudelay as $key => $item )
                                            @if ($loop->first)
                                                {!! Form::hidden("school_pudelay[$key]", $item / 60) !!}
                                            @else
                                                <div class="col-4 mt-1">
                                                    <span class="">{{ $key }}&nbsp;pass:</span>
                                                </div>
                                                <div class="col-6 mb-1">
                                                    {!! Form::text("school_pudelay[$key]", $item / 60, ['class' => "form-control form-control-sm ml-2"]) !!}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-3"><small>minutes</small></div>
                            </div>

                    {{-- buffer -----------------------------------------------------------------------}}

                            <h6 class="mt-4"><strong>On-Time</strong></h6>
                            <p class="text-muted small ml-3">The max lateness (minutes) for arrival at a venue to be regarded as "on-time". An on-time vehicle is preferred to other vehicles. If no vehicle is on-time a vehicle will be interrupted to get to this pickup on time.</p>
                            <div class="row">
                                <div class="col-4 ml-3">
                                    Default: <em class="ml-2">3</em>
                                </div>
                                <div class="col-4">
                                    {!! Form::text('buffer', $settings->buffer / 60, ['class' => "form-control form-control-sm"]) !!}
                                </div>
                                <div class="col-3"><small>minutes</small></div>
                            </div>

                    {{-- pre allocations -----------------------------------------------------------------------}}

                            {{--<h6 class="mt-4"><strong>Pre-Allocations</strong></h6>--}}
                            {{--<p class="text-muted small ml-3">This only applies if <u>Fixed Schedules</u> is set to <u>false</u>.<br>Vehicles dedicated to specific pickup venues can be pre-allocated and will be used if the vehicle is available and has sufficient seats. Use sparingly as this reduces the efficiency of route planning.</p>--}}
                            {{--<div class="d-flex justify-content-between mx-4">--}}
                                {{--<em class="pl-4">vehicle</em>--}}
                                {{--<em>venue</em>--}}
                                {{--<em class="pr-5">time</em>--}}
                            {{--</div>--}}
                            {{--<div class="row">--}}
                                {{--<div class="col-11 ml-3">--}}
                                    {{--@for ( $n = 0; $n <= 6; $n++ )--}}
                                        {{--<div class="row mb-1">--}}
                                            {{--<div class="col-3">--}}
                                                {{--{!! Form::select("pre_allocate[$n][vehicle]", ['' => ' '] +$vehicles, null, ['class' => "form-control form-control-sm custom-select"]) !!}--}}
                                            {{--</div>--}}
                                            {{--<div class="col-6">--}}
                                                {{--{!! Form::select("pre_allocate[$n][loc]", ['' => ' '] +$schools, null, ['class' => "form-control form-control-sm custom-select"]) !!}--}}
                                            {{--</div>--}}
                                            {{--<div class="col-3">--}}
                                                {{--{!! Form::text("pre_allocate[$n][time]", null, ['class' => "form-control form-control-sm time"]) !!}--}}
                                            {{--</div>--}}
                                        {{--</div>--}}
                                    {{--@endfor--}}
                                {{--</div>--}}
                            {{--</div>--}}

                    {{-- trip times -----------------------------------------------------------------------}}

                        <h6 class="mt-4"><strong>Trip Times</strong></h6>
                        <p class="text-muted small ml-3">The time (minutes) for a trip to drop off passengers. This is used when necessary to estimate a trip time (when actual time is not available). It is updated from real time planning and should not be modified here.</p>
                        <div class="row">
                            <div class="col-4 ml-3">
                                Defaults:<br>
                                <span class="ml-5">1 destination: </span><em class="ml-2">15</em><br>
                                <span class="ml-5">2 destinations: </span><em class="ml-2">25</em><br>
                                <span class="ml-5">3 destinations: </span><em class="ml-2">35</em><br>
                                <span class="ml-5">4 destinations: </span><em class="ml-2">45</em><br>
                                <span class="ml-5">5 destinations: </span><em class="ml-2">50</em><br>
                                <span class="ml-5">6 destinations: </span><em class="ml-2">55</em><br>
                            </div>
                            <div class="col-4">
                                <div class="row">
                                    @foreach ( $settings->trip_times as $key => $item )
                                        @if ($loop->first)
                                            {!! Form::hidden("trip_times[$key]", $item / 60) !!}
                                        @else
                                            <div class="col-4 mt-1">
                                                <span class="">{{ $key }}&nbsp;dest:</span>
                                            </div>
                                            <div class="col-6 mb-1">
                                                {!! Form::text("trip_times[$key]", $item / 60, ['class' => "form-control form-control-sm ml-2"]) !!}
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-3"><small>minutes</small></div>
                        </div>

                            <hr class="">
                            <div>
                                {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
                            </div>

                        {!! Form::close() !!}
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
