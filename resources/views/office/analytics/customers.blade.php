@extends('layouts.office')

@section('title')
    <title>Customers</title>
@stop

@section('css')
    @parent
@stop

@section('style')
    @parent
    <style>
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">

            {{--local nav--}}
            @include('layouts.nav.nav-local-stats')

            {{--content--}}
            <section class="col-md col-12 pa-1 content mb-3">
                <h3 class="page-header"><small>Analytics:</small> Customers</h3>
                <hr>

                <h4 class="mt-5">Lifts / day</h4>

                <div class="row">
                    <div class="col-xl-4">
                        <h4 class="mt-5">Current Top Ranked Customer</h4>
                        <p class="text-muted small">Customers with at least 3 bookings /week. Ranked on current month.</p>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm mb-0">
                                <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th class="text-center">{{ now()->subYear()->year }}</th>
                                    <th class="text-center">{{ now()->year }}</th>
                                    <th class="text-center">{{ now()->format('M Y') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-danger">
                                        <th>Total of List</th>
                                        <th class="text-center">{{ number_format($total[now()->subYear()->year] / $days[now()->subYear()->year],1) }}</th>
                                        <th class="text-center">{{ number_format($total[now()->year] / $days[now()->year],1) }}</th>
                                        <th class="text-center">{{ number_format($total['current'] / $days['current'],1) }}</th>
                                    </tr>
                                @foreach ( $customers as $id => $name )
                                    <tr>
                                        @if ( isset($stats['current'][$id]) && $stats['current'][$id] > $days['current'] * .6 )
                                            <th>{{ $name }}</th>
                                            @if ( isset($stats[now()->subYear()->year][$id]) )
                                                <td class="text-center">{{ number_format($stats[now()->subYear()->year][$id] / $days[now()->subYear()->year],1) }}</td>
                                            @else
                                                <td></td>
                                            @endif
                                            @if ( isset($stats[now()->year][$id]) )
                                                @php
                                                    if ( isset($stats[now()->subYear()->year][$id]) ) {
                                                        $class = $stats[now()->year][$id] / $days[now()->year] > $stats[now()->subYear()->year][$id] / $days[now()->subYear()->year] ? 'bg-success' : '';
                                                    } else {
                                                        $class = '';
                                                    }
                                                @endphp
                                                <td class="text-center {{ $class }}">{{ number_format($stats[now()->year][$id] / $days[now()->year],1) }}</td>
                                            @else
                                                <td></td>
                                            @endif
                                            @if ( isset($stats['current'][$id]) )
                                                @php
                                                    $class = $stats['current'][$id] / $days['current'] > $stats[now()->year][$id] / $days[now()->year] ? 'bg-success' : '';
                                                @endphp
                                                <td class="text-center {{ $class }}">{{ number_format($stats['current'][$id] / $days['current'],1) }}</td>
                                            @else
                                                <td></td>
                                            @endif
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
                        <h4 class="mt-5">Declining Customers</h4>
                        <p class="text-muted small">Customers with at least 3 bookings /week. Ranked on previous year.</p>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm mb-0">
                                <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th class="text-center">{{ now()->subYear()->year }}</th>
                                    <th class="text-center">{{ now()->year }}</th>
                                    <th class="text-center">{{ now()->format('M Y') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ( $customers as $id => $name )
                                    <tr>
                                    @if ( isset($stats[now()->subYear()->year][$id]) && $stats[now()->subYear()->year][$id] > $days[now()->subYear()->year] * .6 && !isset($stats['current'][$id]) )
                                        <th>{{ $name }}</th>
                                        @if ( isset($stats[now()->subYear()->year][$id]) )
                                            <td class="text-center">{{ number_format($stats[now()->subYear()->year][$id] / $days[now()->subYear()->year],1) }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                        @if ( isset($stats[now()->year][$id]) )
                                            <td class="text-center">{{ number_format($stats[now()->year][$id] / $days[now()->year],1) }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                        @if ( isset($stats['current'][$id]) )
                                            <td class="text-center">{{ number_format($stats['current'][$id] / $days['current'],1) }}</td>
                                        @else
                                            <td></td>
                                        @endif
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