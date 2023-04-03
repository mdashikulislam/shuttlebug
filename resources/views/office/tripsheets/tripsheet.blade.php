@extends('layouts.front')

@section('title')
    <title>Trip Sheet</title>
@stop

@section('css')
    @parent
    <link rel="stylesheet" href="{!! asset('css/back.css') !!}">
    <link rel="stylesheet" href="{!! asset('css/signature-pad.css') !!}">
@stop

@section('style')
    <style>
        .container-fluid { padding: 0; }
        .card-header, .profile, .signature { cursor: pointer; }
        .btn-width { width: 33%; }
        h5.navbar-text { margin: 0; }
        .dropdown-menu { font-size: 13px; box-shadow: -7px 7px 6px -4px #777; }
        #map-canvas { height: calc(100vh - 50px); width: 100vw; }

        @media (max-width: 767px) {
            .btn-width { width: 100%; }
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">

    {{-- navbar ----------------------------------------------------------------------------------------}}

        <nav class="navbar navbar-expand navbar-inverse fixed-top">
            <ul class="navbar-nav">
                @if ( $vehicle->id == 102 )
                    <li class="nav-item"><a id="delay" class="btn btn-info btn-sm" href="#">Delay Sms</a></li>
                @endif
                <li class="nav-item"><a class="back btn btn-info btn-sm d-none" href="#"></a></li>
            </ul>
            <ul class="navbar-nav mx-auto">
                <li><h5 class="navbar-text text-white">{{ $vehicle->model }}: Tripsheet - <small>{{ Carbon\Carbon::parse($date)->format('D j F') }}</small></h5></li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a id="ts_help" class="dropdown-toggle btn btn-info btn-sm" href="#" data-toggle="dropdown">Help</a>
                    <div class="dropdown-menu dropdown-menu-right bg-light px-3" style="width:250px">
                        @include('office.tripsheets.help_ts')
                    </div>
                </li>
            </ul>
        </nav>

        <div id="pages" class="carousel slide" data-ride="false" data-interval="false" data-pause="true">
            <div class="carousel-inner">

            {{-- tripsheet ----------------------------------------------------------------------------------------}}

                <div class="container carousel-item active">
                    <div class="row mt-5">
                        <div class="col-12 accordion" id="accordion">
                            @foreach ( $trips->where('type', 'pickup')->all() as $trip )
                                @php $dropoffs = $trips->where('type', 'dropoff')->where('route', $trip->route)->all(); @endphp

                                <div class="card mb-2">

                        {{-- header --------------------------------------------------------------------------------}}

                                    <div class="card-header" id="pickup{{ $trip->route }}" data-toggle="collapse" data-target="#collapse{{ $trip->route }}">
                                        <i class="text-muted fa fa-sort"></i>
                                        <span class="ml-3">{{ substr($trip->putime,0,5) }}</span>
                                        <span class="ml-3">{{ $trip->venue }}</span>
                                        <span class="pull-right d-none d-sm-inline">
                                            <small>
                                                pickup: {{ count(explode(',',$trip->passengers)) }}
                                                @if ( count($dropoffs) > 0 )
                                                    <span class="ml-3">dropoff: {{ count($dropoffs) }}</span>
                                                @endif
                                            </small>
                                        </span>
                                    </div>

                                    <div id="collapse{{ $trip->route }}" class="collapse" aria-labelledby="pickup{{ $trip->route }}" data-parent="#accordion">
                                        <div class="card-body mb-5">

                        {{-- pickups ------------------------------------------------------------------------------}}

                                            <ul class="list-group mb-1">
                                                <li class="list-group-item bg-dark text-white mb-1">Pickup
                                                    <span class="ml-3">@ {{ $trip->address }}</span>
                                                </li>

                                        {{-- passengers ------------------------------------------------------------}}

                                                @foreach ( explode(',',$trip->passengers) as $passenger )
                                                    <li class="list-group-item profile mb-1" data-name="{{ $passenger }}" data-due="{{ $trip->putime }}">
                                                        <span class="text-danger">{{ substr($trip->putime,0,5) }}</span>
                                                        <span class="ml-3">{{ $passenger }}</span>
                                                        <span class="pull-right"><i class="text-info  fa fa-user-circle"></i></span>
                                                    </li>
                                                @endforeach
                                            </ul>

                                        {{-- buttons --------------------------------------------------------------}}

                                            @if ( count($dropoffs) > 0 )
                                                <div class="clearfix">
                                                    @if ( in_array($trip->depart, $departed) )
                                                        <button class="btn btn-success btn-width pull-right depart mb-2" disabled>
                                                            Departure Confirmed
                                                        </button>
                                                    @else
                                                        <button id="depart{{ $trip->route }}" class="btn btn-info btn-width pull-right depart mb-2" data-due="{{ $trip->depart }}" data-info="{{ substr($trip->putime,0,5).' @ '.$trip->venue }}">
                                                            Confirm Departure
                                                        </button>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="clearfix">
                                                    <button class="mapcaller btn btn-info btn-width pull-right mb-2" data-route="{{ $trip->route }}">
                                                        View Map Location
                                                    </button>
                                                </div>

                                                @php $next_pickup = $trips->where('type', 'pickup')->where('route', $trip->route+1)->first(); @endphp
                                                @if ( !is_null($next_pickup) && $next_pickup->venue == $trip->venue )
                                                    <strong>Wait here for more passengers @ {{ substr($next_pickup->putime,0,5) }}.</strong>
                                                @elseif ( !is_null($next_pickup) )
                                                    <strong>Pickup more passengers at {{ $next_pickup->venue }} before dropping off passengers.</strong>
                                                @endif
                                            @endif

                        {{-- dropoffs ------------------------------------------------------------------------------}}

                                            @if ( count($dropoffs) > 0 )
                                                <ul class="list-group mb-1">
                                                    <li class="list-group-item bg-dark text-white mb-1">Drop Off</li>

                                        {{-- passengers ------------------------------------------------------------}}

                                                    @foreach ( $dropoffs as $dropoff )
                                                        <li class="list-group-item signature mb-1" data-name="{{ $dropoff->passengers }}" data-due="{{ $dropoff->dotime }}" data-trip="{{ $dropoff->id }}" data-putime="{{ $trip->putime }}">
                                                            <div class="row">
                                                                <div class="col-sm-5">
                                                                    <span>{{ substr($dropoff->dotime,0,5) }}</span>
                                                                    <span class="ml-3">{{ $dropoff->passengers }}</span>
                                                                    <span class="pull-right d-sm-none"><i class="text-muted fa fa-pencil-square-o"></i></span>
                                                                </div>
                                                                <div class="col-sm-7">
                                                                    {{ $dropoff->venue > '' ? $dropoff->venue.': ' : '' }} {{ $dropoff->address }}
                                                                    <span class="pull-right d-none d-sm-inline"><i class="text-info fa fa-pencil-square-o"></i></span>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>

                                        {{-- buttons ----------------------------------------------------------------}}

                                                <div class="clearfix">
                                                    <button class="mapcaller btn btn-info btn-width pull-right mb-2" data-route="{{ $trip->route }}">
                                                        View Route Map
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            {{-- profile ----------------------------------------------------------------------------------------}}

                <div id="profile" class="container carousel-item">
                    {{-- profile page --}}
                </div>

            {{-- signature ----------------------------------------------------------------------------------------}}

                <div id="signature" class="container carousel-item">
                    {{-- signature page --}}
                </div>

            {{-- map ----------------------------------------------------------------------------------------}}

                <div id="map" class="carousel-item">
                    <div id="map-canvas">

                    </div>
                </div>

            </div>
        </div>
        @if ( count($trips) == 0 )
            <div class="alert alert-info text-center">There are no trips today</div>
        @endif
    </div>
@stop

@section('script')
@parent
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAX1xKuub_cJqm_icVEDQ_iqe1iESyVXNY"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
@stop


@section('jquery')
@parent
    <script>
        let map;
        let assets = "{!! asset('/images/') !!}";

        $(function() {
            {{-- highlght selected header --}}
            $('.card-header').on('click', function() {
                $(this).toggleClass('bg-secondary text-white');
                let id = $(this).attr('id');
                $('.card-header').not('#' + id).removeClass('bg-secondary text-white');
            });

            {{-- load profile page --}}
            $('.profile').on('click', function() {
                let time = $(this).data('due');
                let name = $(this).data('name');

                $('#profile').load('/tripsheets/profile/' + encodeURIComponent(name) + '/' + time, function () {
                    {{-- update navbar and links and switch to profile --}}
                    $('.navbar-text').html('Passenger Profile:');
                    $('a.back').html('<i class="fa fa-caret-left"></i> TripSheet').removeClass('d-none');
                    $('#delay').addClass('d-none');
                    $('.help').addClass('d-none');
                    $('#help_profile').removeClass('d-none');
                    $('.carousel').carousel(1);
                });
            });

            {{-- load signature page --}}
            $('.signature').on('click', function() {
                let time = $(this).data('due');
                let putime = $(this).data('putime');
                let name = $(this).data('name');
                let trip = $(this).data('trip');

                $('#signature').load('/tripsheets/signature/' + encodeURIComponent(name) + '/' + time, function () {
                    {{-- update navbar and hidden trip fields and switch to signature --}}
                    $('.navbar-text').html('Confirm Drop Off');
                    $('a.back').html('<i class="fa fa-caret-left"></i> TripSheet').removeClass('d-none');
                    $('#delay').addClass('d-none');
                    $('#tripid').val(trip);
                    $('#trippu').val(putime);
                    $('.help').addClass('d-none');
                    $('#help_sms').removeClass('d-none');
                    $('.carousel').carousel(2);

                    {{-- signture pad --}}
                    let canvas = document.querySelector("canvas");
                    let signaturePad = new SignaturePad(canvas, {
                        backgroundColor: "rgb(255,255,255)",
                        penColor: "rgb(38, 99, 197)"
                    });

                    function resizeCanvas() {
                        let ratio =  Math.max(window.devicePixelRatio || 1, 1);
                        canvas.width = canvas.offsetWidth * ratio;
                        canvas.height = canvas.offsetHeight * ratio;
                        canvas.getContext("2d").scale(ratio, ratio);
                        signaturePad.clear();
                    }

                    window.addEventListener("resize", resizeCanvas);
                    resizeCanvas();

                    document.getElementById('clearsig').addEventListener('click', function () {
                        signaturePad.clear();
                    });

                    document.getElementById('savesig').addEventListener('click', function () {
                        $('#siginput').val(signaturePad.toDataURL('image/jpeg'));
                    });
                });
            });

            {{-- load map data --}}
            $('.mapcaller').on('click', function() {
                let route = $(this).data('route');
                let vehicle = "{{ $vehicle->id }}";

                $.post("{!! url('tripsheets/mapdata') !!}", { route: route, vehicle: vehicle})
                    .done(function (mapdata) {
                    {{-- update navbar and links and switch to map --}}
                    $('.navbar-text').html('Rout Map: ' + mapdata[0]['venue'] + ' @ ' + mapdata[0]['time']);
                    $('a.back').html('<i class="fa fa-caret-left"></i> TripSheet').removeClass('d-none');
                    $('#delay').addClass('d-none');
                    $('.help').addClass('d-none');
                    $('#help_map').removeClass('d-none');
                    $('.carousel').carousel(3);
                    routemap(mapdata);
                });
            });

            {{-- feedback departure --}}
            $('.depart').on('click', function() {
                let element = $(this).attr('id');
                let feedback = {
                    'departure' : true,
                    'vehicle'   : "{{ $vehicle->id }}",
                    'due'       : $(this).data('due'),
                    'info'      : $(this).data('info'),
                };

                $.post("{!! url('tripsheets/feedback') !!}", feedback)
                .done(function (data) {
                    $('#' + element).toggleClass('btn-info btn-success').html(data).prop('disabled', true);
                });
            });

            {{-- feedback no show --}}
            $(document).on('click', '.noshow', function() {
                let element = $(this).attr('id');
                let feedback = {
                    'noshow'    : true,
                    'passenger' : $(this).data('passenger'),
                    'vehicle'   : "{{ $vehicle->id }}",
                    'due'       : $(this).data('due'),
                    'info'      : 'noshow',
                };

                $.post("{!! url('tripsheets/feedback') !!}", feedback)
                    .done(function (data) {
                        $('#' + element).toggleClass('btn-info btn-success').html(data).prop('disabled', true);
                    });
            });

            {{-- feedback signature --}}
            $(document).on('click', '#savesig', function() {
                let feedback = {
                    'signed'    : true,
                    'passenger' : $(this).data('passenger'),
                    'tripid'    : $('#tripid').val(),
                    'putime'    : $('#trippu').val(),
                    'vehicle'   : "{{ $vehicle->id }}",
                    'due'       : $(this).data('due'),
                    'sig'       : $('#siginput').val()
                };

                $.post("{!! url('tripsheets/feedback') !!}", feedback)
                    .done(function () {
                        $('#todo_feedback').addClass('d-none');
                        $('#live_feedback').removeClass('d-none');
                    });
            });

            {{-- feedback sms --}}
            $(document).on('click', '#sendsms', function() {
                let feedback = {
                    'sms'       : true,
                    'passenger' : $(this).data('passenger'),
                    'tripid'    : $('#tripid').val(),
                    'putime'    : $('#trippu').val(),
                    'vehicle'   : "{{ $vehicle->id }}",
                    'due'       : $(this).data('due'),
                };

                $.post("{!! url('tripsheets/feedback') !!}", feedback)
                    .done(function () {
                        $('#todo_feedback').addClass('d-none');
                        $('#live_feedback').removeClass('d-none');
                    });
            });

            {{-- send delay sms --}}
            $(document).on('click', '#delay', function() {
                let feedback = {
                    'delay': true
                };
                $.post("{!! url('tripsheets/feedback') !!}", feedback)
                    .done(function () {
                        $('#delay').html('Delay Notified');
                    });
            });

            {{-- back to tripsheet page --}}
            $(document).on('click', '.back', function() {
                $('.navbar-text').html("{!! $vehicle->model !!}: Tripsheet - <small>{!! Carbon\Carbon::parse($date)->format('d F') !!}</small>");
                $('a.back').html('').addClass('d-none');
                $('#delay').removeClass('d-none');
                $('.help').addClass('d-none');
                $('#help_ts').removeClass('d-none');
                $('.carousel').carousel(0);
            });

        });
    </script>
@stop