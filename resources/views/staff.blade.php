@extends('layouts.front')

@section('title')
    <title>Staff</title>
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
            <h1 class="">Staff</h1>
            <p>Meet the people who arrange your shuttle service and take care of your children.</p>
            <br>
            <div class="card staff mb-3">
                <div class="card-body">
                    <h4 class="card-title">Lynne Ives</h4>
                    <img class="img-fluid float-left mr-3 mr-lg-5" src="{!! asset('/images/Lyn-Ives.jpg') !!}" alt="Staff">
                    <p>Lynne has been a resident of Hout Bay for the last 20 years. After raising her own children, she knew that this was the field for her. Lynne studied Montessori teaching at North American Montessori Centre. She holds a PDP license and has first aid certification from Frontline Emergency Care.</p>
                    <p>For over 18 years Lynn has been involved in child care activities in Hout Bay.</p>
                    <p>Lynne heads up the Shuttle Bug operation and her dedication drives the service's commitment to the care and safety of your children.</p>
                </div>
            </div>

            {{--<div class="card staff mb-3">--}}
                {{--<div class="card-body">--}}
                    {{--<h4 class="card-title">Fezile Sinxotho</h4>--}}
                    {{--<img class="img-fluid float-left mr-3 mr-lg-5" src="{!! asset('/images/fez1.jpg') !!}" alt="Staff">--}}
                    {{--<p>Fezile has a long career as a driver and has proven his professionalism and suitability for this position. He is an outstanding organiser; polite, caring and adored by the children.</p>--}}
                {{--</div>--}}
            {{--</div>--}}

            <div class="card staff mb-3">
                <div class="card-body">
                    <h4 class="card-title">Anelisiwe Tembani</h4>
                    <img class="img-fluid float-left mr-3 mr-lg-5" src="{!! asset('/images/nezi.jpg') !!}" alt="Staff">
                    <p>"Nezi" as the children refer to her, has worked with us for 4 years. She is a young, intelligent and dedicated member of the Shuttle Bug team.</p>
                    <p>Nezi is an assistant teacher at a nursery school (in the morning) and with her bubbly caring nature, she quickly endears herself to the children.</p>
                </div>
            </div>

            <div class="card staff mb-3">
                <div class="card-body">
                    <h4 class="card-title">Tumi</h4>
                    <img class="img-fluid float-left mr-3 mr-lg-5" src="{!! asset('/images/tumi.jpg') !!}" alt="Staff">
                    <p>Tumi has recently joined Shuttle Bug, she is friendly, presentable and very keen to get to know the children.</p>
                </div>
            </div>

            {{--<div class="card staff mb-3">--}}
                {{--<div class="card-body">--}}
                    {{--<h4 class="card-title">Fortunate Cele</h4>--}}
                    {{--<img class="img-fluid float-left mr-3 mr-lg-5" src="{!! asset('/images/fortunate.jpg') !!}" alt="Staff">--}}
                    {{--<p>Fortunate's lovely bubbly personality ensures she is always happy and friendly to the children.</p>--}}
                {{--</div>--}}
            {{--</div>--}}
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