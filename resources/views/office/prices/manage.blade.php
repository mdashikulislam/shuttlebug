@extends('layouts.office')

@section('title')
    <title>Manage Prices</title>
@stop

@section('css')
    @parent
@stop

@section('style')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-prices')

        {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 mb-4 content" id="content">
                <div class="row">
                    <div class="col-md">
                        <h3 class="page-header">Manage Prices & Promotions</h3>
                    </div>
                </div>
                <hr class="mt-2">

        {{-- info ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg-8 col-xl-6">
                        <h5 class="mt-4"><strong>Standard Prices</strong></h5>
                        <p class="text-muted ml-3">This price is applied to all bookings that do not qualify for a Special Price or Promotion.<br>The current Standard Price cannot be changed but a new Standard Price can be added to become effective on some future date. All new bookings as well as bookings already in the system will automatically get the new price from that future date.</p>

                        <h6 class="mt-3 ml-3 d-lg-none">
                            @if ( is_null($standard) )
                                <a class="btn btn-outline-dark btn-sm" href="{!! url('office/prices/edit') !!}">Add New Standard Price</a>
                            @else
                                <a href="{!! url('office/prices/edit/'.$standard->id) !!}" class="btn btn-outline-dark btn-sm" role="button">Edit Future Standard Price</a>
                            @endif
                        </h6>
                    </div>
                    <div class="col-lg-12"></div>

                    <div class="col-lg-8 col-xl-6">
                        <h5 class="mt-4"><strong>Special Prices</strong></h5>
                        <p class="text-muted ml-3">Special Prices replace the Standard Price when a booking meets certain criteria. This allows special prices to be created for various types of bookings e.g. VIP clients, Short trips etc.<br>Existing Special Prices should be updated whenever the Standard Price is revised and is done in the same way i.e. by adding a new price effective on some future date.<br>Introducing new Special Prices requires programming so can only be done by the webmaster.</p>

                        @foreach ( $special as $spec )
                            <h6 class="mt-3 ml-3 d-lg-none">
                                <a class="btn btn-outline-dark btn-sm" href="{!! url('office/prices/special/edit/'.$spec->id) !!}">Edit {{ $spec->name }} Price</a>
                            </h6>
                        @endforeach
                        <h6 class="mt-3 ml-3 d-lg-none">
                            <a class="btn btn-outline-dark btn-sm" href="{!! url('office/prices/special/edit') !!}">Add New Special Price</a>
                        </h6>
                    </div>
                    <div class="col-lg-12"></div>

                    <div class="col-lg-8 col-xl-6">
                        <h5 class="mt-4"><strong>Promotions</strong></h5>
                        <p class="text-muted ml-3">Promotions work in the same way as Special Prices. The Promotion Price will replace the Standard Price or Special Price when the booking meets the promotion criteria. The difference is that Promotions are short duration 'specials' and the Promotion Price will automatically fall away when the promotion expires.<br>Adding a new promotion can only be done by the webmaster.</p>

                        @foreach ( $promotion as $promo )
                            <h6 class="mt-3 ml-3 d-lg-none">
                                <a class="btn btn-outline-dark btn-sm" href="{!! url('office/prices/promotion/edit/'.$promo->id) !!}">Edit {{ $promo->name }}</a>
                            </h6>
                        @endforeach
                        <h6 class="mt-3 ml-3 d-lg-none">
                            <a class="btn btn-outline-dark btn-sm" href="{!! url('office/prices/promotion/edit') !!}">Add New Promotion</a>
                        </h6>
                    </div>
                    <div class="col-lg-12"></div>
                </div>
            </section>
        </div>
    </div>
@stop

@section('script')
    @parent
@stop


@section('jquery')
    @parent
    <script>
        $(function() {

        });
    </script>
@stop