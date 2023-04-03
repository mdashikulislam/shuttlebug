@extends('layouts.office')

@section('title')
    <title>Trip Sheet Summary</title>
@stop

@section('style')
    <style>
        @foreach ($vehicles as $id => $vehicle )
          .{{ 'v'.$id }} { background-color: {{ $colours[$loop->index] }}; }
        @endforeach
        .ylw { background-color: yellow; }
        a.map { color: #212529; text-decoration: none; }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-tripsheets')

        {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-lg col-12 pa-1 content d-none d-md-block" id="content">

        {{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg-8 my-auto">
                        <h3>Trip Sheet Summary
                            <small class="text-muted ml-3">{{ Carbon\Carbon::parse($date)->format('l j F Y') }}
                                <small class="ml-3">{{ collect(Arr::collapse($trips))->where('type', 'dropoff')->count() }} Passengers</small></small>
                        </h3>
                        <span class="text-muted">Vehicles show Innova or homeheroes # seats. The number above the vehicle is # passengers for the day.</span>
                    </div>
                    <div class="col-lg-4 my-auto">
                        <button id="print" class="btn btn-outline-dark">Print Summary</button>
                    </div>
                </div>
                <hr class="mb-4">

                <div class="row">

                    @if ( count(Arr::flatten($trips)) == 0 )
                        <div class="col-lg-12 text-center">
                            @if ( count($passengers) == 0 )
                                <p>There are no bookings on {{ Carbon\Carbon::parse($date)->format('l j F Y') }}.</p>
                            @else
                                <p>There is no trip plan for {{ Carbon\Carbon::parse($date)->format('l j F Y') }}.</p>
                                <p><a class="btn btn-primary" href="{!! url('office/operations/tripplans') !!}">Run the Trip Planner</a></p>
                            @endif
                        </div>
                    @else

        {{-- table ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-12 col-xl-9 mb-3">
                        <table class="table table-sm table-bordered mb-0" id="index-table">
                            <thead>
                                <tr>
                                    <th>Pickup<br>Time</th>
                                    @foreach( $vehicles as $id => $vehicle )
                                        <th class="text-center"><small>{{ $vehicle['pass'] }}</small><br>
                                            {{ $id == 102 ? 'Inn' : $vehicle['seats'].'s' }}<br>
                                            @if ( $vehicle['att'] == 'None' )
                                                <span class="text-danger"><small>{{ $vehicle['att'] }}</small></span>
                                            @else
                                                <small>{{ $vehicle['att'] }}</small>
                                            @endif
                                        </th>
                                    @endforeach
                                    <th>Passenger</th>
                                    <th>Pick Up</th>
                                    <th>Drop Off</th>
                                    <th class="trim">Drop<br>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ( $timeslots as $timeslot )
                                    @php $ts = 0; $vn = 0; @endphp

                                    {{-- am trips ------------------------------------------------------------------}}

                                    @if ( $timeslot < '09:00:00' )
                                        @foreach ( $vehicles as $id => $vehicle )
                                            @foreach ( $trips[$id] as $trip )
                                                @if ( $trip->putime == $timeslot && $trip->type == 'pickup' )
                                                    <tr>
                                                        <th>{{ substr($timeslot,0,5) }}</th>
                                                        @for ( $i = 0; $i < count($vehicles); $i++ )
                                                            @if ( $i == $vn )
                                                                <td class="text-center v{{ $id }}"><i class="fa fa-check"></i></td>
                                                            @else
                                                                <td class=""></td>
                                                            @endif
                                                        @endfor
                                                        @php
                                                            $leg_passengers = explode(',', $trip->passengers);
                                                        @endphp
                                                        @if ( count($leg_passengers) > 1 )
                                                            <td>
                                                                @foreach ( $leg_passengers as $passenger )
                                                                    {{ $passenger }}<br>
                                                                @endforeach
                                                            </td>
                                                        @else
                                                            <td>{{ $trip->passengers }}</td>
                                                        @endif
                                                        <td>
                                                            <a class="map" href="#" data-geo="{{ $trip->geo }}">
                                                                @if ( !in_array(substr($trip->address,strrpos($trip->address, ',')+1), ['Hout Bay','Llandudno']) )
                                                                    <small class="text-danger">{{ str_replace(',Hout Bay','',$trip->address) }}</small>
                                                                @else
                                                                    <small>{{ str_replace(',Hout Bay','',$trip->address) }}</small>
                                                                @endif
                                                            </a>
                                                        </td>
                                                        @php
                                                            $dropoffs = collect($trips[$id])->where('type', 'dropoff')->where('dotime', '<', '09:00:00')->whereIn('passengers', $leg_passengers)->all();
                                                        @endphp
                                                        <td>
                                                            @foreach ( $dropoffs as $dropoff )
                                                                <a class="map" href="#" data-geo="{{ $dropoff->geo }}">
                                                                    @if ( !in_array(substr($dropoff->address,strrpos($dropoff->address, ',')+1), ['Hout Bay','Llandudno']) )
                                                                        <small class="text-danger">{{ $dropoff->venue }}</small>
                                                                    @else
                                                                        <small>{{ $dropoff->venue }}</small>
                                                                    @endif
                                                                </a><br>
                                                            @endforeach
                                                        </td>
                                                        <td>
                                                            @foreach ( $dropoffs as $dropoff )
                                                                {{ substr($dropoff->dotime,0,5) }}<br>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            @php $ts++; @endphp
                                            @php $vn++; @endphp
                                        @endforeach

                                    {{-- day trips -----------------------------------------------------------------}}

                                    @else
                                        @foreach ( $vehicles as $id => $vehicle )
                                            @foreach ( $trips[$id] as $trip )
                                                @php
                                                if ( $trip->putime == $timeslot && $trip->type == 'pickup' ) {
                                                    if ( !in_array(substr($trip->address,strrpos($trip->address, ',')+1), ['Hout Bay','Llandudno']) ) {
                                                        $venue = '<span class="text-danger">'.$trip->venue.'</span>';
                                                    } else {
                                                        $venue = $trip->venue;
                                                    }
                                                    $pugeo = $trip->geo;
                                                    $leg = $trip->route;
                                                }
                                                @endphp
                                                @if ( $trip->putime == $timeslot && $trip->type == 'dropoff' )
                                                    <tr>
                                                        <th>{{ $ts == 0 ? substr($timeslot,0,5) : '' }}</th>
                                                        @for ( $i = 0; $i < count($vehicles); $i++ )
                                                            @if ( $i == $vn )
                                                                <td class="text-center v{{ $id }}"><i class="fa fa-check"></i></td>
                                                            @else
                                                                <td class=""></td>
                                                            @endif
                                                        @endfor
                                                        <td>{{ $passengers[$trip->pass_id] }}</td>
                                                        <td>
                                                            <a class="map" href="#" data-geo="{{ $pugeo }}">
                                                            <small>{!! str_replace(',Hout Bay','',$venue) !!}</small>
                                                            </a>
                                                        </td>
                                                        <td><a class="map" href="#" data-geo="{{ $trip->geo }}">
                                                            @php
                                                            $dov = $trip->venue == 'Home' ? $trip->address : $trip->venue;
                                                                if ( !in_array(substr($trip->address,strrpos($trip->address, ',')+1), ['Hout Bay','Llandudno']) ) {
                                                                    $dovenue = '<span class="text-danger">'.$dov.'</span>';
                                                                } else {
                                                                    $dovenue = str_replace(',Hout Bay','',$dov);
                                                                }
                                                            @endphp
                                                                <small class="">{!! $dovenue !!}</small></a>
                                                        </td>
                                                        <td class="{{ $timeslot > '09:00:00' && $trip->route != $leg ? 'ylw' : ''}}">{{ substr($trip->dotime,0,5) }}</td>
                                                    </tr>
                                                    @php $ts++; @endphp
                                                @endif
                                            @endforeach
                                            @php $vn++; @endphp
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                        <small><span class="ylw pull-right mt-2"> Highlighted Time indicates passenger drop off was delayed. </span></small>
                    </div>

        {{-- map ----------------------------------------------------------------------------------------}}

                    <div class="col-xl-3 d-none d-xl-block">
                        <div id="map" style="margin-top:100px;height:400px;"></div>
                        <div class="text-muted small mt-3">
                            Click on passenger's Pickup or Drop Off to locate on map.
                        </div>
                    </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@stop

@section('script')
@parent
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAX1xKuub_cJqm_icVEDQ_iqe1iESyVXNY"></script>
<script src="{!! asset('js/jquery.print.js') !!}"></script>
@stop


@section('jquery')
@parent
    <script>
        let map;
        let marker;

        $(function() {

            initialize();

            // print table
            $('#print').on('click', function() {
                $('#index-table').print({
                    title: "{{ 'Trip Sheet Summary '.Carbon\Carbon::parse($date)->format('l j F Y') }}"
                });
            });

            // map
            function initialize() {
                let latlng = new google.maps.LatLng(-34.027, 18.365);
                let options = {
                    zoom: 13,
                    center: latlng,
                    mapTypeControl: false,
                    streetViewControl: false,
                    zoomControlOptions: {
                        style: google.maps.ZoomControlStyle.SMALL
                    },
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };

                map = new google.maps.Map($('#map')[0], options);

                $('.map').click(function(e) {
                    e.preventDefault();
                    if (marker && marker.setMap) marker.setMap(null);
                    let loc = $(this).data('geo');

                    // Place marker on map
                    let lat = parseFloat(loc.substr(0, loc.indexOf(',')));
                    let lon = parseFloat(loc.substr(loc.indexOf(',')+1));
                    let latLng = new google.maps.LatLng(lat, lon);
                    marker = new google.maps.Marker({
                        position: latLng,
                        map: map
                    });
                    map.setCenter(marker.getPosition());
                });
            }
        });
    </script>
@stop
