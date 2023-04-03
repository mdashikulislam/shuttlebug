@extends('layouts.office')

@section('title')
    <title>Vehicle Stats</title>
@stop

@section('css')
    @parent
@stop

@section('style')
    @parent
    <style>
        .grey { background: #f2f2f2; }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">

            {{--local nav--}}
            @include('layouts.nav.nav-local-stats')

            {{--content--}}
            <section class="col-md col-12 pa-1 content mb-3">
                <h3 class="page-header"><small>Analytics:</small> Vehicles</h3>
                <hr>

                {{--<h4 class="mt-3">Vehicle Usage</h4>--}}
                {{--<p class="text-muted small">Excludes last day of term which is a contra-pattern. <span class="ml-3">Up to end of current month.</span> <span class="text-info ml-3">Blue numbers are per day.</span></p>--}}
                <div class="table-responsive mt-4">
                    <table class="table table-sm mb-0" style="width:auto">
                        <thead>
                        <tr>
                            <th></th>
                            @foreach ( $days as $month => $count )
                                @if ($loop->first)
                                    <th class="text-center px-3">{{ \Carbon\Carbon::parse('2018-'.$month.'-15')->format('M') }} to date</th>
                                @else
                                    <th class="text-center px-3">{{ \Carbon\Carbon::parse('2018-'.$month.'-15')->format('F') }}</th>
                                @endif
                            @endforeach
                        </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <th colspan="{{ count($days) + 1 }}" class="grey"><em>Total</em></th>
                            </tr>
                            <tr>
                                <td><em>Days</em></td>
                                @foreach ( $days as $month => $count )
                                    <td class="text-center"><em>{{ $count }}</em></td>
                                @endforeach
                            </tr>
                            <tr>
                                <td><em>Passengers</em></td>
                                @foreach ( $days as $month => $count )
                                    <td class="text-center"><em>{{ isset($bookings[$month]) ? $bookings[$month] : '' }}</em></td>
                                @endforeach
                            </tr>
                            <tr>
                                <td><em>Pass /day</em></td>
                                @foreach ( $days as $month => $count )
                                    <td class="text-center text-info"><em>{{ isset($bookings[$month]) ? number_format($bookings[$month] / $count,1) : '' }}</em></td>
                                @endforeach
                            </tr>

                            @foreach ( $vehicles as $vehicle )
                                <tr>
                                    <th colspan="{{ count($days) + 1 }}" class="grey">
                                        {{ $fleet->where('id', $vehicle)->first()->model ?? '3 seater' }}
                                        @if ( isset($vdays[$vehicle]) )
                                            @if ( isset($vdays[$vehicle]['end']) )
                                                <span class="small ml-3">( up to {{ $vdays[$vehicle]['end'] }} )</span>
                                            {{--@else--}}
                                                {{--<span class="small ml-3">( from {{ $vdays[$vehicle]['start'] }} )</span>--}}
                                            @endif
                                        @endif
                                    </th>
                                </tr>
                                <tr>
                                    <td>Passengers</td>
                                    @foreach ( $days as $month => $count )
                                        @if ( !isset($vdays[$vehicle])
                                            || (isset($vdays[$vehicle]['end']) && $vdays[$vehicle]['month'] >= $month)
                                             || (isset($vdays[$vehicle]['start']) && $vdays[$vehicle]['month'] <= $month) )

                                            <td class="text-center">{{ collect($stats)->where('vehicle',$vehicle)->where('month',$month)->first()->bookings ?? 0 }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                                <tr>
                                    <td>Pass /day</td>
                                    @foreach ( $days as $month => $count )
                                        @if ( !isset($vdays[$vehicle])
                                            || (isset($vdays[$vehicle]['end']) && $vdays[$vehicle]['month'] >= $month)
                                             || (isset($vdays[$vehicle]['start']) && $vdays[$vehicle]['month'] <= $month) )

                                            @php
                                            $exists = collect($stats)->where('vehicle',$vehicle)->where('month',$month)->first();
                                            @endphp
                                            @if ( isset($vdays[$vehicle]) && $vdays[$vehicle]['month'] == $month )
                                                <td class="text-center text-info">{{ !is_null($exists) ? number_format($exists->bookings / $vdays[$vehicle]['days'],1) : '' }}</td>
                                            @else
                                                <td class="text-center text-info">{{ !is_null($exists) ? number_format($exists->bookings / $count,1) : '' }}</td>
                                            @endif
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                                <tr>
                                    <td>Km</td>
                                    @foreach ( $days as $month => $count )
{{--                                        @if ( !isset($vdays[$vehicle->id]) || (isset($vdays[$vehicle->id]) && $vdays[$vehicle->id]['month'] >= $month) )--}}
                                        @if ( !isset($vdays[$vehicle])
                                            || (isset($vdays[$vehicle]['end']) && $vdays[$vehicle]['month'] >= $month)
                                             || (isset($vdays[$vehicle]['start']) && $vdays[$vehicle]['month'] <= $month) )

                                            <td class="text-center">{{ collect($mileage)->where('reg',$vehicle)->where('month',$month)->first()->km ?? '' }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                                <tr>
                                    <td>Km /day</td>
                                    @foreach ( $days as $month => $count )
{{--                                        @if ( !isset($vdays[$vehicle->id]) || (isset($vdays[$vehicle->id]) && $vdays[$vehicle->id]['month'] >= $month) )--}}
                                        @if ( !isset($vdays[$vehicle])
                                        || (isset($vdays[$vehicle]['end']) && $vdays[$vehicle]['month'] >= $month)
                                         || (isset($vdays[$vehicle]['start']) && $vdays[$vehicle]['month'] <= $month) )

                                            @if ( !is_null(collect($mileage)->where('reg',$vehicle)->where('month',$month)->first()) )

                                                @if ( isset($vdays[$vehicle]) && $vdays[$vehicle]['month'] == $month )
                                                    <td class="text-center text-info">{{ number_format(collect($mileage)->where('reg',$vehicle)->where('month',$month)->first()->km / $vdays[$vehicle]['days'],1) }}</td>
                                                @else
                                                    <td class="text-center text-info">{{ number_format(collect($mileage)->where('reg',$vehicle)->where('month',$month)->first()->km / $count,1) }}</td>
                                                @endif
                                            @else
                                                <td></td>
                                            @endif
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                                <tr>
                                    <td>Km /pass</td>
                                    @foreach ( $days as $month => $count )
                                        @php
                                            $stat = collect($mileage)->where('reg',$vehicle)->where('month',$month)->first();
                                        @endphp
{{--                                        @if ( !isset($vdays[$vehicle->id]) || (isset($vdays[$vehicle->id]) && $vdays[$vehicle->id]['month'] >= $month) )--}}
                                        @if ( !isset($vdays[$vehicle])
                                        || (isset($vdays[$vehicle]['end']) && $vdays[$vehicle]['month'] >= $month)
                                         || (isset($vdays[$vehicle]['start']) && $vdays[$vehicle]['month'] <= $month) )

                                            @if ( !is_null(collect($mileage)->where('reg',$vehicle)->where('month',$month)->first()) )
                                                <td class="text-center text-info">{{ number_format(collect($mileage)->where('reg',$vehicle)->where('month',$month)->first()->km / collect($stats)->where('vehicle',$vehicle)->where('month',$month)->first()->bookings,1) }}</td>
                                            @else
                                                <td></td>
                                            @endif
                                        @else
                                            <td></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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

    </script>
@stop