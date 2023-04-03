@extends('layouts.office')

@section('title')
    <title>Customer Bookings</title>
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

            @include('layouts.nav.nav-local-bookings')

    {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-lg col-12 pa-1 content d-none d-lg-block" id="content">

    {{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg-5 col-xl-4">
                        <h3>Bookings: <small>{{ $user->name }}<br>
                            {{ count($bookings) }} {{ is_null($q) ? 'current' : 'completed' }} bookings</small>
                        </h3>
                        @if ( !is_null($q) )
                            <p><small><span class="text-muted">Journal adjustments:</span> <span class="text-primary">bookings added</span> | <span class="text-danger">bookings cancelled</span></small></p>
                        @endif
                    </div>

    {{-- buttons ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-3 col-xl-4">
                        @if ( !is_null($q) )
                            <a href="{!! URL::to("office/bookings/list/customer/$user->id" ) !!}" class="btn btn-outline-dark btn-sm">List Current Bookings</a>
                        @else
                            <a href="{!! URL::to("office/bookings/list/customer/$user->id/completed") !!}" class="btn btn-outline-dark btn-sm">List Completed Bookings</a>
                        @endif
                    </div>

    {{-- messages ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-4 col-xl-4">
                        @if ( session()->has('confirm') )
                            <div class="alert-success alert-temp">{{ session('confirm') }}</div>
                        @elseif ( session()->has('warning') )
                            <div class="alert-warning alert-temp">{{ session('warning') }}</div>
                        @elseif ( session()->has('danger') )
                            <div class="alert-danger alert-temp">{{ session('danger') }}</div>
                        @endif
                    </div>
                </div>

    {{-- form & table ----------------------------------------------------------------------------------------}}

                {!! Form::open(['url' => 'office/bookings/cancel', 'id' => 'capture']) !!}
                    <table class="dtable display mt-3" data-order='' cellspacing="0" width="100%" id="index-table">
                        <thead>
                            <tr>
                                @if ( is_null($q) )
                                    <th>Cancel</th>
                                @endif
                                <th>Date</th>
                                <th>Passenger</th>
                                <th>Pickup</th>
                                <th>Time</th>
                                <th>Drop Off</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php $last_date = 1; @endphp
                            @foreach ( $bookings as $booking )
                                @if ( $booking->journal == 'added')
                                    <tr class="text-primary">
                                @elseif ( $booking->journal == 'cancelled' )
                                    <tr class="text-danger">
                                @else
                                    <tr>
                                @endif
                                    @if ( is_null($q) )
                                        <td class="pt-0">
                                            <label class="control control-checkbox mt-1">
                                                {!! Form::checkbox('cancel[]', $booking->id, null) !!}
                                                <span class="control_indicator"></span>
                                            </label>
                                        </td>
                                    @endif
                                    <td>
                                        {{  $booking->date == $last_date ? '' : \Carbon\Carbon::createFromFormat('Y-m-d',$booking->date)->format('l jS F') }}
                                    </td>
                                    <td>{{ $booking->passenger->name }}</td>
                                    <td>{{ $booking->puloc->id < 200000 ? 'home' : $booking->puloc->name }}</td>
                                    <td>
                                        {{  $booking->putime == '00:00:00' ? substr($booking->dotime,0,5).' (drop off)' : substr($booking->putime,0,5) }}
                                    </td>
                                    <td>{{ $booking->doloc->id < 200000 ? 'home' : $booking->doloc->name }}</td>
                                    <td>{{ $booking->price }}</td>
                                </tr>
                                @php $last_date = $booking->date; @endphp
                            @endforeach
                        </tbody>
                    </table>
                    @if ( is_null($q) )
                        <div class="mt-2">
                            <span class="tab"><a id="checkall" href="#">Select All</a></span>
                            {{ Form::hidden('customer', $user->id) }}
                            {{ Form::submit('Cancel Selected', ['class' => 'btn btn-primary ml-3']) }}
                        </div>
                    @endif
                {!! Form::close() !!}
                <br>
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

            {{-- get daily list --}}
            $('#listdaily').on('click', function () {
                let date = $('#dtdaily').val();
                if ( date > '' ) {
                    window.location.href = $(this).data('route') + date;
                }
            });

            {{-- toggle checkboxes --}}
            $(document).on('click', '#checkall', function () {
                if ( $(this).html() === 'Select All' ) {
                    $('input:checkbox').prop('checked', true);
                    $(this).html('Select None');
                }
                else {
                    $('input:checkbox').prop('checked', false);
                    $(this).html('Select All');
                }
            });

            {{-- switch away from index page when window re-sized to md --}}
            $( window ).resize(function() {
                if ($(window).width() < 990) {
                    window.location.href = '/office/bookings';
                }
            });
        });
    </script>
@stop