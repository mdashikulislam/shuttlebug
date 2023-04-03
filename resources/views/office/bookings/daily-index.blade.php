@extends('layouts.office')

@section('title')
    <title>Daily Bookings</title>
@stop

@section('css')
    @parent
@stop

@section('style')
    @parent
    <style>
        table.dataTable tbody tr.wht { background-color: #fff; }
        table.dataTable tbody tr.gry { background-color: #f9f9f9; }
        table.dataTable tbody td { border-top: 1px solid #ddd; }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-bookings')

    {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-lg col-12 pa-1 content d-none d-lg-block" id="content">

    {{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg-8 col-xl-8">
                        <h3>Bookings: <small>{{ \Carbon\Carbon::createFromFormat('Y-m-d',$date)->format('l jS F Y') }}<br>
                                {{ count($bookings) }} passengers</small></h3>
                    </div>

    {{-- buttons ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-4 col-xl-3 text-right">
                        <div class="form-group">
                            {!! Form::text('date', null, ['class' => 'form-control datepicker', 'id' => 'dtdaily', 'placeholder' => 'change date', 'data-route' => '/office/bookings/list/daily/']) !!}
                        </div>
                    </div>
                </div>

    {{-- table ----------------------------------------------------------------------------------------}}

                <table class="dtable mt-3" data-order='' cellspacing="0" width="100%" id="index-table">
                    <thead>
                        <tr>
                            <th class="id d-none">Id</th>
                            <th>Pickup Time</th>
                            <th>Passenger</th>
                            <th>Pickup</th>
                            <th>Drop Off</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php $last_time = 1; $class = 'wht'; @endphp
                        @foreach ( $bookings as $booking )
                            @if ( $booking->putime == '00:00:00' && $booking->dotime != $last_time )
                                @php $class = $class == 'wht' ? 'gry' : 'wht'; @endphp
                            @elseif ( $booking->putime > '00:00:00' && $booking->putime != $last_time )
                                @php $class = $class == 'wht' ? 'gry' : 'wht'; @endphp
                            @endif

                            <tr class="{{ $class }}">
                                <td class="d-none">{{ $booking->id }}</td>
                                <td>
                                    @if ( $booking->putime == '00:00:00' )
                                        {{  $booking->dotime == $last_time ? '' : substr($booking->dotime,0,5).' (drop off)' }}
                                    @else
                                        {{ $booking->putime == $last_time ? '' : substr($booking->putime,0,5) }}
                                    @endif
                                </td>
                                <td>{{ $booking->passenger->name }}</td>
                                <td>{{ $booking->puloc->id < 200000 ? 'home' : $booking->puloc->name }}
                                    @if ( !in_array($booking->puloc->suburb, ['Hout Bay','Llandudno']) )
                                        <span class="ml-3 text-danger"><i class="fa fa-star"></i></span>
                                    @endif
                                </td>
                                <td>{{ $booking->doloc->id < 200000 ? 'home' : $booking->doloc->name }}
                                    @if ( !in_array($booking->doloc->suburb, ['Hout Bay','Llandudno']) )
                                        <span class="ml-3 text-danger"><i class="fa fa-star"></i></span>
                                    @endif
                                </td>
                            </tr>
                            @php $last_time = $booking->putime == '00:00:00' ? $booking->dotime : $booking->putime; @endphp
                        @endforeach
                    </tbody>
                </table>
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

            {{-- get daily list --}}
            $('#dtdaily').on('change', function() {
                if ( $(this).val() > '' ) {
                    window.location.href = $(this).data('route') + $(this).val();
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