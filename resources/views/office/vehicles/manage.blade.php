@extends('layouts.office')

@section('title')
    <title>Manage Vehicles</title>
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

            @include('layouts.nav.nav-local-vehicles')

        {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 mb-4 content" id="content">
                <div class="row">
                    <div class="col-md">
                        <h3 class="page-header">Manage Vehicles & Attendants</h3>
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

        {{-- search vehicle ----------------------------------------------------------------------------------------}}

                <h5 class="mt-4">Find a Vehicle</h5>
                <p class="text-muted ml-3">Enter any part of a vehicle's model or registration.</p>
                <div class="row">
                    <form class="col-sm-8 col-md-6 col-lg-4 col-xl-3 ml-3">
                        <label class="sr-only" for="searchterm">Name</label>
                        <div class="input-group">
                            <input class="form-control" type="text" placeholder="search anything" id="vehicleterm">
                            <div class="input-group-addon btn btn-outline-dark" id="findvehicle" role="button">
                                <i class="fa fa-search"></i>
                            </div>
                        </div>
                    </form>
                </div>

        {{-- vehicle results ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-md-10 col-xl-6">
                        <div id="showvehicle" class="card ml-3 mt-4" style="display:none">
                            <div class="card-header">
                                <h5 class="card-title">
                                    Matching Vehicles
                                    <button type="button" class="closesearch btn btn-outline-dark btn-sm float-right">
                                        <i aria-hidden="true" class="fa fa-close"></i>
                                    </button>
                                </h5>
                                <small class="card-text text-muted">Click on the Vehicle to open/edit the profile.</small>
                            </div>
                            <div id="vehiclebody" class="card-body">
                                {{--ajax view here--}}
                            </div>
                        </div>
                    </div>
                </div>

        {{-- add vehicle ----------------------------------------------------------------------------------------}}

                <h5 class="mt-5">
                    <a class="btn btn-outline-dark" href="{!! url('office/operations/vehicles/create') !!}">Add a New Vehicle</a>
                </h5>

        {{-- add driver ----------------------------------------------------------------------------------------}}

                {{--<h5 class="mt-5">--}}
                    {{--<a class="btn btn-outline-dark" href="{!! url('office/operations/drivers/edit') !!}">Add a New Driver</a>--}}
                {{--</h5>--}}

        {{-- add attendant ------------------------------------------------------------------------------------}}

                <h5 class="mt-5">
                    <a class="btn btn-outline-dark" href="{!! url('office/operations/attendants/edit') !!}">Add a New Attendant</a>
                </h5>
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
            // search vehicle
            $('#findvehicle').on('click', function () {
                let $find = encodeURIComponent($('#vehicleterm').val());
                $('#vehiclebody').load('/office/operations/vehicles/find/' + $find, function () {
                    $('#showvehicle').show();
                });
            });

            //close results pane
            $('.closesearch').on('click', function () {
                $('#showvehicle').hide('slow');
            });
        });
    </script>
@stop