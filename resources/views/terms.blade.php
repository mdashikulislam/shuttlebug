@extends('layouts.front')

@section('title')
    <title>Terms</title>
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
            <h1 class="">Terms</h1>
            <p>By registering as a customer of Shuttle Bug you confirm your agreement to the following terms.</p>
            <br>
            <ul style="line-height:1.8">
                <li>The password that you set for use to access your private account pages must be sufficiently robust to protect against unauthorised access by being at least 8 characters in length and contain alpha characters, numerals and symbols.</li>
                <li>We must be notify of all changes to contact information, collection and destination addresses and passenger information.</li>
                <li>We must be notified of all pertinent medical information of passengers that is required to provide adequate care .</li>
                <li>The contact details of at least one additional guardian is provided in case we are unable to contact you.</li>
                <li>Changes to shuttle bookings for the following day's early morning shuttles must be received by the previous evening.</li>
                <li>Changes to shuttle bookings for the day's school / extramural collections must be received by 8am that day.</li>
                <li>Accounts are payable on presentation.</li>
                <li>We reserve the right to cancel bookings when circumstances beyond our control prevent us from executing the shuttles as planned.</li>
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