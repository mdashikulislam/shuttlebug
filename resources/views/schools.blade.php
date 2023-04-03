@extends('layouts.front')

@section('title')
    <title>Schools</title>
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
            <h1 class="">Schools</h1>
            <div class="row">
                <div class="col col-md-6">
                    <p>Regular shuttles run daily at various times from the following schools:</p>
                    <ul>
                        <li>Hout Bay International</li>
                        <li>Llandudno Primary</li>
                        <li>My Montessori</li>
                        <li>Valley Prep</li>
                    </ul>
                </div>
                <div class="col col-md-6">
                    <p>On-demand shuttles are also available to and from <em>all other schools</em> in Hout Bay, including:</p>
                    <ul>
                        <li>Kronendal Primary</li>
                        <li>Hout Bay Montessori</li>
                        <li>Klein Leeukop</li>
                        <li>Bisschop House</li>
                    </ul>
                    and many more
                </div>
            </div>
            <br>
            <br>
            <p>If your school is not listed <a href="{!! url('contact') !!}">contact us</a> to enquire about providing a shuttle service.</p>
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