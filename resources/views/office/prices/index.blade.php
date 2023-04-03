@extends('layouts.office')

@section('title')
    <title>Price Listing</title>
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
                        <h3>Standard Prices</h3>
                    </div>

                    {{-- buttons ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-8 col-xl-5">
                        @if ( is_null($future) )
                            <a href="{!! url('office/prices/edit') !!}" class="btn btn-outline-dark" role="button">Add Future Price</a>
                        @else
                            <a href="{!! url('office/prices/edit/'.$future->id) !!}" class="btn btn-outline-dark" role="button">Edit Future Price</a>
                        @endif
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
                        <th>Effective Date</th>
                        <th>Price</th>
                        <th>Sibling Disc</th>
                        <th>Volume Disc</th>
                        <th>Homeheroes</th>
                    </tr>
                    </thead>
                    <tbody>
                        @if (!is_null($future) )
                            <tr class="text-danger">
                                <td class="id">{{ $future->id }}</td>
                                <td>Future Price</td>
                                <td>{{ $future->date }}</td>
                                <td>{{ $future->basic_rate }}</td>
                                <td>{{ $future->sibling_disc }}</td>
                                <td>{{ $future->volume_disc }}</td>
                                <td>{{ $future->hh }}</td>
                            </tr>
                        @endif
                        @foreach($existing as $price)
                            <tr class="{{ $loop->first ? '' : 'text-muted' }}">
                                <td class="id">{{ $price->id }}</td>
                                <td>
                                    @if ($loop->first)
                                        Current Price
                                    @else
                                        <small>expired</small>
                                    @endif
                                </td>
                                <td>{{ $price->date }}</td>
                                <td>{{ $price->basic_rate }}</td>
                                <td>{{ $price->sibling_disc }}</td>
                                <td>{{ $price->volume_disc }}</td>
                                <td>{{ $price->hh }}</td>
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

            {{-- switch away from index page when window re-sized to md --}}
            $( window ).resize(function() {
                if ($(window).width() < 990) {
                    window.location.href = '/office/prices';
                }
            });
        });
    </script>
@stop