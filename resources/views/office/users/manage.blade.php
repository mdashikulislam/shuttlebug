@extends('layouts.office')

@section('title')
    <title>Manage Customers</title>
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

            @include('layouts.nav.nav-local-users')

        {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 mb-4 content" id="content">
                <div class="row">
                    <div class="col-md">
                        <h3 class="page-header">Manage Customers</h3>
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

        {{-- search ----------------------------------------------------------------------------------------}}

                <h5 class="mt-4">Find a Customer</h5>
                <p class="text-muted ml-3">Enter any part of a customer's id, name, email, suburb or child's id or name.</p>
                <div class="row">
                    <form class="col-sm-8 col-md-6 col-lg-4 col-xl-3 ml-3">
                        <label class="sr-only" for="searchterm">Name</label>
                        <div class="input-group">
                            <input class="form-control" type="text" placeholder="search anything" id="searchterm">
                            <div class="input-group-addon btn btn-outline-dark" id="findbtn" role="button">
                                <i class="fa fa-search"></i>
                            </div>
                        </div>
                    </form>
                </div>

        {{-- search results ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-md-10 col-xl-6">
                        <div id="showsearch" class="card ml-3 mt-4" style="display:none">
                            <div class="card-header">
                                <h5 class="card-title">
                                    Matching Customers
                                    <button id="closesearch" type="button" class="btn btn-outline-dark btn-sm float-right">
                                        <i aria-hidden="true" class="fa fa-close"></i>
                                    </button>
                                </h5>
                                <small class="card-text text-muted">Click on the Customer to open/edit their profile.</small>
                            </div>
                            <div id="searchbody" class="card-body">
                                {{--ajax view here--}}
                            </div>
                        </div>
                    </div>
                </div>

        {{-- add customer ----------------------------------------------------------------------------------------}}

                <h5 class="mt-5">
                    <a class="btn btn-outline-dark" href="{!! url('office/users/customers/create') !!}">Add a New Customer</a>
                </h5>

        {{-- children photos -------------------------------------------------------------------------------------}}

                <h5 class="mt-5">
                    <a class="btn btn-outline-dark" href="{!! url('office/users/children/photos') !!}">Children Photos</a>
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
            // search
            $('#findbtn').on('click', function () {
                let $find = encodeURIComponent($('#searchterm').val());
                $('#searchbody').load('/office/users/find/' + $find, function () {
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