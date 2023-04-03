@extends('layouts.front')

@section('title')
    <title>Vehicle Log</title>
@stop

@section('css')
    @parent
    <link rel="stylesheet" href="{!! asset('css/back.css') !!}">
@stop

@section('style')
    <style>
    </style>
@stop

@section('content')
    <div class="container">

    {{-- header ----------------------------------------------------------------------------------------}}

        <nav class="navbar navbar-expand-lg navbar-inverse fixed-top">
            <span class="navbar-brand">Vehicle Log - <small>{{ Carbon\Carbon::parse($date)->format('l j M Y') }}</small></span>
        </nav>

        <div class="row justify-content-around mt-5">

    {{-- message ----------------------------------------------------------------------------------------}}

            @if ( count($summary) == 0 )
                <div class="col-12 text-center">
                    <h5>You have no trips on {{ Carbon\Carbon::parse($date)->format('l j M Y') }}</h5>
                </div>
            @else

    {{-- mileage ----------------------------------------------------------------------------------------}}

                <div class="col-lg-6 col-xl-4 text-center mb-5">
                    <h5>Welcome {{ $attendant }}.</h5>
                    <p>Your Vehicle: {{ $vehicle->id == 102 ? 'Innova' : 'Home Heroes '.$vehicle->model }}</p>
                </div>

    {{-- times ----------------------------------------------------------------------------------------}}

                <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <h5 class="text-center mb-4">Trip Sheet Summary:</h5>
                    <span>Passengers:</span><span class="float-right">{{ $summary['passengers'] }}</span><br>
                    <span>Pickup Venues:</span><span class="float-right">{{ $summary['venues'] }}</span><br>
                    <span>First Pickup:</span><span class="float-right">{{ substr($summary['start'],0,5) }}</span><br>
                    <span>Last Drop Off:</span><span class="float-right">{{ substr($summary['end'],0,5) }}</span><br>
                    <span>Travel Distance:</span><span class="float-right">{{ $summary['km'] }} km</span>
                </div>

    {{-- tripsheet link ------------------------------------------------------------------------------}}

                <div class="col-12 mt-4 mt-md-5"></div>
                <div class="col-md-6 col-lg-4 text-center mt-4 mt-lg-5">
                    @if ( !is_null($vehicles) )
                        {!! Form::select('vehicle', ['' => 'Vehicles'] +collect($vehicles)->pluck('att','id')->all(), $vehicle->id, ['class' => "form-control custom-select mb-3", 'id' => 'myvehicle']) !!}
                    @else
                        {!! Form::hidden('vehicle', $vehicle->id, ['id' => 'myvehicle']) !!}
                    @endif
                    <button id="callts" class="btn btn-primary btn-block">Open Trip Sheet</button>
                    <br>
                </div>
            @endif
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
            {{-- call trip sheet --}}
            $('#callts').on('click', function() {
                window.location.href = '/tripsheets/tripsheet/' + $('#myvehicle').val();
            });
        });
    </script>
@stop