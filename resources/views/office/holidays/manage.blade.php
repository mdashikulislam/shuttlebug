@extends('layouts.office')

@section('title')
    <title>Manage Holidays</title>
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

            @include('layouts.nav.nav-local-holidays')

        {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 mb-4 content" id="content">
                <div class="row">
                    <div class="col-md">
                        <h3 class="page-header">Manage Holidays</h3>
                    </div>
                </div>
                <hr class="mt-2">

        {{-- info ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg-8 col-xl-6">
                        <h5 class="mt-4"><strong>Public Holidays</strong></h5>
                        <p class="text-muted ml-3">Before capturing bookings in a particular year, the public holidays for that year must be loaded into the database. Once loaded they can still be edited.</p>

                        <h6 class="mt-3 ml-3">
                            @if ( is_null($public) )
                                <a class="btn btn-outline-dark btn-sm" href="{!! url('office/holidays/public/edit') !!}">Add {{ now()->year }} Public Holidays</a>
                            @else
                                <a href="{!! url('office/holidays/public/edit') !!}" class="btn btn-outline-dark btn-sm" role="button">Edit {{ now()->year }} Public Holidays</a>
                            @endif
                            @if ( !is_null($public) && now()->month > 10 )
                                <a class="btn btn-outline-dark btn-sm ml-3" href="{!! url('office/holidays/public/edit/'.now()->addYear()->year) !!}">Add {{ now()->addYear()->year }} Public Holidays</a>
                            @endif
                        </h6>
                    </div>
                    <div class="col-lg-12"></div>

                    <div class="col-lg-8 col-xl-6">
                        <h5 class="mt-4"><strong>School Holidays</strong></h5>
                        <p class="text-muted ml-3">School holidays must also exist in the database for the year in which bookings are being captured. School holidays are created by loading the <u>school terms</u> for the year.</p>

                        <h6 class="mt-3 ml-3">
                            @if ( is_null($school) )
                                <a class="btn btn-outline-dark btn-sm" href="{!! url('office/holidays/edit') !!}">Add {{ now()->year }} School Terms</a>
                            @else
                                <a href="{!! url('office/holidays/edit') !!}" class="btn btn-outline-dark btn-sm" role="button">Edit {{ now()->year }} School Terms</a>
                            @endif
                            @if ( !is_null($school) && now()->month > 10 )
                                <a class="btn btn-outline-dark btn-sm ml-3" href="{!! url('office/holidays/edit/'.now()->addYear()->year) !!}">Add {{ now()->addYear()->year }} School Terrms</a>
                            @endif
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