@extends('layouts.myaccount')

@section('title')
    <title>Extramurals</title>
@endsection

@section('css')
    @parent
@endsection

@section('style')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <nav class="d-none d-md-block sidebar"></nav>

    {{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

    {{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        <h3>Extramurals</h3>
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
                <hr class="mt-2 mb-1 mb-2">
                <p class="text-muted mb-5">Add the extramural venues that you require for shuttles.<br>These should include locations other than <u>Home</u> and <u>School</u>.</p>

                <div class="row">
                    <div class="col-sm-6 col-lg-4">
                        @if ( count($xmurals) > 0 )
                            {!! Form::select('xmurals', ['' => 'Extramurals'] +$xmurals, null, ['class' => "form-control custom-select callform", 'id' => 'xmurals']) !!}
                        @else
                            <p>You have not added any Extramural Activities.</p>
                        @endif
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <button class="btn btn-outline-secondary addxmural mt-2 mt-sm-0">Add Extramural</button>
                    </div>
                </div>

    {{-- form ----------------------------------------------------------------------------------------}}

                <div class="row mt-3">
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

@endsection

@section('script')
    @parent
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAX1xKuub_cJqm_icVEDQ_iqe1iESyVXNY"></script>
@endsection

@section('jquery')
    <script>
        let xmural = "{!! is_null($xmurals) ? '' : 'set' !!}";
        let elemId = '';
        let elemNf = '';

        $(function() {
            {{-- load new form --}}
            $('.addxmural').on('click', function() {
                $('#xmurals').val('');
                $('.form-container').load('/myaccount/xmurals/form/{{ $user->id }}/null', function () {
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
                    $(elemId).val('');
                    $(elemId).prop('disabled', true);
                    setRequiredFields();
                }
            });

            {{-- load xmural form --}}
            $('.callform').on('change', function() {
                if ( $(this).val() > '' ) {
                    $('.form-container').load('/myaccount/xmurals/form/{{ $user->id }}/' + $(this).val(), function () {
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
@endsection