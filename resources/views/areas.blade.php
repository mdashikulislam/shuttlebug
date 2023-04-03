@extends('layouts.front')

@section('title')
    <title>Areas</title>
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
            <h1 class="">Areas</h1>
            <p>The service is available to residents living in the areas listed below.</p>
            <h2>Hout Bay, Llandudno</h2>
            {{--<h4 class="pl-3">Hout Bay, Llandudno</h4>--}}
            {{--<p class="pl-3">We also offer a limited service within Cape Town Southern Suburbs. The service was primarily introduced because of the demand in Hout Bay for an 'Over The Mountain' service. As a result we do provide a service for certain schools at certain times. Please <a href="{!! url('contact') !!}">contact us</a> to find out if we are able to assist you.</p>--}}

            <p>Shuttles are available between home, school and extramural venues. This includes early morning shuttles from home to school and lifts during the day from school to home or extramural activities.</p>

            <p>In addition, a limited service is available between Hout Bay and certain venues in; </p>
            <h2>Camps Bay, Constantia, Plumstead, Wynberg and Claremont</h2>
            <p><small>See Schools for more information</small></p>

                {{--Shuttles might also be available to schools immediately adjacent to the area but this will depend on the distance.</p>--}}
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