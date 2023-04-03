@extends('layouts.office')

@section('title')
    <title>promotions Listing</title>
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

            <section class="col-lg col-12 pa-1 content d-none d-lg-block" id="content">

                {{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg-4 col-xl-3">
                        <h3>Promotion Prices</h3>
                    </div>

                    {{-- buttons ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-8 col-xl-5">
                        <a href="{!! url('office/prices/promotion/edit') !!}" class="btn btn-outline-dark" role="button">New Promotion</a>
                        <button class="btn btn-outline-dark call-edit" data-route="{!! url('office/prices/promotion/edit') !!}" disabled>Edit Selected Promotion</button>
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

                <table class="dtable display mt-3" data-order='' cellspacing="0" width="100%" id="index-table">
                    <thead>
                    <tr title="Click column heading to sort table">
                        <th class="id">Id</th>
                        <th>Status</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Effective Date</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($future as $price)
                            <tr class="text-danger">
                                <td class="id">{{ $price->id }}</td>
                                <td>Future Price</td>
                                <td>{{ $price->name }}</td>
                                <td>{{ $price->rate }}</td>
                                <td>{{ $price->start }} - {{ $price->expire }}</td>
                            </tr>
                        @endforeach
                        @foreach($existing as $price)
                            <tr class="{{ $price->expire >= now() ? '' : 'text-muted' }}">
                                <td class="id">{{ $price->id }}</td>
                                <td>
                                    @if ($price->expire >= now())
                                        Current Price
                                    @else
                                        <small>expired</small>
                                    @endif
                                </td>
                                <td>{{ $price->name }}</td>
                                <td>{{ $price->rate }}</td>
                                <td>{{ $price->start }} - {{ $price->expire }}</td>
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
                {{-- hijack the back.js function to apply this only to future prices --}}
                let row = table.rows( indexes ).data().toArray();
                if ( row[0][1] === 'Future Price' ) {
                    rowselected = row[0][0];
                    $('.call-edit').prop('disabled', false);
                }
            }).on('deselect', function () {
                handleRowDeselect();
            });

            {{-- switch away from index page when window re-sized to md --}}
            $( window ).resize(function() {
                if ($(window).width() < 990) {
                    window.location.href = '/office/prices';
                }
            });
        });
    </script>
@stop