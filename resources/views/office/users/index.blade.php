@extends('layouts.office')

@section('title')
    <title>Customer Listing</title>
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

            <section class="col-lg col-12 pa-1 content d-none d-lg-block" id="content">

                {{-- toolbar ----------------------------------------------------------------------------------------}}

                @include('office.users.toolbar')

                {{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg-4 col-xl-3">
                        <h3>{{ is_null($listing) ? 'Customers' : 'Inactive Customers' }} <small id="records"></small></h3>
                    </div>

                    {{-- buttons ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-8 col-xl-5">
                        <a href="{!! url('office/users/customers/create') !!}" class="btn btn-outline-dark" role="button">Add Customer</a>
                        <button class="btn btn-outline-dark call-edit" data-route="{!! url('office/users/customers/edit') !!}" disabled>
                            Edit Customer
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
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>Suburb</th>
                        <th>Children</th>
                        @if ( !is_null($listing) )
                            <th>Activate</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ( $customers as $customer )
                        <tr>
                            <td class="id">{{ $customer->id }}</td>
                            <td>{{ $customer->alpha_name }}</td>
                            <td>{{ $customer->mobile }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->suburb }}</td>
                            <td><small>
                                @foreach ( $customer->children as $child )
                                    {{ $child->name }},
                                @endforeach
                            </small></td>
                            @if ( !is_null($listing) )
                                <td><a href="{!! url('office/users/customers/activate', [$customer->id]) !!}"><i class="fa fa-unlock"></i></a></td>
                            @endif
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
            @if ( session()->has('userfilter') )
                @foreach ( session('userfilter') as $filter => $value )
                    @php $selector = '.'.$filter.'filter'; @endphp
                    $("{{ $selector }}").val("{!! $value !!}").trigger('change');
                @endforeach
            @endif

            {{-- switch away from index page when window re-sized to md --}}
            $( window ).resize(function() {
                if ($(window).width() < 990) {
                    window.location.href = '/office/users';
                }
            });
        });
    </script>
@stop