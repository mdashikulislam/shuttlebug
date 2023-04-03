@extends('layouts/office')

@section('title')
    <title>Booking Form</title>
@stop

@section('css')
    @parent
    <link rel="stylesheet" href="{{ asset('css/jquery.timepicker.css') }}">
@stop

@section('style')

@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-bookings')

{{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

{{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        <h3>
                            <small>Booking:</small> {{ $passenger->name }}
                            @if ( $passenger->friend == 'friend' )
                                (friend)
                            @endif
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
                <hr>

                <div class="row">
                    <div class="col-lg">
                        <ul class="text-muted mt-2 mb-5">
                            <li><em>Home <i class="fa fa-long-arrow-right"></i> School</em> shuttles <u>before 09:00</u> do not require times.</li>
                            <li><em>Home <i class="fa fa-long-arrow-right"></i> Xmural</em> shuttles <u>before 09:00</u> require a '<em> By </em>' time.</li>
                            <li>All other bookings should have an '<em> At </em>' time.</li>
                            @if ( $date < now()->toDateString() )
                                <li class="text-danger">Bookings added or cancelled via journal entries are not shown.<br>
                                    <small>Use "List Customer Bookings" on the manage page to see them.</small></li>
                            @endif
                        </ul>
                    </div>

                    <div class="col-lg">
                        @if ( $date < now()->toDateString() )
                            <h6 class="text-info mt-2">You are changing bookings that have already been invoiced so these changes will be processed via journal entries.</h6>
                            <hr>
                        @endif
                    </div>
                </div>

{{-- form ----------------------------------------------------------------------------------------}}

                {!! Form::open(['url' => 'office/bookings/store', 'id' => 'capture']) !!}
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

                            @php $n = 1; @endphp
                            @foreach ( $bookings as $booking )
                                <div class="form-row pt-2">

                            {{-- puloc ------------------------------------------------------------------------------}}

                                    <div class="form-group col-7 col-lg-3">
                                        @php $var = 'edit.'.$booking->id.'.puloc_id'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("edit[$booking->id][puloc_id]", "Pick Up $n") !!}
                                        {!! Form::select("edit[$booking->id][puloc_id]", ['' => ''] +$venues, $booking->puloc_id,
                                            ['class' => "form-control custom-select puloc $$var",
                                            'data-time' => "putime$booking->id",
                                            'id' => "puloc$booking->id",
                                            $disabled]) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>

                            {{-- putime -----------------------------------------------------------------------------}}

                                    <div class="form-group col-3 col-lg-2">
                                        @php $var = 'edit.'.$booking->id.'.putime'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("edit[$booking->id][putime]", 'At') !!}
                                        {!! Form::text("edit[$booking->id][putime]", $booking->putime > 0 ? $booking->putime:null,
                                            ['class' => "form-control time $$var",
                                            'id' => "putime$booking->id",
                                            'data-other' => "dotime$booking->id",
                                            $disabled]) !!}
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
                                        @php $var = 'edit.'.$booking->id.'.doloc_id'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("edit[$booking->id][doloc_id]", 'Drop Off') !!}
                                        {!! Form::select("edit[$booking->id][doloc_id]", ['' => ''] +$venues, $booking->doloc_id,
                                            ['class' => "form-control custom-select doloc $$var",
                                            'data-time' => "dotime$booking->id",
                                            'id' => "doloc$booking->id",
                                            $disabled]) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>

                            {{-- dotime -----------------------------------------------------------------------------}}

                                    <div class="form-group col-3 col-lg-2">
                                        @php $var = 'edit.'.$booking->id.'.dotime'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("edit[$booking->id][dotime]", 'By') !!}
                                        {!! Form::text("edit[$booking->id][dotime]", $booking->dotime > 0 ? $booking->dotime:null,
                                            ['class' => "form-control time $$var",
                                            'id' => "dotime$booking->id",
                                            'data-other' => "putime$booking->id",
                                            $disabled, 'readonly']) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>

                            {{-- price ------------------------------------------------------------------------------}}

                                    <div class="form-group col-2 col-lg-1">
                                        {!! Form::label("edit[$booking->id][price]", 'Price') !!}
                                        {!! Form::text("edit[$booking->id][price]", $booking->price,
                                            ['class' => 'form-control',
                                            'id' => "price$booking->id",
                                            'readonly', $disabled]) !!}
                                    </div>

                            {{-- desktop cancel --------------------------------------------------------------------}}

                                    <div class="form-group col-lg-1 d-none d-lg-block">
                                        {!! Form::label("", 'Cancel') !!}
                                        <label class="control control-checkbox-danger ml-2">
                                            {!! Form::checkbox("edit[$booking->id][cancel]", $booking->price, null) !!}
                                            <span class="control_indicator"></span>
                                        </label>
                                    </div>
                                </div>
                                @php $n++; @endphp
                            @endforeach
                        </div>

                    {{-- new bookings -----------------------------------------------------------------------------}}

                        <div class="col-md-12">
                            @if ( count($bookings) < 4 )
                                @if ( $date < now()->toDateString() )
                                    <h6 class="mt-3"><strong>Add Bookings to Invoice</strong></h6>
                                    <p class="text-danger ml-3 mb-3"><small>Only do this for shuttles completed without a booking !</small></p>
                                @else
                                    <h6 class="mt-3 mb-3"><strong>Add New Bookings</strong></h6>
                                @endif
                            @endif

                            @for ( $i = $n; $i <= 4; $i++ )
                                <div class="form-row pt-2">

                            {{-- puloc ----------------------------------------------------------------------------}}

                                    <div class="form-group col-7 col-lg-3">
                                        @php $var = 'create.'.$i.'.puloc_id'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("create[$i][puloc_id]", "Pick Up $i") !!}
                                        {!! Form::select("create[$i][puloc_id]", ['' => ''] +$venues, null,
                                            ['class' => "form-control custom-select puloc $$var",
                                            'data-time' => "putime$i",
                                            'id' => "puloc$i"]) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>

                            {{-- putime -----------------------------------------------------------------------------}}

                                    <div class="form-group col-3 col-lg-2">
                                        @php $var = 'create.'.$i.'.putime'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("create[$i][putime]", 'At') !!}
                                        {!! Form::text("create[$i][putime]", null,
                                            ['class' => "form-control time $$var",
                                            'id' => "putime$i"]) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>

                            {{-- doloc ------------------------------------------------------------------------------}}

                                    <div class="form-group col-7 col-lg-3">
                                        @php $var = 'create.'.$i.'.doloc_id'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("create[$i][doloc_id]", 'Drop Off') !!}
                                        {!! Form::select("create[$i][doloc_id]", ['' => ''] +$venues, null,
                                            ['class' => "form-control custom-select doloc $$var",
                                            'data-time' => "dotime$i",
                                            'id' => "doloc$i"]) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>

                            {{-- dotime ----------------------------------------------------------------------------}}

                                    <div class="form-group col-3 col-lg-2">
                                        @php $var = 'create.'.$i.'.dotime'; $$var = $errors->has($var) ? 'is-invalid' : ''; @endphp

                                        {!! Form::label("create[$i][dotime]", 'By') !!}
                                        {!! Form::text("create[$i][dotime]", null,
                                            ['class' => "form-control time $$var",
                                            'id' => "dotime$i", 'readonly']) !!}
                                        <div class="text-danger"><small>{{ $errors->first($var) }}</small></div>
                                    </div>
                                    <div class="form-group col-2 col-xl-1">
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <div class="col-12"></div>

                    {{-- submit ----------------------------------------------------------------------------------------}}

                        <div class="col-lg-6 col-xl-4 mt-4 mb-5">
                            {!! Form::hidden('user_id', $passenger->user_id) !!}
                            {!! Form::hidden('passenger_id', $passenger->id) !!}
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
                            If the school doesn't appear in the list it might be marked as inactive and you will need to re-activate the school before booking it as a venue.<br><br>
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
@stop

@section('script')
    @parent
    <script src="{!! asset('js/jquery.timepicker.min.js') !!}"></script>
@stop


@section('jquery')
    @parent
    <script>
        let dropby = "{!! substr($passenger->school->dropby,0,5) !!}";

        $(function() {
            $('.time').timepicker({
                'timeFormat': 'H:i',
                // 'disableTextInput': true,
                'minTime': '08:00',
                'maxTime': '20:00',
                'step': '15'
            });

            {{-- change times for existing bookings --}}
            // $('.puloc').each(function() {
            //     if ( $(this).val() > '' ) {
            //         let school = $(this).val();
            //         let timefield = $(this).data('time');
            //
            //         if ( typeof nontimes[school] !== 'undefined' ) {
            //             $('#' + timefield).timepicker('option', {
            //                 disableTimeRanges: nontimes[school]
            //             });
            //         }
            //     }
            // });

            {{-- handle venue change --}}
            $('.puloc, .doloc').on('change', function() {
                let timefield = $(this).data('time');
                let text = $(this).find(':selected').text();

                {{-- toggle readonly property for morning xmurals --}}
                if ($(this).hasClass('doloc') && $(this).val() < 400000 && $(this).val() > 300000 ) {
                    $('#' + timefield).val('').prop('readonly', false);
                } else if ($(this).hasClass('doloc')) {
                    $('#' + timefield).prop('readonly', true);
                }

                {{-- show modal for other school selection --}}
                if (text === 'Other School') {
                    $('#referrer').val($(this).attr('id'));
                    $('#otherSchools').modal('show');
                }

                {{-- set dotime for school --}}
                if (text.substring(0,9) === 'My School' && $(this).hasClass('doloc') ) {
                    let pickupfield = $(this).attr('id').replace(/doloc/g, 'puloc');

                    if ($('#' + pickupfield).find(':selected').text() === 'Home') {
                        $('#' + timefield).val(dropby).prop('readonly', false);
                    }
                }
            });

            {{-- handle other school change --}}
            $('#otherschool').on('change', function() {
                let value = $(this).val();
                let text = $(this).find(':selected').text();
                let venue = $('#referrer').val();

                {{-- add selected school to venue options and select this school for the current venue --}}
                $('.puloc, .doloc').each(function() {
                    if ( $(this).attr('id') === venue ) {
                        $(this).append('<option value="' + value + '" selected="selected">' + text + '</option>');
                        $('.puloc').trigger("change");
                    } else {
                        $(this).append('<option value="' + value + '">' + text + '</option>');
                    }
                });
            });

        });
    </script>
@stop
