@extends('layouts.myaccount')

@section('title')
    <title>Close Account</title>
@endsection

@section('css')
    @parent
@endsection

@section('style')
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
                        <h3>Close Account</h3>
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
                <hr class="mt-2 mb-1 mb-md-5">

    {{-- sm - local nav ----------------------------------------------------------------------------------------}}

                <div class="btn-group d-md-none mb-5" role="group">
                    <a type="button" class="btn btn-outline-dark btn-sm ml-1" href="{!! url('myaccount/profile',[Auth::user()->id]) !!}">Profile</a>
                    <a type="button" class="btn btn-outline-dark btn-sm ml-1" href="{!! url('myaccount/password') !!}">Password</a>
                    <a type="button" class="btn btn-outline-dark btn-sm disabled">Account</a>
                </div>

                <div class="row">
                    <div class="col-sm-8 col-md-8 col-lg-6 col-xl-5">
                        <p class="text-muted ml-3">You can request that your account be closed by clicking on the button below.</p>
                        <p class="text-muted ml-3 mb-5">We will check the status of your bookings and invoices and notify you accordingly.</p>
                            <a class="btn btn-primary" href="{!! url('myaccount/account',[Auth::user()->id]) !!}">Close Account</a>
                        </div>
                    </div>
            </section>
        </div>
    </div>
@endsection

@section('script')
    @parent
@endsection

@section('jquery')
    <script>
        $(function() {

        });
    </script>
@endsection