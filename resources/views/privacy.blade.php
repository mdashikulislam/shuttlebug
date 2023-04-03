@extends('layouts.front')

@section('title')
    <title>Privacy</title>
@endsection

@section('css')
    @parent
    <link rel="stylesheet" href="{!! asset('css/front.css') !!}">
@endsection

@section('style')
@endsection

@section('content')
    <div class="container">
        @include('layouts.nav.front-nav')

        <header>
            <div class="row justify-content-end h-100">
                <div class="col-6 col-md-9 col-lg-12 my-auto">
                    <h1 class="text-center">safely transporting your children</h1>
                </div>
            </div>
        </header>

        <section class="leader text-center mt-5">
            @include('layouts/leader-menu')
        </section>

        <section class="article pt-5">
            <h1 class="">Privacy</h1>
            <p>We recognise the sensitive nature of the data you provide during registration and while managing your information and take care that it is adequately protected.</p>
            <br>
            <ul style="line-height:1.8">
                <li>No information is shared with any third parties other than as detailed below.</li>
                <li>We only use the information as intended for the administration of our service.</li>
                <li>Access to your account information is restricted to you and selected admin staff only.</li>
                <li>Passenger information and collection / destination addresses are available only to staff who need the information to deliver the service.</li>
                <li>We will provide pertinent information where necessary to first responders in emergencies.</li>
                <li>We do not use your contact details for promotions.</li>
                <li>We contact you only when requested in the various notification options.</li>
                <li>The website uses cookies to facilitate the delivery of information while you are browsing.</li>
                <li>When you close your account at Shuttle Bug all your stored data is removed from our servers.</li>
            </ul>
        </section>
    </div>

    <section class="footer">
        <div class="container">
            @include('layouts/footer')
        </div>
    </section>
@endsection

@section('script')
@endsection

@section('jquery')
@endsection