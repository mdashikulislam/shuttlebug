@extends('layouts/office')

@section('title')
    <title>School Form</title>
@stop

@section('css')
    @parent
    <link rel="stylesheet" href="{{ asset('css/jquery.timepicker.css') }}">
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-schools')

{{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

{{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        @if ( !is_null($school) )
                            <h3>
                                <span class="small">{{ $school->id }}:</span> {{ $school->name }}
                            </h3>
                        @else
                            <h3>Create a new School</h3>
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

                @if ( ! is_null($school) )
                    {!! Form::model($school, ['url' => ['office/schools/update', $school->id], 'id' => 'capture']) !!}
                @else
                    {!! Form::open(['url' => 'office/schools/store', 'id' => 'capture']) !!}
                @endif
                <div class="row">
                    <div class="col-md-11 col-lg-6 col-xl-5">

                {{-- name --------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            @php $e_name = $errors->has('name') ? 'is-invalid' : ''; @endphp

                            {!! Form::label('name', 'Name', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('name', null, ['class' => "form-control $e_name", 'autofocus']) !!}
                                <div class="invalid-feedback"><small>{{ $errors->first('name') }}</small></div>
                            </div>
                        </div>

                {{-- phone ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('phone', 'Phone', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('phone', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>

                {{-- dropfrom ----------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            @php $e_from = $errors->has('dropfrom') ? 'is-invalid' : ''; @endphp

                            {!! Form::label('dropfrom', 'Opening Time', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('dropfrom', null, ['class' => "form-control time $e_from"]) !!}
                                <div class="invalid-feedback"><small>{{ $errors->first('dropfrom') }}</small></div>
                            </div>
                        </div>

                {{-- dropby ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            @php $e_by = $errors->has('dropby') ? 'is-invalid' : ''; @endphp

                            {!! Form::label('dropby', 'Start Time', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('dropby', null, ['class' => "form-control time $e_by"]) !!}
                                <div class="invalid-feedback"><small>{{ $errors->first('dropby') }}</small></div>
                            </div>
                        </div>

                {{-- status ----------------------------------------------------------------------------------------}}

                        @if ( !is_null($school) )
                            <div class="form-group row">
                                {!! Form::label('role', 'Status', ['class' => 'col col-form-label']) !!}
                                <div class="col-md-9 col-lg-8">
                                    <label class="control control-radio mt-1">Active
                                        {!! Form::radio('status', 'active', null) !!}
                                        <span class="control_indicator"></span>
                                    </label>
                                    <label class="control control-radio ml-3 mt-1">Inactive
                                        {!! Form::radio('status', 'inactive', null) !!}
                                        <span class="control_indicator"></span>
                                    </label>
                                </div>
                            </div>
                        @endif

                        <h6><strong>Address</strong></h6>

                {{-- unit ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('unit', 'Building/Estate No & Name', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-7">
                                {!! Form::text('unit', null, ['class' => 'form-control', 'placeholder' => 'if applicable']) !!}
                            </div>
                        </div>

                {{-- street ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            @php $e_street = $errors->has('street') ? 'is-invalid' : ''; @endphp

                            {!! Form::label('street', 'Street No & Name', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-7">
                                {!! Form::text('street', null, ['class' => "form-control address $e_street"]) !!}
                                <div class="invalid-feedback"><small>{{ $errors->first('street') }}</small></div>
                            </div>
                        </div>

                {{-- suburb ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            @php $e_suburb = $errors->has('suburb') ? 'is-invalid' : ''; @endphp

                            {!! Form::label('suburb', 'Suburb', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-7">
                                {!! Form::text('suburb', null, ['class' => "form-control address $e_suburb", 'placeholder' => 'eg. Hout Bay']) !!}
                                <div class="invalid-feedback"><small>{{ $errors->first('suburb') }}</small></div>
                            </div>
                        </div>

                {{-- city ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            @php $e_city = $errors->has('city') ? 'is-invalid' : ''; @endphp

                            {!! Form::label('city', 'City', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-7">
                                {!! Form::text('city', null, ['class' => "form-control address $e_city", 'placeholder' => 'eg. Cape Town']) !!}
                                <div class="invalid-feedback"><small>{{ $errors->first('city') }}</small></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 col-xl-5 ml-xl-3">

                {{-- geo location ----------------------------------------------------------------------------------}}

                        <h6 class="mt-5 mt-lg-0"><strong>Map Location</strong></h6>
                        <p class="text-muted"><small>Click the map button to find the school - drag the marker to the correct position.</small></p>

                        <div class="form-group row">
                            @php $e_geo = $errors->has('geo') ? 'is-invalid' : ''; @endphp

                            {!! Form::label('geo', 'Map Location', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                <div class="input-group">
                                    {!! Form::text('geo', null, ['class' => "form-control $e_geo"]) !!}
                                    <div class="input-group-addon btn btn-outline-dark" id="callLocationMap" role="button" title="view map">
                                        <i class="fa fa-globe fa-lg"></i>
                                    </div>
                                </div>
                                <div class="text-danger"><small>{{ $errors->first('geo') }}</small></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-11 col-lg-12 col-xl-10"><hr class="mb-1"></div>
                    <div class="col-xl-12"></div>

                {{-- submit ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-6 col-xl-4 mt-4 mb-5">
                        @if ( is_null($school) )
                            {!! Form::submit('Save School', ['class' => 'btn btn-primary']) !!}
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

            {{-- remove geo if address fields change --}}
            $(document).on('change', '.address', function() {
                $('#geo').val('');
            });
        });
    </script>
@stop
