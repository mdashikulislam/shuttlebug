@extends('layouts/office')

@section('title')
    <title>Attendant Form</title>
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
                        @if ( !is_null($attendant) )
                            <h3>Attendant:
                                <span class="small">{{ $attendant->id }}:</span> {{ $attendant->first_name.' '.$attendant->last_name }}
                            </h3>
                        @else
                            <h3>Create a new Attendant</h3>
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

                @if ( ! is_null($attendant) )
                    {!! Form::model($attendant, ['url' => ['office/operations/attendants/store', $attendant->id], 'id' => 'capture']) !!}
                @else
                    {!! Form::open(['url' => 'office/operations/attendants/store', 'id' => 'capture']) !!}
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

                {{-- role --------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('role', 'Role', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                <label class="control control-radio mt-1">Primary
                                    {!! Form::radio('role', 'primary', null) !!}
                                    <span class="control_indicator"></span>
                                </label>
                                <label class="control control-radio ml-2 mt-1">Senior
                                    {!! Form::radio('role', 'senior', null) !!}
                                    <span class="control_indicator"></span>
                                </label>
                                <label class="control control-radio ml-2 mt-1">Other
                                    {!! Form::radio('role', '', null) !!}
                                    <span class="control_indicator"></span>
                                </label>
                                <p class="text-muted"><small>Primary = Lyn, Senior = preferred attendant</small></p>
                            </div>
                        </div>

                        <h6 class="mt-3"><strong>Working Hours</strong></h6>
                        <p class="text-muted"><small>If this attendant is available for a limited period set the earliest and latest times. Otherwise set times from 06:00 to 19:00 to make the attendant available for all trips.</small></p>

                {{-- from ----------------------------------------------------------------------------------}}

                        <div class="form-group row ml-3">
                            {!! Form::label('from', 'Pickups From', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('from', null, ['class' => "form-control time"]) !!}
                            </div>
                        </div>

                {{-- to ----------------------------------------------------------------------------------------}}

                        <div class="form-group row ml-3">
                            {!! Form::label('to', 'Pickups To', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('to', null, ['class' => "form-control time"]) !!}
                            </div>
                        </div>

                {{-- status --------------------------------------------------------------------------}}

                        <div class="form-group row mt-5">
                            {!! Form::label('status', 'Status', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                <label class="control control-radio mt-1">Active
                                    {!! Form::radio('status', 'active', is_null($attendant) ? true:null) !!}
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
                                <p class="text-muted"><small>Active = in use, Inactive = not currently in use, History = no longer an attendant.</small></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 col-xl-5 ml-xl-3">

                    </div>

                    <div class="col-md-11 col-lg-12 col-xl-10"><hr class="mb-1"></div>
                    <div class="col-xl-12"></div>

                {{-- submit ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-6 col-xl-4 mt-4 mb-5">
                        @if ( is_null($attendant) )
                            {!! Form::submit('Save Attendant', ['class' => 'btn btn-primary']) !!}
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
