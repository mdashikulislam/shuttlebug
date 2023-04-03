@extends('layouts/office')

@section('title')
    <title>Customer Xmurals</title>
@stop

@section('css')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-users')

{{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

{{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        <h3>
                            <span class="small user">{{ $user->id }}</span>: {{ $user->name }} <span class="small">(extramurals)</span>
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
                <hr class="mt-2 mb-0">

{{-- local nav ---------------------------------------------------------------------------------}}

                <nav class="breadcrumb pl-0 mb-5">
                    @if ( !is_null($user) )
                        <small>
                            <a class="breadcrumb-item" href="{!! url('office/users/customers/edit', ['id' => $user->id]) !!}">Profile</a>
                            <a class="breadcrumb-item" href="{!! url('office/users/guardians/edit', ['id' => $user->id]) !!}">Guardians</a>
                            <a class="breadcrumb-item" href="{!! url('office/users/children/edit', ['id' => $user->id]) !!}">Children</a>
                            <span class="breadcrumb-item">Extramurals</span>
                        </small>
                    @endif
                </nav>

                <div class="row">
                    <div class="col-md-6 col-xl-2">

{{-- form selection ----------------------------------------------------------------------------------------}}

                        <div class="form-group">
                           {!! Form::select('xmurals', ['' => 'Extramurals'] +$xmurals, null, ['class' => "form-control custom-select callform", 'id' => 'xmurals']) !!}
                        </div>

                        <button class="btn btn-outline-secondary addxmural mt-xl-5">Add Extramural</button>

{{-- notes ----------------------------------------------------------------------------------------}}

                        <p class="text-muted mt-3"><small>Do not add school venues to extramurals. Lifts to/from school should be booked as a school shuttle, not an extramural shuttle.</small></p>

                    </div>

{{-- form ----------------------------------------------------------------------------------------}}

                    <div class="form-container col-md-12 col-xl-9 ml-xl-5">
                    </div>
                </div>
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
        let xmural = "{!! is_null($xmurals) ? '' : 'set' !!}";
        let elemId = '';
        let elemNf = '';

        $(function() {

            {{-- load new form --}}
            $('.addxmural').on('click', function() {
                $('#xmurals').val('');
                let user = $('.user').html();
                $('.form-container').load('/office/users/xmurals/form/' + user + '/null', function () {
                    elemId = $('#id');
                    $('.form-container').show();
                    $(elemId).prop('disabled', false);
                });
            });

            {{-- toggle new venue --}}
            $(document).on('click', '#newvenue', function() {
                elemNf = $('#newform');
                $(elemNf).toggleClass('d-none');
                if ( $(elemNf).hasClass('d-none') ) {
                    $('input').prop('required', false);
                    $(elemId).prop('disabled', false);
                } else {
                    $(elemId).val('').prop('disabled', true);
                    setRequiredFields();
                }
            });

            {{-- load xmural form --}}
            $('.callform').on('change', function() {
                let xmural = $(this).val();
                let user = $('.user').html();

                if ( xmural > '' ) {
                    $('.form-container').load('/office/users/xmurals/form/' + user + '/' + xmural, function () {
                        elemId = $('#id');
                        $('.form-container').show();
                        $(elemId).prop('disabled', true);
                        $('#venue').prop('readonly', true);
                        setRequiredFields();
                    });
                } else {
                    $('.form-container').empty();
                }
            });

            {{-- remove geo if address fields change --}}
            $(document).on('change', '.address', function() {
                $('#geo').val('');
            });

            {{-- switches new venue inputs to required --}}
            function setRequiredFields() {
                $('#venue').prop('required', true);
                $('#street').prop('required', true);
                $('#suburb').prop('required', true);
                $('#city').prop('required', true);
                $('#geo').prop('required', true);
            }

            {{-- enable known venues input before submitting form --}}
            $('#capture').bind('submit', function () {
                $(this).find(elemId).prop('disabled', false);
            });
        });
    </script>
@stop