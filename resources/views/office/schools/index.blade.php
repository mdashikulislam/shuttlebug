@extends('layouts.office')

@section('title')
    <title>School Listing</title>
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

            @include('layouts.nav.nav-local-schools')

            {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-lg col-12 pa-1 content d-none d-lg-block" id="content">

                {{-- toolbar ----------------------------------------------------------------------------------------}}

                @include('office.schools.toolbar')

                {{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg-4 col-xl-3">
                        <h3>{{ is_null($listing) ? 'Schools' : 'Inactive Schools' }} <small id="records"></small></h3>
                    </div>

                    {{-- buttons ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-8 col-xl-5">
                        <a href="{!! url('office/schools/create') !!}" class="btn btn-outline-dark" role="button">Add School</a>
                        <button class="btn btn-outline-dark call-edit" data-route="{!! url('office/schools/edit') !!}" disabled>
                            Edit School
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
                </div>

                {{-- table ----------------------------------------------------------------------------------------}}

                <table class="dtable display mt-3" data-order='[[ 1, "asc" ]]' cellspacing="0" width="100%" id="index-table">
                    <thead>
                    <tr title="Click column heading to sort table">
                        <th class="id">Id</th>
                        <th>Name</th>
                        <th>City</th>
                        <th>Suburb</th>
                        <th>Open</th>
                        <th>Start</th>
                        <th>Active Children</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ( $schools as $school )
                        <tr>
                            <td class="id">{{ $school->id }}</td>
                            <td>{{ $school->name }}</td>
                            <td>{{ $school->city }}</td>
                            <td>{{ $school->suburb }}</td>
                            <td>{{ substr($school->dropfrom,0,5) }}</td>
                            <td>{{ substr($school->dropby,0,5) }}</td>
                            <td>{{ $school->children_count }}</td>
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

            {{-- filter --}}
            $('.filter').multifilter();
            @if ( session()->has('schoolfilter') )
            @foreach ( session('schoolfilter') as $filter => $value )
            @php $selector = '.'.$filter.'filter'; @endphp
            $("{{ $selector }}").val("{!! $value !!}").trigger('change');
            @endforeach
            @endif

            {{-- switch away from index page when window re-sized to md --}}
            $( window ).resize(function() {
                if ($(window).width() < 990) {
                    window.location.href = '/office/schools';
                }
            });
        });
    </script>
@stop