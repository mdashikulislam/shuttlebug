@extends('layouts.office')

@section('title')
    <title>Manage Bookings</title>
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

            @include('layouts.nav.nav-local-bookings')

        {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 mb-4 content" id="content">
                <div class="row">
                    <div class="col-md">
                        <h3 class="page-header">Manage Bookings</h3>
                    </div>

                    <div class="col-md">
                        @if ( session()->has('confirm') )
                            <div class="alert-success alert-temp">{{ session('confirm') }}</div>
                        @elseif ( session()->has('warning') )
                            <div class="alert-warning alert-temp">{{ session('warning') }}</div>
                        @elseif ( session()->has('danger') )
                            <div class="alert-danger alert-temp">{{ session('danger') }}</div>
                        @endif
                    </div>
                </div>
                <hr class="mt-2">

        {{-- shuttle booking -------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-sm-8 col-md-6 col-lg-5 col-xl-3">
                        <h5 class="mt-5">Add / Edit a Booking</h5>
                        <p class="text-muted ml-3 mb-0"><small>If the customer or passenger does not appear in the list, they may be marked as Inactive.</small></p>
                        <div class="ml-3">
                            <div class="mt-2">
                                {!! Form::select('customer', ['' => 'Select Customer'] +$customers, null, ['class' => 'form-control custom-select', 'id' => 'customer']) !!}
                            </div>
                            <div class="mt-1">
                                {!! Form::select('passenger', ['' => 'Select Passenger'] +$passengers, null, ['class' => 'form-control custom-select', 'id' => 'passengers']) !!}
                            </div>
                            <div class="mt-1">
                                {!! Form::text('date', null, ['class' => 'form-control datepicker', 'placeholder' => 'click for date', 'id' => 'date']) !!}
                            </div>
                            <div class="mt-1">
                                <button class="btn btn-outline-dark" id="editbooking">Book</button>
                            </div>
                        </div>

        {{-- duplicate bookings -------------------------------------------------------------------------------------}}

                        <h5 class="mt-5">Duplicate Bookings</h5>
                        <p class="text-muted ml-3 mb-0"><small>Select the customer and passenger then choose a date in the week that contains the bookings to duplicate.</small></p>
                        <div class="ml-3">
                            <div class="mt-2">
                                {!! Form::select('dup_customer', ['' => 'Select Customer'] +$list_customers, null, ['class' => 'form-control custom-select', 'id' => 'dup_customer']) !!}
                            </div>
                            <div class="mt-1">
                                {!! Form::select('dup_passenger', ['' => 'Select Passenger'] +$passengers, null, ['class' => 'form-control custom-select', 'id' => 'dup_passengers']) !!}
                            </div>
                            <div class="mt-1">
                                {!! Form::text('dup_date', null, ['class' => 'form-control datepicker', 'placeholder' => 'click for date', 'id' => 'dup_date']) !!}
                            </div>
                            <div class="mt-1">
                                <button class="btn btn-outline-dark" id="dupbooking">Duplicate</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-8 col-md-6 col-lg-5 col-xl-3 offset-lg-1">

        {{-- Email Bookings ----------------------------------------------------------------------------------------}}

                        <h5 class="mt-5">Email Bookings</h5>
                        <p class="text-muted ml-3 mb-0"><small>Email the bookings up to the end of next week to the customer.</small></p>
                        <form class="mt-2 ml-3">
                            <label class="sr-only" for="role">Date</label>
                            <div class="input-group">
                                {!! Form::select('email_customer', ['' => 'Select Customer'] +$email_customers, null, ['class' => 'form-control custom-select', 'id' => 'email_customer']) !!}
                                <div class="input-group-addon btn btn-outline-dark" id="emailbooking" role="button">
                                    <i class="fa fa-envelope"></i>
                                </div>
                            </div>
                        </form>


        {{-- view daily bookings -----------------------------------------------------------------------------------}}

                        <div class="d-none d-lg-block">
                            <h5 class="mt-5">List Daily Bookings</h5>
                            <p class="text-muted ml-3 mb-0"><small>Summary of bookings on selected date.</small></p>
                            <form class="mt-2 ml-3">
                                <label class="sr-only" for="role">Date</label>
                                <div class="input-group">
                                    {!! Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'dtdaily', 'placeholder' => 'date']) !!}
                                    <div class="input-group-addon btn btn-outline-dark" id="listdaily" role="button" data-route="/office/bookings/list/daily/">
                                        <i class="fa fa-list"></i>
                                    </div>
                                </div>
                            </form>
                        </div>

        {{-- view customer bookings -------------------------------------------------------------------------------}}

                        <div class="d-none d-lg-block">
                            <h5 class="mt-5">List Customer Bookings</h5>
                            <p class="text-muted ml-3 mb-0"><small>The list allows you to delete multiple bookings.</small></p>
                            <form class="mt-2 ml-3">
                                <label class="sr-only" for="role">Customer</label>
                                <div class="input-group">
                                    {!! Form::select('customer', ['' => 'Select Customer'] +$list_customers, null, ['class' => 'form-control custom-select', 'id' => 'customerlist']) !!}
                                    <div class="input-group-addon btn btn-outline-dark" id="listcustomer" role="button" data-route="/office/bookings/list/customer/">
                                        <i class="fa fa-list"></i>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{--<div class="d-none d-xl-block col-xl-3 offset-lg-1">--}}
                        {{--<h5 class="mt-5">Term Customers</h5>--}}
                        {{--<div class="text-muted small">--}}
                            {{--@foreach ( $prime as $customer )--}}
                                {{--{{ $customer }}<br>--}}
                            {{--@endforeach--}}
                        {{--</div>--}}

                        {{--<h5 class="mt-5">Weekly Customers</h5>--}}
                        {{--<div class="text-muted small">--}}
                            {{--@foreach ( $secondary as $customer )--}}
                                {{--{{ $customer }}<br>--}}
                            {{--@endforeach--}}
                        {{--</div>--}}
                    {{--</div>--}}
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
            {{-- get passengers for selected customer --}}
            $('#customer, #dup_customer').on('change', function() {
                let passid = $(this).attr('id') === 'customer' ? 'passengers' : 'dup_passengers';
                if ( $(this).val() > '' ) {
                    $.ajax({
                        type: "GET",
                        url: '/office/users/children/select/' + $(this).val(),
                        success: function (data) {
                            $('#' + passid).empty().html(data);
                        }
                    });
                } else {
                    $('#' + passid).empty().html('<option value="">Select Passenger</option>');
                }
            });

            {{-- edit booking --}}
            $('#editbooking').on('click', function() {
                window.location.href = '/office/bookings/edit/' + $('#passengers').val() + '/' + $('#date').val();
            });

            {{-- duplicate booking --}}
            $('#dupbooking').on('click', function() {
                window.location.href = '/office/bookings/duplicate/' + $('#dup_passengers').val() + '/' + $('#dup_date').val();
            });

            {{-- email bookings --}}
            $('#emailbooking').on('click', function() {
                window.location.href = '/office/bookings/email/' + $('#email_customer').val() + '/review';
            });

            {{-- get daily list --}}
            $('#listdaily').on('click', function () {
                let date = $('#dtdaily').val();
                if ( date > '' ) {
                    window.location.href = $(this).data('route') + date;
                }
            });

            {{-- get customer list --}}
            $('#listcustomer').on('click', function () {
                let customer = $('#customerlist').val();
                if ( customer > '' ) {
                    window.location.href = $(this).data('route') + customer;
                }
            });

            // search
            $('#findbtn').on('click', function () {
                let $find = encodeURIComponent($('#searchterm').val());
                $('#searchbody').load('/office/schools/find/' + $find, function () {
                    $('#showsearch').show();
                });
            });

            //close results pane
            $('#closesearch').on('click', function () {
                $('#showsearch').hide('slow');
            });
        });
    </script>
@stop