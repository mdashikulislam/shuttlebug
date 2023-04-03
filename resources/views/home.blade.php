@extends('layouts.front')

@section('title')
    <title>Welcome to Shuttle Bug</title>
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

        <section class="text-center">
            <div class="slide">
                <img class="" src="{!! asset('/images/slide6.jpg') !!}" alt="safety is our priority">
                <div class="promo-tag">
                    <p><strong>Safety</strong> <b>is our priority</b></p>
                </div>
            </div>
        </section>

        <section class="article pt-5">
            <h2 class="">Welcome to Shuttle Bug</h2>
            <p>Shuttle Bug is a convenient, safe and professional service that specialises in the transportation of children in Hout Bay and Llandudno.</p>
            <p>We understand the special requirements needed for caring for children and have made sure the service is perfect for this purpose.</p>
            <br>
            <img class="img-fluid float-right mb-3 mr-lg-5" src="{!! asset('/images/safety2.jpg') !!}" alt="School transport for children in Hout Bay">

            <h2>The Service</h2>
            <p>Provides a safe, professional management and monitoring process for your child's transport.</p>
            <ul style="line-height:1.8">
                <li>Available between home, school and extramural venues within the service area.</li>
                <li>Arrival and departure times at schools and extramural locations are set by you.</li>
                <li>Arrival and departure times at home are determined by the journey time.</li>
                <li>Caters for flexible ad-hoc and scheduled bookings which are easily updated.</li>
                <li>Our route planning ensures children spend the minimum possible time travelling.</li>
            </ul>

            <h2>The Attendant</h2>
            <p>Providing care and assistance to the child.</p>
            <ul style="line-height:1.8">
                <li>Safely gathers children together at busy pickup venues.</li>
                <li>Accompanies each child between the vehicle and venue.</li>
                <li>Takes care of all their needs during the drive.</li>
                <li>Ensures that each child is comfortable and buckled up properly.</li>
                <li>Allows the driver to concentrate on the task at hand without interruption.</li>
                <li>Is in contact with the office at all times.</li>
            </ul>
            <img class="img-fluid float-right mr-lg-5 mb-3" src="{!! asset('/images/safety3.jpg') !!}" alt="transport between home and school for children in Hout Bay and Llandudno">

            <h2>The Driver</h2>
            <p>Dedicated and responsible professionals focused on safety.</p>
            <ul style="line-height:1.8">
                <li>Drivers are selected on experience and suitability.</li>
                <li>Full background checks are done on all staff.</li>
                <li>Drivers are required to hold professional driving licenses.</li>
            </ul>

            <h2>The Vehicle</h2>
            <p>Fully compliant and managed by a professional fleet management operation.</p>
            <ul style="line-height:1.8">
                <li>Vehicles are meticulously maintained and inspected.</li>
                <li>Each vehicle is equipped with car and booster seats for younger children.</li>
                <li>Every vehicle meets regulatory requirements and carries appropriate permits.</li>
                <li>Backup vehicles are available in case of emergency.</li>
                <li>We are in continual contact with each vehicle.</li>
            </ul>

            <div><br></div>
            <p>We have implemented many extra precautions and measures to keep your children safe and on time.</p>
            <p>We understand that we are transporting your most precious possessions and make no compromise whatsoever in the quality of care.</p>
            <br>
            <h1 class="text-center">We will exceed your every expectation!</h1>
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