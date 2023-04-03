@extends('layouts.front')

@section('title')
    <title>Home Heroes</title>
@stop

@section('css')
    @parent
    <link rel="stylesheet" href="{{ asset('css/bootstrap-datepicker3.standalone.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/back.css') }}">
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md">
                <h3 class="page-header">Home Heroes</h3>
                <h4>Vehicle Schedules for {{ \Carbon\Carbon::parse($date)->format('D j F') }}</h4>
            </div>

            <div class="col-md">
                <div class="text-right mb-3">
                    <span class="mr-3">View Vehicle Schedules for</span>
                    {!! Form::text('date', null, ['class' => 'form-control form-control-sm datepicker float-right w-25', 'placeholder' => 'select date', 'id' => 'date']) !!}
                </div>
            </div>
        </div>

        <div class="row">
            @if ( !is_null($invoice) )
                <div class="alert alert-info text-center mt-3 ml-3">
                    <div class="h4">Invoicing:</div>
                    @foreach ( $invoice as $line )
                        {{ $line['pass'] }} passengers @ R {{ $line['price'] }} value R {{ $line['value'] }}<br>
                    @endforeach
                    <hr>
                    Total: R {{ array_sum(array_column($invoice, 'value')) }}
                </div>
            @elseif ( count($vehicles) > 0 )
                <p class="text-info mt-3 ml-3">The invoicing data for {{ \Carbon\Carbon::parse($date)->format('D j F') }} will be available here from the next day.</p>
            @endif
            @if ( count($vehicles) > 0 )
                <div class="table-responsive mt-5">
                    <table class="table table-striped table-bordered table-sm mb-4">
                        <thead>
                            <tr>
                                <th class="text-center"></th>
                                @foreach ( $vehicles as $vehicle )
                                    <th colspan="2" class="text-center">
                                        {{ $vehicle['seats'] }} seater. ( {{ $vehicle['att'] }} )
                                    </th>
                                @endforeach
                            </tr>
                            <tr>
                                <th class="text-center">Time</th>
                                @foreach ( $vehicles as $vehicle )
                                    <th>Venue</th>
                                    <th>Pax<sup class="text-danger">1</sup></th>
                                @endforeach
                            </tr>
                            <tr>
                                <td colspan="{{ count($vehicles) * 2 + 1 }}"></td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ( $times as $time )
                                <tr>
                                    <th class="text-center">{{ $time }}</th>

                                    @foreach ( $vehicles as $vehicle )
                                        @if ( in_array($time, array_keys($vehicle['times'])) )
                                            @if ( $time < '09:00' )
                                                @php
                                                $pickup = collect($plan->am[$vehicle['id']])->where('type', 'pickup')->where('putime', $time)->where('venue', $vehicle['times'][$time])->first();
                                                @endphp
                                                <td>{{ !is_null($pickup) ? $pickup['venue'] : '' }}<br>
                                                    <small class="text-info">Free @ {{ $pickup['dotime'] }}</small>
                                                </td>
                                                <td>{{ !is_null($pickup) ? count(explode(',',$pickup['description'])) : '' }}
                                                    @if ( collect($plan->am[$vehicle['id']])->where('type', 'depart')->first()['zone'] != 'in' )
                                                        <span class="text-danger"><i class="fa fa-star"></i></span>
                                                    @endif
                                                </td>
                                            @else
                                                @php
                                                $pickup = collect($plan->day)->where('pickup.time', $time)->where('pickup.venue', $vehicle['times'][$time])->first();
                                                if ( !is_null($pickup) ) {
                                                    $vzone = $pickup['vzone'][$vehicle['id']] ?? 'in';
                                                    $pzone = $pickup['pickup']['zone'];

                                                    if ( strpos($pickup['ranking'][$vehicle['id']],'waiting') !== false ) {
                                                        $passengers = min(substr($pickup['available'][$vehicle['id']],0,1), $pickup['pickup']['passengers'][$vzone]);
                                                    } else {
                                                        $passengers = $pickup['pickup']['passengers'][$vzone];
                                                    }

                                                    $pass = $pickup['pass'][$vehicle['id']] > $passengers ? $passengers : $pickup['pass'][$vehicle['id']];
                                                    $oflow = $pickup['pass'][$vehicle['id']] > $passengers ? $pickup['pass'][$vehicle['id']] : 0;
                                                }
                                                @endphp
                                                @if ( !is_null($pickup) )
                                                    <td>{{ $pickup['pickup']['venue'] }}<br>
                                                        <small class="text-info">Free @ {{ $pickup['free'][$vehicle['id']] }}</small>
                                                    </td>
                                                    <td>{{ $pass }}
                                                        @if ( $oflow > 0 )
                                                            <span class="text-danger">({{ $oflow }})</span>
                                                        @endif
                                                        @if ( $vzone != 'in' || $pzone != 'in' )
                                                            <span class="text-danger"><i class="fa fa-star"></i></span>
                                                        @endif
                                                    </td>
                                                @else
                                                    <td></td>
                                                    <td></td>
                                                @endif
                                            @endif
                                        @else
                                            <td></td>
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                            <tr style="background-color: #ffffcc;">
                                <td></td>
                                @foreach ( $vehicles as $vehicle )
                                    <th colspan="2" class="text-center">{{ $vehicle['pax'] }} pax, {{ $vehicle['mileage'] }} km
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <p class="small"><sup>1</sup> Passengers collected.<br>
                        <span class="text-danger ml-3">(x)</span> passengers on-board if still carrying passengers from previous pickup.<br>
                        <span class="text-danger ml-3"><i class="fa fa-star"></i></span> trip is outside Hout Bay.
                    </p>
                    <p class="small">Schedules are subject to last-minute changes.</p>
                </div>

            @elseif ( is_null($plan) )
                @if ( \Carbon\Carbon::parse($date)->toDateString() <= now()->toDateString() )
                    <div class="alert alert-info text-center mt-5">There are no trips on {{ \Carbon\Carbon::parse($date)->format('D j F') }}.</div>
                @else
                    <div class="alert alert-danger text-center mt-5">The trips for {{ \Carbon\Carbon::parse($date)->format('D j F') }} have not yet been processed<br>or there are no trips on the day.</div>
                @endif

            @elseif ( count($vehicles) == 0 )
                <div class="alert alert-info mt-5">No vehicles are required on {{ \Carbon\Carbon::parse($date)->format('D j F') }}.</div>
            @endif
        </div>
    </div>
@stop

@section('script')
    @parent
    <script src="{!! asset('js/bootstrap-datepicker.min.js') !!}"></script>
@stop


@section('jquery')
    @parent
    <script>

        $(document).ready(function() {
            $('.datepicker').datepicker('destroy');
            $('.datepicker').datepicker({
                format:         'yyyy-mm-dd',
                autoclose:      true,
                weekStart:      1,
                startDate:      '2019-01-01',
                startView:      'days',
                todayHighlight: 'true',
            });

            {{-- load the schedules for selected date --}}
            $('#date').on('change', function() {
                if ( $(this).val() > '' ) {
                    window.location.href = '/homeheroes/' + $(this).val();
                }
            });
        });
    </script>
@stop