@extends('layouts/office')

@section('title')
    <title>Customer Children</title>
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
                            <span class="small user">{{ $user->id }}</span>: {{ $user->name }} <span class="small">(children)</span>
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
                            <span class="breadcrumb-item">Children</span>
                            <a class="breadcrumb-item" href="{!! url('office/users/xmurals/edit', ['id' => $user->id]) !!}">Extramurals</a>
                        </small>
                    @endif
                </nav>

                <div class="row">

    {{-- form selection ----------------------------------------------------------------------------------------}}
                    <div class="col-sm-4 col-xl-2">
                        <div class="form-group">
                            {!! Form::select('children', ['' => 'Children'] +$children, null, ['class' => "form-control custom-select callform", 'id' => 'actChild']) !!}
                        </div>
                    </div>

                    <div class="col-sm-4 col-xl-2">
                        <div class="form-group">
                            {!! Form::select('friends', ['' => 'Friends'] +$friends, null, ['class' => "form-control custom-select callform", 'id' => 'actFriend']) !!}
                        </div>
                    </div>

                    <div class="col-sm-4 col-xl-2">
                        <button class="btn btn-outline-secondary addchild">Add Child</button>
                    </div>
                </div>

{{-- form ----------------------------------------------------------------------------------------}}

                <div class="row mt-5">
                    <div class="form-container col-md-12 col-xl-9 ml-xl-5">
                    </div>
                </div>
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
            {{-- new child form --}}
            $('.addchild').on('click', function() {
                let user = $('.user').html();
                $('.form-container').load('/office/users/children/form/' + user + '/null', function () {
                    $('.form-container').show();
                });
            });

            {{-- load child form --}}
            $('.callform').on('change', function() {
                let child = $(this).val();
                let user = $('.user').html();
                if ( child > '' ) {
                    $('.form-container').load('/office/users/children/form/' + user + '/' + child, function () {
                        $('.form-container').show();
                    });
                } else {
                    $('.form-container').empty();
                }
            });
        });
    </script>
@stop