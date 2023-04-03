@extends('layouts/office')

@section('title')
    <title>Event Booking Form</title>
@stop

@section('css')
    @parent
    <link rel="stylesheet" href="{{ asset('css/jquery.timepicker.css') }}">
@stop

@section('style')
    <style>
        .form-row { border-bottom: 1px solid #cbcdcf; }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-events')

{{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

{{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        <h3>
                            <small>Event Booking:</small> {{ $customer->name }}
                            <small>{{ \Carbon\Carbon::createFromFormat('Y-m-d',$date)->format('D j M Y') }}</small>
                        </h3>
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
                <hr class=" mt-2 mb-5">

{{-- form ----------------------------------------------------------------------------------------}}

                {!! Form::open(['url' => 'office/events/store', 'id' => 'capture']) !!}
                    <div class="row">

                    {{-- existing bookings ---------------------------------------------------------------------}}

                        <div class="col-md-12">
                            @if ( count($bookings) > 0 )
                                @if ( $date < now()->toDateString() )
                                    <h6 class=""><strong>Cancel Completed Bookings</strong></h6>
                                    <p class="text-danger ml-3 mb-3"><small>This will remove them from Invoicing !</small></p>
                                    @php $disabled = 'disabled'; @endphp
                                @else
                                    <h6 class="mb-3"><strong>Edit Existing Bookings</strong></h6>
                                    @php $disabled = ''; @endphp
                                @endif
                            @endif

                            @php $n = 0; @endphp
                            @foreach ( $bookings as $booking )
                                @php $style = $loop->index & 1 ? 'background:#f9f9f9' : 'background:#fff'; $n++; @endphp
                                <div class="form-row pt-2" style="{{ $style }}">

                            {{-- puloc ------------------------------------------------------------------------------}}

                                    <div class="form-group col-7 col-lg-3">
                                        @php $var = 'edit.'.$booking->id.'.puloc'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("edit[$booking->id][puloc]", "Pick Up $n") !!}
                                        {!! Form::select("edit[$booking->id][puloc]", ['' => ''] +$venues, $booking->puloc,
                                            ['class' => "form-control custom-select puloc $$var",
                                            'id' => "puloc$booking->id", $disabled, 'required']) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>

                            {{-- putime -----------------------------------------------------------------------------}}

                                    <div class="form-group col-3 col-lg-2 col-xl-1">
                                        @php $var = 'edit.'.$booking->id.'.putime'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("edit[$booking->id][putime]", 'At') !!}
                                        {!! Form::text("edit[$booking->id][putime]", $booking->putime > 0 ? $booking->putime:null,
                                            ['class' => "form-control time $$var", $disabled, 'required']) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>

                            {{-- mobile cancel ----------------------------------------------------------------------}}

                                    <div class="col-2 col-md-1 d-lg-none">
                                        {!! Form::label("", 'Cancel') !!}
                                        <label class="control control-checkbox-danger ml-2">
                                            {!! Form::checkbox("edit[$booking->id][cancel]", $booking->id, null) !!}
                                            <span class="control_indicator"></span>
                                        </label>
                                    </div>

                            {{-- doloc ------------------------------------------------------------------------------}}

                                    <div class="form-group col-7 col-lg-3">
                                        @php $var = 'edit.'.$booking->id.'.doloc'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("edit[$booking->id][doloc]", 'Drop Off') !!}
                                        {!! Form::select("edit[$booking->id][doloc]", ['' => ''] +$venues, $booking->doloc,
                                            ['class' => "form-control custom-select doloc $$var",
                                            'id' => "doloc$booking->id", $disabled, 'required']) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>

                            {{-- passengers -------------------------------------------------------------------------}}

                                    <div class="form-group col-3 col-lg-2 col-xl-1">
                                        @php $var = 'edit.'.$booking->id.'.passengers'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("edit[$booking->id][passengers]", 'Passengers') !!}
                                        {!! Form::text("edit[$booking->id][passengers]", $booking->passengers,
                                            ['class' => "form-control $$var $disabled", 'required']) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>

                            {{-- price ------------------------------------------------------------------------------}}

                                    <div class="form-group col-2 col-lg-1">
                                        {!! Form::label("edit[$booking->id][tripfee]", 'Trip Fee') !!}
                                        {!! Form::text("edit[$booking->id][tripfee]", $booking->tripfee,
                                            ['class' => 'form-control', $disabled, 'required']) !!}
                                    </div>

                            {{-- desktop cancel --------------------------------------------------------------------}}

                                    <div class="form-group col-lg-1 d-none d-lg-block">
                                        {!! Form::label("", 'Cancel') !!}
                                        <label class="control control-checkbox-danger ml-2">
                                            {!! Form::checkbox("edit[$booking->id][cancel]", $booking->id, null) !!}
                                            <span class="control_indicator"></span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    {{-- new bookings -----------------------------------------------------------------------------}}

                        <div class="col-md-12">
                            @if ( count($bookings) < 2 )
                                @if ( $date < now()->toDateString() )
                                    <h6 class="mt-3"><strong>Add Bookings to Invoice</strong></h6>
                                    <p class="text-danger ml-3 mb-3"><small>Only do this for shuttles completed without bookings !</small></p>
                                @else
                                    <h6 class="mt-3 mb-3"><strong>Add New Bookings</strong></h6>
                                @endif
                            @endif

                            @for ( $i = $n+1; $i <= 2; $i++ )
                                @php $style = $i & 1 ? 'background:#fff' : 'background:#f9f9f9'; @endphp
                                <div class="form-row pt-2" style="{{ $style }}">

                            {{-- puloc ----------------------------------------------------------------------------}}

                                    <div class="form-group col-7 col-lg-3">
                                        @php $var = 'create.'.$i.'.puloc'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("create[$i][puloc]", "Pick Up $i") !!}
                                        {!! Form::select("create[$i][puloc]", ['' => ''] +$venues, null,
                                            ['class' => "form-control custom-select puloc $$var", 'id' => "puloc$i"]) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>

                            {{-- putime -----------------------------------------------------------------------------}}

                                    <div class="form-group col-3 col-lg-2 col-xl-1">
                                        @php $var = 'create.'.$i.'.putime'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("create[$i][putime]", 'At') !!}
                                        {!! Form::text("create[$i][putime]", null,
                                            ['class' => "form-control time $$var"]) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>

                            {{-- doloc ------------------------------------------------------------------------------}}

                                    <div class="form-group col-7 col-lg-3">
                                        @php $var = 'create.'.$i.'.doloc'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("create[$i][doloc]", 'Drop Off') !!}
                                        {!! Form::select("create[$i][doloc]", ['' => ''] +$venues, null,
                                            ['class' => "form-control custom-select doloc $$var", 'id' => "doloc$i"]) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>

                            {{-- passengers ----------------------------------------------------------------------------------------}}

                                    <div class="form-group col-3 col-lg-2 col-xl-1">
                                        @php $var = 'create.'.$i.'.passengers'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("create[$i][passengers]", 'Passengers') !!}
                                        {!! Form::text("create[$i][passengers]", null, ['class' => "form-control $$var"]) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>

                            {{-- price -------------------------------------------------------------------------}}

                                    <div class="form-group col-3 col-lg-2 col-xl-1">
                                        @php $var = 'create.'.$i.'.tripfee'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("create[$i][tripfee]", 'Trip Fee') !!}
                                        {!! Form::text("create[$i][tripfee]", null, ['class' => "form-control $$var"]) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <div class="col-12"></div>

                    {{-- submit ----------------------------------------------------------------------------------------}}

                        <div class="col-lg-6 col-xl-4 mt-4 mb-5">
                            {!! Form::hidden('user_id', $customer->id) !!}
                            {!! Form::hidden('date', $date) !!}

                            @if ( $date < now()->toDateString() )
                                {!! Form::submit('Update Invoice', ['class' => 'btn btn-danger']) !!}
                            @else
                                {!! Form::submit('Save Bookings', ['class' => 'btn btn-primary']) !!}
                            @endif
                        </div>
                    </div>
                {!! Form::close() !!}
            </section>
        </div>
    </div>

{{-- modals ----------------------------------------------------------------------------------------}}

    <div class="modal fade" id="otherSchools" tabindex="-1" role="dialog" aria-labelledby="Schools" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="Schools">Select the School</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @if ( !is_null($schools) )
                    <div class="modal-body">
                        <p class="text-muted" style="font-size:14px">The school selected here will be added to the Pick Up & Drop Off venues on the booking form, so it can be selected again if required.<br><br>
                            <u><strong>Be aware:</strong></u> If you save the booking and it is returned with errors, this school will no longer appear in the venues and you will have to add it again.
                        </p>
                        <div class="form-group">
                            {!! Form::label("otherschool", 'Select School') !!}
                            {!! Form::select('otherschool', ['' => ' '] +$schools, null, ['class' => 'form-control custom-select']) !!}
                            {!! Form::hidden('referrer', null, ['id' => 'referrer']) !!}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-dark" data-dismiss="modal">OK</button>
                        <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Close</button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="otherAddress" tabindex="-1" role="dialog" aria-labelledby="Address" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="Address">Provide the Address</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @if ( !is_null($schools) )
                    <div class="modal-body">
                        <p class="text-muted" style="font-size:14px">The address selected here will be added to the Pick Up & Drop Off venues on the booking form, so it can be selected again if required.<br><br>
                            <u><strong>Be aware:</strong></u> If you save the booking and it is returned with errors, this address will no longer appear in the venues and you will have to add it again.<br><br>
                            All fields are required.
                        </p>
                        {!! Form::label("street", 'Street Number & Name') !!}
                        {!! Form::text('street', null, ['class' => 'form-control mb-2']) !!}
                        {!! Form::label("suburb", 'Suburb') !!}
                        {!! Form::text('suburb', null, ['class' => 'form-control mb-2']) !!}
                        {!! Form::label("city", 'City') !!}
                        {!! Form::text('city', null, ['class' => 'form-control mb-2']) !!}
                        {!! Form::hidden('referrer', null, ['id' => 'referrer']) !!}
                    </div>
                    <div class="modal-footer">
                        <button id="submitaddress" type="button" class="btn btn-outline-dark" data-dismiss="modal">OK</button>
                        <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Close</button>
                    </div>
                @endif
            </div>
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

            {{-- handle venue change --}}
            $('.puloc, .doloc').on('change', function() {
                let text = $(this).find(':selected').text();

                {{-- show modal for school selection --}}
                if (text === 'A School') {
                    $('#referrer').val($(this).attr('id'));
                    $('#otherSchools').modal('show');
                }

                {{-- show modal for address selection --}}
                if (text === 'Other') {
                    $('#referrer').val($(this).attr('id'));
                    $('#otherAddress').modal('show');
                }
            });

            {{-- handle other school change --}}
            $('#otherschool').on('change', function() {
                let value = $(this).val();
                let text = $(this).find(':selected').text();
                let venue = $('#referrer').val();

                if ( value > '' ) {
                    {{-- add selected school to venue options and select this school for the current venue --}}
                    $('.puloc, .doloc').each(function () {
                        if ($(this).attr('id') === venue) {
                            $(this).append('<option value="' + value + '" selected="selected">' + text + '</option>');
                        } else {
                            $(this).append('<option value="' + value + '">' + text + '</option>');
                        }
                    });
                }
            });

            {{-- handle address selection --}}
            $('#submitaddress').on('click', function() {
                let street = $('#street').val();
                let suburb = $('#suburb').val();
                let city = $('#city').val();
                let venue = $('#referrer').val();

                if ( street > '' && suburb > '' && city > '' ) {
                    let address = street + ',' + suburb + ',' + city;
                    {{-- add address to venue options and select this address for the current venue --}}
                    $('.puloc, .doloc').each(function () {
                        if ($(this).attr('id') === venue) {
                            $(this).append('<option value="' + address + '" selected="selected">' + address + '</option>');
                        } else {
                            $(this).append('<option value="' + address + '">' + address + '</option>');
                        }
                    });
                } else {
                    alert('The address is incomplete.');
                }
            });

            {{-- remove booking --}}
            $('.remove').on('click', function() {
                let id = $(this).data('target');
                $('#puloc' + id).val('');
                $('#putime' + id).val('');
                $('#doloc' + id).val('');
                $('#dotime' + id).val('');
                $('#price' + id).val('');
            });

        });
    </script>
@stop
