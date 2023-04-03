@extends('layouts.myaccount')

@section('title')
    <title>Profile</title>
@endsection

@section('css')
    @parent
@endsection

@section('style')
    @parent
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">
            @include('layouts.nav.nav-myaccount-profile')

    {{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

    {{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        <h3>Your Profile</h3>
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
                <hr class="mt-2 mb-1 mb-md-2">

    {{-- sm - local nav ----------------------------------------------------------------------------------------}}

                <div class="btn-group d-md-none mb-3" role="group">
                    <a type="button" class="btn btn-outline-dark btn-sm disabled">Profile</a>
                    <a type="button" class="btn btn-outline-dark btn-sm ml-1" href="{!! url('myaccount/password') !!}">Password</a>
                    <a type="button" class="btn btn-outline-dark btn-sm ml-1" href="{!! url('myaccount/account') !!}">Account</a>
                </div>

                <p class="text-muted mb-3 mb-md-5">As the account holder you are the primary liaison.</p>

    {{-- form  ----------------------------------------------------------------------------------------}}
                {!! Form::model($user, ['url' => ['myaccount/profile/update', $user->id], 'id' => 'capture']) !!}
                    @include('office.users.partial-profile')
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
@endsection

@section('script')
    @parent
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAX1xKuub_cJqm_icVEDQ_iqe1iESyVXNY"></script>
@endsection

@section('jquery')
    <script>
        $(function() {

            {{-- clear geo when address changes --}}
            $(document).on('change', '#street, #suburb, #city', function() {
                $('#geo').val('');
            });
        });
    </script>
@endsection