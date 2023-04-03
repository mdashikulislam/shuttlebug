@extends('layouts.office')

@section('title')
    <title>Manage Event Bookings</title>
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

            @include('layouts.nav.nav-local-events')

        {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 mb-4 content" id="content">
                <div class="row">
                    <div class="col-md">
                        <h3 class="page-header">Manage Event Bookings</h3>
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

            {{-- event booking -------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-sm-8 col-md-6 col-lg-5 col-xl-3">
                        <h5 class="mt-5">Add / Edit an Event Booking</h5>
                        <p class="text-muted ml-3 mb-0"><small>If the customer does not appear in the list, they may be marked as Inactive.</small></p>
                        <div class="ml-3">
                            <div class="mt-2">
                                {!! Form::select('customer', ['' => 'Select Customer'] +$customers, null, ['class' => 'form-control custom-select', 'id' => 'customer']) !!}
                            </div>
                            <div class="mt-1">
                                {!! Form::text('date', null, ['class' => 'form-control datepicker', 'placeholder' => 'click for date', 'id' => 'date']) !!}
                            </div>
                            <div class="mt-1">
                                <button class="btn btn-outline-dark" id="editbooking">Book</button>
                            </div>
                        </div>
                    </div>
                </div>

            {{-- Email Bookings ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-sm-8 col-md-6 col-lg-5 col-xl-3">
                        <h5 class="mt-5">Email Event Bookings</h5>
                        <p class="text-muted ml-3 mb-0"><small>Email event bookings to the customer.</small></p>
                        <form class="mt-2 ml-3">
                            <label class="sr-only" for="role">Customer</label>
                            <div class="input-group">
                                {!! Form::select('email_customer', ['' => 'Select Customer'] +$email_customers, null, ['class' => 'form-control custom-select', 'id' => 'email_customer']) !!}
                                <div class="input-group-addon btn btn-outline-dark" id="emailbooking" role="button">
                                    <i class="fa fa-envelope"></i>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            {{-- view customer bookings -------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-sm-8 col-md-6 col-lg-5 col-xl-3">
                        <div class="d-none d-lg-block">
                            <h5 class="mt-5">List Customer Event Bookings</h5>
                            <p class="text-muted ml-3 mb-0"><small>The list allows you to delete multiple bookings.</small></p>
                            <form class="mt-2 ml-3">
                                <label class="sr-only" for="role">Customer</label>
                                <div class="input-group">
                                    {!! Form::select('customer', ['' => 'Select Customer'] +$list_customers, null, ['class' => 'form-control custom-select', 'id' => 'customerlist']) !!}
                                    <div class="input-group-addon btn btn-outline-dark" id="listcustomer" role="button" data-route="/office/events/list/customer/">
                                        <i class="fa fa-list"></i>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
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

            {{-- edit booking --}}
            $('#editbooking').on('click', function() {
                window.location.href = '/office/events/edit/' + $('#customer').val() + '/' + $('#date').val();
            });

            {{-- email bookings --}}
            $('#emailbooking').on('click', function() {
                window.location.href = '/office/events/email/' + $('#email_customer').val() + '/review';
            });

            {{-- get customer list --}}
            $('#listcustomer').on('click', function () {
                let customer = $('#customerlist').val();
                if ( customer > '' ) {
                    window.location.href = $(this).data('route') + customer;
                }
            });
        });
    </script>
@stop