@extends('layouts.front')

@section('title')
    <title>Contact Shuttle Bug</title>
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

        <section class="article pt-5">
            <h1 class="pb-5">Contact Us</h1>
            <div class="row">
                <div class="col-md">
                    <h2>Call</h2>
                    <p>Speak to Lyn about your enquiry.</p>
                    <ul>
                        <li>082 386 9723</li>
                    </ul>
                </div>
                <div class="col-md">
                    <h2>Email</h2>
                    <p>Send your enquiry to one of these addresses.</p>
                    <dl class="row pl-4">
                        <dt class="col-sm-3">Information</dt>
                        <dd class="col-sm-9"><a href="mailto:lyn@shuttlebug.co.za">lyn@shuttlebug.co.za</a></dd>
                        <dt class="col-sm-3">Bookings</dt>
                        <dd class="col-sm-9"><a href="mailto:bookings@shuttlebug.co.za">bookings@shuttlebug.co.za</a></dd>
                        <dt class="col-sm-3">Accounts</dt>
                        <dd class="col-sm-9"><a href="mailto:admin.hb@shuttlebug.co.za">admin.hb@shuttlebug.co.za</a></dd>
                    </dl>
                </div>
            </div>
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