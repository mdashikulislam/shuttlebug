@extends('layouts/office')

@section('title')
    <title>Driver Form</title>
@stop

@section('css')
    @parent
    <link rel="stylesheet" href="{{ asset('css/jquery.timepicker.css') }}">
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-vehicles')

{{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

{{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        @if ( !is_null($driver) )
                            <h3>
                                <span class="small">{{ $driver->id }}:</span> {{ $driver->first_name.' '.$driver->last_name }}
                            </h3>
                        @else
                            <h3>Create a new Driver</h3>
                        @endif
                    </div>

{{-- messages ----------------------------------------------------------------------------------------}}

                    <div class="col-lg">
                        @if ( session()->has('confirm') )
                            <div class="alert-success alert-temp">{{ session('confirm') }}</div>
                        @elseif ( session()->has('warning') )
                            <div class="alert-warning alert-temp">{{ session('warning') }}</div>
                        @elseif ( session()->has('danger') )
                            <div class="alert-danger alert-temp">{{ session('danger') }}</div>
                        @elseif ( count($errors) > 0 )
                            <div class="alert-danger alert-temp">Some required data is missing.</div>
                        @endif
                    </div>
                </div>
                <hr class="mt-2 mb-5">

{{-- form ----------------------------------------------------------------------------------------}}

                @if ( ! is_null($driver) )
                    {!! Form::model($driver, ['url' => ['office/operations/drivers/store', $driver->id], 'id' => 'capture']) !!}
                @else
                    {!! Form::open(['url' => 'office/operations/drivers/store', 'id' => 'capture']) !!}
                @endif
                <div class="row">
                    <div class="col-md-11 col-lg-6 col-xl-5">

                {{-- first name ---------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('first_name', 'First Name', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('first_name', null, ['class' => "form-control", 'required']) !!}
                            </div>
                        </div>

                {{-- last name ----------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('last_name', 'Last Name', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('last_name', null, ['class' => "form-control"]) !!}
                            </div>
                        </div>

                        <h6 class="mt-3 mt-xl-0"><strong>Restricted Availability</strong></h6>
                        <p class="text-muted"><small>If this driver is available for a limited period set the earliest and latest pickup times. Otherwise set times to 06:00 and 18:00 to make the driver available for all pickups.</small></p>

                {{-- from ----------------------------------------------------------------------------------}}

                        <div class="form-group row ml-3">
                            {!! Form::label('from', 'Pickups From', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('from', is_null($driver) ? '06:00' : null, ['class' => "form-control time"]) !!}
                            </div>
                        </div>

                {{-- to ----------------------------------------------------------------------------------------}}

                        <div class="form-group row ml-3">
                            {!! Form::label('to', 'Pickups To', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('to', is_null($driver) ? '18:00' : null, ['class' => "form-control time"]) !!}
                            </div>
                        </div>

                {{-- vehicle ----------------------------------------------------------------------------------}}

                        <div class="form-group row mt-5">
                            {!! Form::label('vehicle', "Driver's Vehicle", ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::select('vehicle', ['' => 'Vehicles'] +$vehicles, !is_null($vehicle) ? $vehicle->id : null, ['class' => "form-control custom-select"]) !!}
                                <p class="text-muted"><small>Select the default vehicle for this driver. If the vehicle is not in the list you can update it when you add the vehicle.</small></p>
                            </div>
                        </div>

                {{-- status ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('status', 'Status', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                <label class="control control-radio mt-1">Active
                                    {!! Form::radio('status', 'active', is_null($driver) ? true:null) !!}
                                    <span class="control_indicator"></span>
                                </label>
                                <label class="control control-radio ml-2 mt-1">Inactive
                                    {!! Form::radio('status', 'inactive', null) !!}
                                    <span class="control_indicator"></span>
                                </label>
                                <label class="control control-radio ml-2 mt-1">History
                                    {!! Form::radio('status', 'history', null) !!}
                                    <span class="control_indicator"></span>
                                </label>
                                <p class="text-muted"><small>Active = in use, Inactive = not currently in use, History = no longer a driver.</small></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 col-xl-5 ml-xl-3">

                    </div>

                    <div class="col-md-11 col-lg-12 col-xl-10"><hr class="mb-1"></div>
                    <div class="col-xl-12"></div>

                {{-- submit ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-6 col-xl-4 mt-4 mb-5">
                        @if ( is_null($driver) )
                            {!! Form::submit('Save Driver', ['class' => 'btn btn-primary']) !!}
                        @else
                            {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
                        @endif
                    </div>
                </div>
                {!! Form::close() !!}
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
