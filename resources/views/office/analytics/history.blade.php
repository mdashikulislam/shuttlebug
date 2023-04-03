@extends('layouts.office')

@section('title')
    <title>Historical Bookings</title>
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
            <section class="col-md col-12 pa-1 content">
                <h3 class="page-header"><small>Analytics:</small> Bookings History</h3>
                <hr>

                @if ( isset($delayed) )
                    <h4>Stats will be available from the 4th of January.</h4>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th><strong>Year</strong></th>
                                    <th class="text-center">Total</th>
                                    @foreach ( cal_info(0)['abbrevmonths'] as $key => $month )
                                        <th class="text-center">{{ $month }}</th>
                                    @endforeach
                                </tr>
                            <tr>
                                <td colspan="14">&nbsp;</td>
                            </tr>
                            </thead>
                            @foreach ( $stats as $year => $mstats)
                                <tbody>
                                    <tr class="grey">
                                        <th><strong>{{ $year }}</strong></th>
                                        <td colspan="13"><small>{{ collect($mstats)->sum('days') }} days
                                        @if ( collect($mstats)->sum('count') > 0 )
                                            <span class="ml-3">R {{ round(collect($mstats)->sum('value') / collect($mstats)->sum('count'),0) }} /lift</span></small>
                                        @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Month</td>
                                        <td class="text-center"><strong>{{ collect($mstats)->sum('count') }}</strong></td>
                                        @foreach ( $mstats as $key => $stat )
                                            <td class="text-center">{{ $stat['count'] }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>per Day</td>
                                        @if ( collect($mstats)->sum('count') > 0 )
                                            <td class="text-center"><strong>{{ round(collect($mstats)->sum('count') / collect($mstats)->sum('days')) }}</strong></td>
                                        @else
                                            <td class="text-center">0</td>
                                        @endif
                                        @foreach ( $mstats as $key => $stat )
                                            @if ( $stat['days'] == 0 )
                                                <td class="text-center">0</td>
                                            @else
                                                <td class="text-center">{{ round(($stat['count'] / $stat['days'])) }}</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Sales <small>(K)</small></td>
                                        @if ( collect($mstats)->sum('value') > 0 )
                                            <td class="text-center"><strong>{{ number_format(collect($mstats)->sum('value')/1000,1) }}</strong></td>
                                        @else
                                            <td class="text-center">0</td>
                                        @endif
                                        @foreach ( $mstats as $key => $stat )
                                            <td class="text-center">{{ number_format($stat['value']/1000,1) }}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            @endforeach
                        </table>
                    </div>
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

        $(document).ready(function() {


        });
    </script>
@stop
