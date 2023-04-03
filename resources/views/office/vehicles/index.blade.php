@extends('layouts.office')

@section('title')
    <title>Vehicle Listing</title>
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

            <section class="col-lg col-12 pa-1 content d-none d-lg-block" id="content">

                <div class="row">
                    <div class="col-lg-4 col-xl-3">
                        <h3>Vehicles <small id="records"></small></h3>
                    </div>

                    {{-- buttons ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-8 col-xl-5">
                        <a href="{!! url('office/operations/vehicles/create') !!}" class="btn btn-outline-dark" role="button">Add Vehicle</a>
                        <button class="btn btn-outline-dark call-edit" data-route="{!! url('office/operations/vehicles/edit') !!}" disabled>
                            Edit Vehicle
                        </button>
                    </div>

                    {{-- messages ----------------------------------------------------------------------------------------}}

                    <div class="col-xl-4">
                        @if ( session()->has('confirm') )
                            <div class="alert-success alert-temp">{{ session('confirm') }}</div>
                        @elseif ( session()->has('warning') )
                            <div class="alert-warning alert-temp">{{ session('warning') }}</div>
                        @elseif ( session()->has('danger') )
                            <div class="alert-danger alert-temp">{{ session('danger') }}</div>
                        @endif
                    </div>

                    <div class="col-lg-6 col-xl-5 text-muted mt-3">
                        <p>The vehicles list includes all vehicles that can carry 4 or more passengers.<br>
                            Smaller vehicles are not added to the list.<br>
                            If a new 4+ seater is added to the fleet it should be added to this list.</p>
                    </div>
                </div>

                {{-- table ----------------------------------------------------------------------------------------}}

                <table class="dtable display mt-3" data-order='[[ 1, "asc" ]]' cellspacing="0" width="100%" id="index-table">
                    <thead>
                    <tr title="Click column heading to sort table">
                        <th class="id">Id</th>
                        <th>Model</th>
                        <th>Seats</th>
                        <th>Status</th>
                        <th>Type</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ( $vehicles as $vehicle )
                        <tr>
                            <td class="id">{{ $vehicle->id }}</td>
                            <td>{{ $vehicle->model }}</td>
                            <td>{{ $vehicle->seats }}</td>
                            <td>{{ $vehicle->status }}</td>
                            <td>{{ $vehicle->primary ? 'Shuttle Bug' : 'Homeheroes' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
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
        let table;

        $(function() {
            {{-- datatable --}}
            table = $('#index-table').DataTable( {"dom": 'lrtip'} );
            table.on('select', function ( e, dt, type, indexes ) {
                handleRowSelect(table.rows( indexes ).data().toArray()[0][0]);
            }).on('deselect', function () {
                handleRowDeselect();
            });
            $('#records').html('(' + table.page.info().recordsDisplay + ')');

            {{-- switch away from index page when window re-sized to md --}}
            $( window ).resize(function() {
                if ($(window).width() < 990) {
                    window.location.href = '/office/operations/vehicles';
                }
            });
        });
    </script>
@stop