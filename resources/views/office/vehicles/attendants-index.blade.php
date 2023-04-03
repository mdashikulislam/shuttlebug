@extends('layouts.office')

@section('title')
    <title>Attendants Listing</title>
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

                {{-- toolbar ----------------------------------------------------------------------------------------}}

                {{--@include('office.vehicles.toolbar')--}}

                {{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg-4 col-xl-3">
                        <h3>Attendants <small id="records"></small></h3>
                    </div>

                    {{-- buttons -----------------------------------------------------------------------------------}}

                    <div class="col-lg-8 col-xl-5">
                        <a href="{!! url('office/operations/attendants/edit') !!}" class="btn btn-outline-dark" role="button">Add Attendant</a>
                        <button class="btn btn-outline-dark call-edit" data-route="{!! url('office/operations/attendants/edit') !!}" disabled>
                            Edit Attendant
                        </button>
                    </div>

                    {{-- messages ----------------------------------------------------------------------------------}}

                    <div class="col-xl-4">
                        @if ( session()->has('confirm') )
                            <div class="alert-success alert-temp">{{ session('confirm') }}</div>
                        @elseif ( session()->has('warning') )
                            <div class="alert-warning alert-temp">{{ session('warning') }}</div>
                        @elseif ( session()->has('danger') )
                            <div class="alert-danger alert-temp">{{ session('danger') }}</div>
                        @endif
                    </div>
                </div>

                {{-- table ----------------------------------------------------------------------------------------}}

                <table class="dtable display mt-3" data-order='' cellspacing="0" width="100%" id="index-table">
                    <thead>
                    <tr title="Click column heading to sort table">
                        <th class="id">Login Code</th>
                        <th>Name</th>
                        <th>Working Hours</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ( $attendants as $attendant )
                        <tr>
                            <td class="id">{{ $attendant->id }}</td>
                            <td>{{ $attendant->first_name.' '.$attendant->last_name }}</td>
                            <td>{{ substr($attendant->from,0,5) }} - {{ substr($attendant->to,0,5) }}</td>
                            <td>{{ $attendant->role }}</td>
                            <td>{{ $attendant->status }}</td>
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