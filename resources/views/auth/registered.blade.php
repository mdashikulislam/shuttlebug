@extends('layouts.front')

@section('title')
    <title>Registered</title>
@endsection

@section('css')
    @parent
    <link rel="stylesheet" href="{!! asset('css/front.css') !!}">
@endsection

@section('content')
    <div class="container">
        @include('layouts.nav.front-nav')

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-4 col-xl-5">
                <div class="mt-5">
                    <h2 class="text-center">Registered</h2>
                    <hr>
                </div>
                <div class="mt-5">
                    <p>Thank you for registering and welcome to Shuttle Bug.</p>
                    <p>Your registration details have been emailed to you for safe keeping.
                        <small>(check your spam folder if the email is not in your inbox)</small></p>
                    <p>You have been logged in, so you can now visit <a href="{{ url('myaccount/profile') }}">your account</a> to complete your profile.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
