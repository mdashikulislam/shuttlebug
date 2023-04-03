@extends('layouts/office')

@section('title')
    <title>Vehicle Form</title>
@stop

@section('css')
    @parent
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
                        @if ( !is_null($vehicle) )
                            <h3>
                                <span class="small">{{ $vehicle->id }}:</span> {{ $vehicle->model }}
                            </h3>
                        @else
                            <h3>Create a new Vehicle</h3>
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

                @if ( ! is_null($vehicle) )
                    {!! Form::model($vehicle, ['url' => ['office/operations/vehicles/update', $vehicle->id], 'id' => 'capture']) !!}
                @else
                    {!! Form::open(['url' => 'office/operations/vehicles/store', 'id' => 'capture']) !!}
                @endif
                <div class="row">
                    <div class="col-md-11 col-lg-6 col-xl-5">

                        <div class="form-group row">
                            {!! Form::label('model', 'Model', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('model', null, ['class' => "form-control", 'required']) !!}
                            </div>
                        </div>

                {{-- reg ----------------------------------------------------------------------------------------}}

                        {{--<div class="form-group row">--}}
                            {{--{!! Form::label('reg', 'Registration', ['class' => 'col col-form-label']) !!}--}}
                            {{--<div class="col-md-9 col-lg-8">--}}
                                {{--{!! Form::text('reg', null, ['class' => "form-control", 'required']) !!}--}}
                            {{--</div>--}}
                        {{--</div>--}}

                {{-- seats ----------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('seats', 'Passengers', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('seats', null, ['class' => "form-control", 'required']) !!}
                            </div>
                        </div>

                {{-- type ---------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('primary', 'Type', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                <label class="control control-radio mt-1">Shuttle Bug
                                    {!! Form::radio('primary', 1, null) !!}
                                    <span class="control_indicator"></span>
                                </label>
                                <label class="control control-radio ml-2 mt-1">Homeheroes
                                    {!! Form::radio('primary', 0, null) !!}
                                    <span class="control_indicator"></span>
                                </label>
                            </div>
                        </div>

                {{-- licence ----------------------------------------------------------------------------------------}}

                        {{--<div class="form-group row">--}}
                            {{--{!! Form::label('licence', 'Licence Due', ['class' => 'col col-form-label']) !!}--}}
                            {{--<div class="col-md-9 col-lg-8">--}}
                                {{--{!! Form::select('licence', cal_info(0)['months'], null, ['class' => "form-control custom-select"]) !!}--}}
                            {{--</div>--}}
                        {{--</div>--}}

                {{-- renewed ----------------------------------------------------------------------------------------}}

                        {{--<div class="form-group row">--}}
                            {{--{!! Form::label('licence', 'Licence Renewed', ['class' => 'col col-form-label']) !!}--}}
                            {{--<div class="col-md-9 col-lg-8">--}}
                                {{--{!! Form::text('renewed', null, ['class' => "form-control datepicker"]) !!}--}}
                            {{--</div>--}}
                        {{--</div>--}}

                {{-- geo ----------------------------------------------------------------------------------------}}

                        <p class="text-muted"><small>Provide the location of the vehicle at the start of the day.</small></p>

                        <div class="form-group row">
                            {!! Form::label('geo', 'Start Location', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                <div class="input-group">
                                    {!! Form::text('geo', null, ['class' => "form-control", 'required']) !!}
                                    <div class="input-group-addon btn btn-outline-dark" id="callLocationMap" role="button" title="view map">
                                        <i class="fa fa-globe fa-lg"></i>
                                    </div>
                                </div>
                                <p class="text-muted"><small>Click the map button to find the location - drag the marker to the correct position.</small></p>
                            </div>
                        </div>

                {{-- status ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('status', 'Status', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                <label class="control control-radio mt-1">Active
                                    {!! Form::radio('status', 'active', is_null($vehicle) ? true:null) !!}
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
                                <p class="text-muted"><small>Active = in use<br>
                                    Inactive = not currently in use<br>
                                    History = no longer in fleet.</small></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 col-xl-5 ml-xl-3">

                {{-- driver ----------------------------------------------------------------------------------------}}

                        {{--<h6 class="mt-3 mt-xl-0"><strong>Driver</strong></h6>--}}
                        {{--<p class="text-muted"><small>Select the default driver for this vehicle. If the driver is not in the list you can update it when you add the driver. Drivers can be changed when running the route planner.</small></p>--}}
                        {{--<div class="form-group">--}}
                            {{--<div class="col-md-9 col-lg-8">--}}
                                {{--{!! Form::select('driver_id', ['' => 'Driver'] +$drivers, null, ['class' => "form-control custom-select ml-3"]) !!}--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    </div>

                    <div class="col-md-11 col-lg-12 col-xl-10"><hr class="mb-1"></div>
                    <div class="col-xl-12"></div>

                {{-- submit ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-6 col-xl-4 mt-4 mb-5">
                        @if ( is_null($vehicle) )
                            {!! Form::submit('Save Vehicle', ['class' => 'btn btn-primary']) !!}
                        @else
                            {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
                        @endif
                    </div>
                </div>
                {!! Form::close() !!}
            </section>
        </div>
    </div>

    {{-- map modal ----------------------------------------------------------------------------------------}}

    <div class="modal fade" id="locationMapModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="locationHeader"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-header">
                    <span class="small text-info">
                        Zoom with mouse wheel or <i class="fa fa-plus fa-lg"></i> and <i class="fa fa-minus fa-lg"></i> controls &nbsp;~&nbsp;
                        Drag the marker to correct location &nbsp;~&nbsp;
                        Coordinates will be updated &nbsp;~&nbsp;
                        Close when done.
                    </span>
                </div>
                <div class="modal-body" id="locationMap" style="height:650px">
                    {{--map--}}
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    @parent
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAX1xKuub_cJqm_icVEDQ_iqe1iESyVXNY"></script>
@stop


@section('jquery')
    @parent
    <script>
        $(function() {

        });
    </script>
@stop