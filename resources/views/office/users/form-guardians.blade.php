@extends('layouts/office')

@section('title')
    <title>Customer Guardians</title>
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
                            <span class="small">{{ $user->id }}:</span> {{ $user->name }} <span class="small">(guardians)</span>
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
                            <span class="breadcrumb-item">Guardians</span>
                            <a class="breadcrumb-item" href="{!! url('office/users/children/edit', ['id' => $user->id]) !!}">Children</a>
                            <a class="breadcrumb-item" href="{!! url('office/users/xmurals/edit', ['id' => $user->id]) !!}">Extramurals</a>
                        </small>
                    @endif
                </nav>

{{-- form ----------------------------------------------------------------------------------------}}

                {!! Form::open(['url' => 'office/users/guardians/store', 'id' => 'capture']) !!}
                    @include('office.users.partial-guardians')
                {!! Form::close() !!}
            </section>
        </div>
    </div>
@stop

@section('script')
    @parent
@stop


@section('jquery')
    @parent
    <script>
        $(function() {
        });
    </script>
@stop