@extends('layouts.office')

@section('title')
    <title>Am Plan</title>
@stop

@section('css')
    @parent
@stop

@section('style')
    @parent
    <style>
        table { min-width: 60%; }
        .flag { background-color: #ea6272; }
        .good { background-color: #2ab27b; }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-tripplans')

            {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-lg col-12 pa-1 content d-none d-md-block" id="content">

                {{-- header -----------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg-4 col-xl-4 my-auto">
                        <h3>Trip Plan: Morning</h3>
                        <h4>{{ Carbon\Carbon::parse($request->date)->format('l j F Y') }}</h4>
                        <h5 class="text-muted">{{ collect($vehicles)->sum('pass') }} Passengers</h5>
                    </div>

                    {{-- message --------------------------------------------------------------------------}}

                    <div class="col-lg-4 col-xl-4 my-auto">
                        @if ( isset($error) )
                            <div class="alert alert-danger">
                                {{ $error }}
                            </div>
                        @elseif ( $report )
                            <span class="text-muted small">
                                Updated {{ Carbon\Carbon::parse($report->updated_am)->diffForHumans() }}.<br>
                                @if ( isset($runtime) )
                                    Runtime: {{ $runtime }} secs.<br>
                                @endif
                                Vehicles: {{ count($report->am_vehicles) }}<br>
                            </span>
                        @endif
                    </div>

                    {{-- buttons ------------------------------------------------------------------------}}

                    <div class="col-lg-4 col-xl-4 my-auto">
                        <a class="btn btn-danger btn-sm ml-2" href="{!! url('office/operations/tripplans/plan/amrerun') !!}">Force Re-run</a>
                    </div>
                </div>
                <hr class="mb-3">

                {{-- table -----------------------------------------------------------------------------}}

                @if ( $report == false && !isset($error) )
                    There are no trips on the {{ Carbon\Carbon::parse($request->date)->format('j F') }}.
                @elseif ( is_null($report) && !isset($error) )
                    <div class="alert alert-danger mt-5 text-center">
                        <p>There was a problem producing this trip plan.</p>
                        <p>The webmaster has been notified.</p>
                    </div>
                @elseif ( !isset($error) )

                    <table class="dtable display mt-3" data-order='' cellspacing="3" cellpadding="3" id="tplan">

                        @foreach ( $vehicles as $vid => $vehicle )
                            @if ( isset($report->am[$vid]) )
                                <tr>
                                    <th colspan="5">
                                        <h5>
                                            {{ $vid == 102 ? 'Innova' : 'Homeheroes '.$vehicle['seats']. ' seater' }}:
                                            <span class="ml-3 text-info">(<small>Attendant: </small>{{ $vehicle['att'] ?? '' }})</span>
                                            <span class="ml-3 text-info">(<small>Zone: </small>{{ $report->am[$vid][0]['zone'] }})</span>
{{--                                            @if ( $vid == 102 )--}}
{{--                                                <span class="ml-3">--}}
{{--                                                    <small>Change Innova's Zone to: </small>--}}
{{--                                                    {!! Form::select("hackzone", ['' => 'Auto'] + $zones, null, ['class' => 'form-control-sm custom-select', 'id' => 'hackzone', 'style' => 'width:25%']) !!}--}}
{{--                                                </span>--}}
{{--                                            @endif--}}
                                        </h5>
                                    </th>
                                </tr>
                                <tr>
                                    <th>Arrive</th>
                                    <th>Depart</th>
                                    <th>Trip</th>
                                    <th>Passenger</th>
                                    <th>School Times</th>
                                </tr>

                                @foreach ( $report->am[$vid] as $trip )
                                    @php
                                        if ( $trip['type'] == 'dropoff' ) {
                                            $class = ($trip['arrive'] < $trip['from'] || $trip['arrive'] > $trip['by']) ? 'flag' : 'good';
                                        } else {
                                            $class = '';
                                        }
                                    @endphp

                                    <tr>
                                        <td class="{{ $class }}">{{ $trip['type'] == 'depart' ? 'Office' : $trip['arrive'] }}</td>
                                        <td>{{ $trip['depart'] }}</td>
                                        <td>{{ $trip['type'] == 'depart' ? '' : $trip['type'] }}</td>
                                        <td>{{ $trip['type'] == 'depart' ? '' : $trip['description'] }}</td>
                                        <td class="{{ $class }}">{{ $trip['type'] == 'dropoff' ? $trip['from'].' - '.$trip['by'] : '' }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <th colspan="5"><h5>&nbsp;</h5></th>
                                </tr>
                            @endif
                        @endforeach
                    </table>
                @endif
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
            table = $('#tplan').DataTable( {"dom": 'lrtip'} );
            table.on('select', function ( e, dt, type, indexes ) {
                handleRowSelect(table.rows( indexes ).data().toArray()[0][0]);
            }).on('deselect', function () {
                handleRowDeselect();
            });
        });
    </script>
@stop