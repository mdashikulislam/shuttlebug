@extends('layouts.front')

@section('title')
    <title>Prices</title>
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
            <h1 class="">Prices</h1>
            <p>Transporting children requires greater safety measures than when transporting adults. However, we understand that the service needs to be financially viable for parents. We keep our prices as low as possible without compromising safety standards or the sustainability of the service.</p>
            <br>
            <div class="row">
                <div class="col-lg-8">

            {{-- basic rate ----------------------------------------------------------------------------------------}}

                    <div class="card staff mb-3">
                        <div class="card-body">
                            <div class="card-title d-flex justify-content-between">
                                <h4>Basic Rate</h4>
                                <span>R {{ $prices->basic_rate }}</span>
                            </div>
                            @if ( !is_null($new_prices) )
                                <div class="card-title d-flex justify-content-between text-warning">
                                    <span>As From {{ Carbon\Carbon::parse($new_prices->date)->format('d F Y') }} :</span>
                                    <span>R {{ $new_prices->basic_rate }}</span>
                                </div>
                            @endif
                            <div class="text-center">
                                <p>The Basic Rate is the cost per shuttle per child for venues in Hout Bay & Llandudno. A shuttle is a trip between departure point and destination e.g. home to school.</p>
                            </div>
                        </div>
                    </div>

            {{-- volume discount -----------------------------------------------------------------------------------}}

                    <div class="card staff mb-3">
                        <div class="card-body">
                            <div class="card-title d-flex justify-content-between">
                                <h4>Volume Discount</h4>
                                <span>{{ (int) $prices->volume_disc }} %</span>
                            </div>
                            @if ( !is_null($new_prices) && $new_prices->volume_disc != $prices->volume_disc )
                                <div class="card-title d-flex justify-content-between text-warning">
                                    <span>As From {{ Carbon\Carbon::parse($new_prices->date)->format('d F Y') }} :</span>
                                    <span>{{ $new_prices->volume_disc }} %</span>
                                </div>
                            @endif
                            <div class="text-center">
                                <p>Every child booked on more than 15 shuttles a month automatically receives the Volume discount.</p>
                            </div>
                        </div>
                    </div>

            {{-- sibling discount -----------------------------------------------------------------------------------}}

                    @if ( $prices->sibling_disc > 0 )
                        <div class="card staff mb-3">
                            <div class="card-body">
                                <div class="card-title d-flex justify-content-between">
                                    <h4>Sibling Discount</h4>
                                    <span>{{ (int) $prices->sibling_disc }} %</span>
                                </div>
                                @if ( !is_null($new_prices) && $new_prices->sibling_disc != $prices->sibling_disc )
                                    <div class="card-title d-flex justify-content-between text-warning">
                                        <span>As From {{ Carbon\Carbon::parse($new_prices->date)->format('d F Y') }} :</span>
                                        <span>{{ $new_prices->sibling_disc }} %</span>
                                    </div>
                                @endif
                                <div class="text-center">
                                    <p>Discount for each additional sibling sharing a shuttle between the same venues at the same time.</p>
                                </div>
                            </div>
                        </div>
                    @endif

            {{-- special prices ------------------------------------------------------------------------------------}}

                    @foreach ( $specials as $special )
                        @if ( $special->start <= now() )
                            <div class="card staff mb-3">
                                <div class="card-body">
                                    <div class="card-title d-flex justify-content-between">
                                        <h4>{{ $special->name }}</h4>
                                        <span>R {{ $special->rate }}</span>
                                    </div>
                                    @php
                                        $new_special = $specials->where('name', $special->name)->where('start', '>', now())->first();
                                    @endphp
                                    @if ( !is_null($new_special) )
                                        <div class="card-title d-flex justify-content-between text-warning">
                                            <span>As From {{ Carbon\Carbon::parse($new_special->start)->format('d F Y') }} :</span>
                                            <span>R {{ $new_special->rate }}</span>
                                        </div>
                                    @endif
                                    <div class="text-center">
                                        <p>{!! nl2br($special->description) !!}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

            {{-- current promotions -------------------------------------------------------------------------------}}

                    @foreach ( $promos as $promo )
                        <div class="card staff mb-3">
                            <div class="card-body">
                                <div class="card-title d-flex justify-content-between">
                                    <h4>{{ $promo->name }}</h4>
                                    <span>R {{ $promo->rate }}</span>
                                </div>
                                <p class="text-warning">( Expires: {{ Carbon\Carbon::parse($promo->expire)->format('d F Y') }} )</p>
                                <div class="text-center">
                                    <p>{{ $promo->description }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach

            {{-- new promotions -----------------------------------------------------------------------------------}}

                    @foreach ( $new_promos as $promo )
                        <div class="card staff mb-3">
                            <div class="card-body">
                                <div class="card-title d-flex justify-content-between">
                                    <h4>{{ $promo->name }}</h4>
                                    <span>R {{ $promo->rate }}</span>
                                </div>
                                <p class="text-warning">( Promotion Between: {{ Carbon\Carbon::parse($promo->start)->format('d F Y') }} - {{ Carbon\Carbon::parse($promo->expire)->format('d F Y') }} )</p>
                                <div class="text-center">
                                    <p>{{ $promo->description }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach

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