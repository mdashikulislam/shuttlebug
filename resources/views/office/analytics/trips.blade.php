@extends('layouts.office')

@section('title')
    <title>Trip Demographics</title>
@stop

@section('css')
    @parent
@stop

@section('style')
    @parent
    <style>
        .yellow { background: yellow; }
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
                <h3 class="page-header"><small>Analytics:</small> Trips</h3>
                <hr>

                <h4 class="mt-3">Demographics</h4>
                <p class="text-muted small">Excludes last day of term which is a contra-pattern. <span class="ml-3">Up to end of current month.</span> <span class="text-info ml-3">Blue numbers are per day.</span></p>
                <div class="table-responsive mt-3">
                    <table class="table table-sm mb-0">
                        <thead>
                        <tr>
                            <th>Period</th>
                            <th class="text-center">School Days</th>
                            <th class="text-center">Total Lifts</th>
                            <th colspan="4" class="text-center grey">Trip Times</th>
                            <th></th>
                            <th colspan="4" class="text-center grey">Trip Routes</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th class="text-center">< 9am</th>
                            <th class="text-center">9 - 12am</th>
                            <th class="text-center">12 - 4pm</th>
                            <th class="text-center">> 4pm</th>
                            <th></th>
                            <th class="text-center">Home->School</th>
                            <th class="text-center">School->Home</th>
                            <th class="text-center">from Xmural</th>
                            <th class="text-center">to Xmural</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach ( $periods as $year => $count )
                                <tr>
                                    <td>{{ $year }} {{ $year == now()->year ? 'ytd' : '' }}</td>
                                    <td class="text-center">{{ $days[$year] }}</td>
                                    <td class="text-center">
                                        {{ $count }}
                                        <span class="text-info small">{{ number_format($count / $days[$year],1) }}</span>
                                    </td>
                                    @foreach ( $times[$year] as $time => $lifts )
                                        <td class="text-center">
                                            {{ $lifts }}
                                            <span class="text-info small">{{ number_format($lifts / $days[$year],1) }}</span>
                                        </td>
                                    @endforeach
                                    <td></td>
                                    @foreach ( $venues[$year] as $venue => $trips )
                                        <td class="text-center">
                                            {{ $trips }}
                                            <span class="text-info small">{{ number_format($trips / $days[$year],1) }}</span>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col-xl-4">
                        <h4 class="mt-5">Peak Time Trends</h4>
                        <p class="text-muted small">Passengers per day.</p>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th class="text-center">{{ now()->subYear()->year }}</th>
                                        <th class="text-center">{{ now()->year }}</th>
                                        <th class="text-center">{{ now()->format('M Y') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ( $ptimes as $time )
                                        <tr>
                                            <th>{{ substr($time,0,5) }}</th>
                                            @if ( isset($peak_times[now()->subYear()->year][$time]) )
                                                <td class="text-center">{{ number_format($peak_times[now()->subYear()->year][$time] / $days[now()->subYear()->year],1) }}</td>
                                            @else
                                                <td></td>
                                            @endif
                                            @if ( isset($peak_times[now()->year][$time]) )
                                                @php
                                                $class = $peak_times[now()->subYear()->year][$time] / $days[now()->subYear()->year] < $peak_times[now()->year][$time] / $days[now()->year] ? 'bg-success' : '';
                                                @endphp
                                                <td class="text-center {{ $class }}">{{ number_format($peak_times[now()->year][$time] / $days[now()->year],1) }}</td>
                                            @else
                                                <td></td>
                                            @endif
                                            @if ( isset($peak_times['current'][$time]) )
                                                @if ( $days['current'] > 0 )
                                                    @php
                                                        $class = $peak_times[now()->subYear()->year][$time] / $days[now()->subYear()->year] < $peak_times['current'][$time] / $days['current'] ? 'bg-success' : '';
                                                    @endphp
                                                    <td class="text-center {{ $class }}">{{ number_format($peak_times['current'][$time] / $days['current'],1) }}</td>
                                                @else
                                                    <td></td>
                                                @endif
                                            @else
                                                <td></td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-xl-1">
                    </div>

                    <div class="col-xl-4">
                        <h4 class="mt-5">School Trends</h4>
                        <p class="text-muted small">Passengers per day at peak times. Schools with min of 1 lift /day.</p>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm mb-0">
                                <thead>
                                <tr>
                                    <th>School</th>
                                    <th class="text-center">{{ now()->subYear()->year }}</th>
                                    <th class="text-center">{{ now()->year }}</th>
                                    <th class="text-center">{{ now()->format('M Y') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ( $schools as $school )
                                    <tr>
                                        @if ( $school[now()->year] + $school[now()->subYear()->year] > $days[now()->year] + $days[now()->subYear()->year] )
                                            <th>{{ $school['school'] }}</th>
                                            <td class="text-center">{{ number_format($school[now()->subYear()->year] / $days[now()->subYear()->year],1) }}</td>

                                            @php
                                                $class = $school[now()->subYear()->year] / $days[now()->subYear()->year] < $school[now()->year] / $days[now()->year] ? 'bg-success' : '';
                                            @endphp
                                            <td class="text-center {{ $class }}">{{ number_format($school[now()->year] / $days[now()->year],1) }}</td>

                                            @php
                                                $class = $school[now()->subYear()->year] / $days[now()->subYear()->year] < $school['current'] / $days['current'] ? 'bg-success' : '';
                                            @endphp
                                            <td class="text-center {{ $class }}">{{ number_format($school['current'] / $days['current'],1) }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
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