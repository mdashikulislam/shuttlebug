@extends('layouts.office')

@section('title')
    <title>Current Bookings</title>
@stop

@section('css')
    @parent
@stop

@section('style')
    @parent
    <style>
        .yellow { background: yellow; }
        .grey { background: #e3e3e3; }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">

            {{--local nav--}}
            @include('layouts.nav.nav-local-stats')

            {{--content--}}
            <section class="col-md col-12 pa-1 content">
                <h3 class="page-header"><small>Analytics:</small> {{ \Carbon\Carbon::now()->year }} Bookings</h3>
                <hr>
                <p class="text-muted text-right"><sup>1</sup> <small>Months are calendar months, NOT invoicing months.</small><br>
                <sup>2</sup> <small>Sales are gross sales before discounts.</small></p>

                <h4 class="mb-3">Monthly Bookings</h4>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="text-center">YTD <sup>({{ array_sum(array_column($mstats, 'days')) }})</sup></th>
                            @foreach ( cal_info(0)['abbrevmonths'] as $key => $month )
                                @php $class = now()->month == $key ? 'yellow' : ''; @endphp
                                <th class="text-center {{ $class }}">{{ $month }} <sup>({{ $days[$key] ?? '' }})</sup></th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Month</td>
                                <td class="text-center">{{ array_sum(array_column($mstats, 'count')) }}</td>
                                @foreach ( $mstats as $key => $stat )
                                    @php $class = now()->month == $key ? 'yellow' : ''; @endphp
                                    <td class="text-center {{ $class }}">{{ $stat['count'] }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td>per Day</td>
                                @if ( count($mstats) > 0 )
                                    <td class="text-center">{{ round(array_sum(array_column($mstats, 'count')) / array_sum(array_column($mstats, 'days'))) }}</td>
                                @else
                                    <td class="text-center">0</td>
                                @endif
                                @foreach ( $mstats as $key => $stat )
                                    @php $class = now()->month == $key ? 'yellow' : ''; @endphp
                                @if ( $stat['days'] == 0 )
                                    <td class="text-center {{ $class }}">0</td>
                                @else
                                    <td class="text-center {{ $class }}">{{ round(($stat['count'] / $stat['days'])) }}</td>
                                @endif
                                @endforeach
                            </tr>
                            <tr>
                                <td>Sales</td>
                                <td class="text-center">{{ number_format(array_sum(array_column($mstats, 'value'))) }}</td>
                                @foreach ( $mstats as $key => $stat )
                                    @php $class = now()->month == $key ? 'yellow' : ''; @endphp
                                    <td class="text-center {{ $class }}">{{ number_format($stat['value']) }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h4 class="mt-5 mb-3">Daily Bookings</h4>
                <div class="row">

                    @for ( $m = 1; $m <= 3; $m++ )
                        @php
                            if ( $m == 1 ) {
                                $per = 'prev';
                            } elseif ( $m == 2 ) {
                                $per = 'curr';
                            } else {
                                $per = 'next';
                            }
                            $head = \Carbon\Carbon::parse($months[$per])->format('F');
                        @endphp

                        <div class="col-lg-4">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead>
                                        @php
                                            $class = now()->format('F') == $head ? 'yellow' : '';
                                        @endphp
                                        <tr>
                                            <th colspan="7" class="text-center {{ $class }}"><h5 class="mb-0 py-1"><strong>{{ $head }}</strong></h5></th>
                                        </tr>
                                        <tr>
                                            <th class="text-center">M</th>
                                            <th class="text-center">T</th>
                                            <th class="text-center">W</th>
                                            <th class="text-center">Th</th>
                                            <th class="text-center">F</th>
                                            <th class="text-center">S</th>
                                            <th class="text-center">S</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            if ( count($dstats[$per]) == 0 ) {
                                                $f = \Carbon\Carbon::parse($months[$per])->dayOfWeekIso;
                                                $e = \Carbon\Carbon::parse($months[$per])->lastOfMonth()->dayOfWeekIso;
                                                $days = \Carbon\Carbon::parse($months[$per])->lastOfMonth()->day;
                                            } else {
                                                $f = \Carbon\Carbon::parse(key($dstats[$per]))->startOfMonth()->dayOfWeekIso;
                                                $e = \Carbon\Carbon::parse(key($dstats[$per]))->lastOfMonth()->dayOfWeekIso;
                                                $days = \Carbon\Carbon::parse(key($dstats[$per]))->lastOfMonth()->day;
                                            }
                                        @endphp
                                        <tr>

                                        {{-- empty days at start of month --}}
                                        @if ( $f != 1 )
                                            @for ( $i = 1; $i < $f; $i++ )
                                                <td></td>
                                            @endfor
                                        @endif

                                        {{-- month's days --}}
                                        @for ( $i = 1; $i <= $days; $i++ )
                                            @php
                                                $cd = \Carbon\Carbon::parse($months[$per])->addDays($i-1);
                                                $dt = $cd->copy()->toDateString();
                                                $class = in_array($dt, $hols) || $cd->isWeekend() ? 'grey' : '';
                                            @endphp
                                            @if ( $f > 7 )
                                                </tr><tr>
                                                @php $f = 1; @endphp
                                            @endif
                                            <td class="{{ $class }}"><sup class="text-info ml-0 mr-2">{{ $i }}</sup> {{ $dstats[$per][$dt] ?? ' ' }}</td>
                                            @php $f++; @endphp
                                        @endfor

                                        {{-- empty days at end of month --}}
                                        @if ( $e != 7 )
                                            @for ( $f; $f <= 7; $f++ )
                                                <td></td>
                                            @endfor
                                        @endif
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endfor

                </div>
            </section>
        </div>
    </div>

    {{--modals--}}
@stop

@section('script')
    @parent
@stop


@section('jquery')
    @parent
    <script>

        $(document).ready(function() {


        });
    </script>
@stop
