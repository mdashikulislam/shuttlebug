@extends('layouts/office')

@section('title')
    <title>Customer Profile</title>
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
                        @if ( !is_null($user) )
                            <h3>
                                <span class="small">{{ $user->id }}:</span>
                                {{ $user->name }}
                            </h3>
                        @else
                            <h3>Register a New Customer</h3>
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
                <hr class="mt-2 mb-0">

{{-- local nav ---------------------------------------------------------------------------------}}

                <nav class="breadcrumb pl-0 mb-5">
                    @if ( !is_null($user) )
                        <small>
                            <span class="breadcrumb-item">Profile</span>
                            <a class="breadcrumb-item" href="{!! url('office/users/guardians/edit', ['id' => $user->id]) !!}">Guardians</a>
                            <a class="breadcrumb-item" href="{!! url('office/users/children/edit', ['id' => $user->id]) !!}">Children</a>
                            <a class="breadcrumb-item" href="{!! url('office/users/xmurals/edit', ['id' => $user->id]) !!}">Extramurals</a>
                        </small>
                    @endif
                </nav>

{{-- form ----------------------------------------------------------------------------------------}}

                @if ( ! is_null($user) )
                    {!! Form::model($user, ['url' => ['office/users/customers/update', $user->id], 'id' => 'capture']) !!}
                @else
                    {!! Form::open(['url' => 'office/users/customers/store', 'id' => 'capture']) !!}
                @endif

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
@stop

@section('script')
    @parent
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAX1xKuub_cJqm_icVEDQ_iqe1iESyVXNY"></script>
@stop

@section('jquery')
    @parent
    <script>
        $(function() {
            {{-- clear geo when address changes --}}
            $(document).on('change', '#street, #suburb, #city', function() {
                $('#geo').val('');
            });

            {{-- generate password for create form --}}
            @if ( !isset($user) )
                let pw = generatePassword(8);
                $('#password').val(pw);
            @endif
        });
    </script>
@stop