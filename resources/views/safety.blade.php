@extends('layouts.front')

@section('title')
    <title>Safety</title>
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
            <h1 class="">Safety</h1>
            <p>Shuttle Bug is a convenient, safe and professional service that is dedicated to the transportation of children.</p>
            <br>
            <img class="img-fluid float-sm-right ml-5" src="{!! asset('/images/safety1.jpg') !!}" alt="Transport safety">
            <p>We have implemented these requirements for all our vehicles and do not compromise in any way.</p>
            <ul style="line-height:1.8">
                <li>All drivers have professional driving licences in date and without restrictions.</li>
                <li>All drivers are fully trained and regularly monitored to ensure that safe driving habits are maintained.</li>
                {{--<li>All drivers and assistants have first aid training and certification in date.</li>--}}
                <li>All passengers have to wear seatbelts at all times.</li>
                <li>Car and booster seats are available in all vehicles and are used for any child under 6.</li>
                {{--<li>The vehicle is tracked and monitored via satellite 24hrs a day.</li>--}}
                <li>We are in constant touch with emergency services if needed.</li>
                {{--<li>Back up vehicles and drivers are available in case of a breakdown.</li>--}}
                <li>Parents are notified by SMS or email every time their child is dropped off.</li>
                <li>All profiles of children are kept in the vehicle including contact info of parents and guardians.</li>
            </ul>
            <br>
            <p>If there is any safety item you feel we have overlooked please contact us and we will be glad to review it.</p>
            <br>
            <h1 class="text-center">When it comes to safety we do not compromise!</h1>
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