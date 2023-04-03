@extends('layouts.office')

@section('title')
    <title>Financials</title>
@stop

@section('css')
    @parent
@stop

@section('style')
    @parent
    <style>
        .yellow { background: yellow; }
        .grey { background: #f2f2f2; }
        .italic { font-style: italic; }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">

            {{--local nav--}}
            @include('layouts.nav.nav-local-stats')

            {{--content--}}
            <section class="col-md col-12 pa-1 content">
                <h3 class="page-header"><small>Analytics:</small> {{ \Carbon\Carbon::now()->year }} Financials</h3>
                <hr>
                <p class="text-muted ml-3">Months are invoicing months, i.e. 29th to 28th.<br>
                <sup>*</sup> Estimated for current month
                </p>

                <h4 class="mb-3">Monthly</h4>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="text-right">{{ now()->subYear()->year }}</th>
                            <th class="text-right">{{ now()->year }} <sup>YTD</sup></th>
                            @foreach ( cal_info(0)['abbrevmonths'] as $key => $month )
                                @php $class = now()->month == $key ? 'yellow' : ''; @endphp
                                <th class="text-right {{ $class }}">{{ $month }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Bookings</td>
                                <td class="text-right">{{ number_format($lyear['bookings']) }}</td>
                                <td class="text-right">{{ number_format(collect($stats)->sum('bookings')) }}</td>
                                @foreach ( $stats as $month => $stat )
                                    @php $class = now()->month == $month ? 'yellow' : ''; @endphp
                                    <td class="text-right {{ $class }}">{{ number_format($stat['bookings']) }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td>Sales Value</td>
                                <td class="text-right">{{ number_format($lyear['gross']) }}</td>
                                <td class="text-right">{{ number_format(collect($stats)->sum('gross')) }}</td>
                                @foreach ( $stats as $month => $stat )
                                    @php $class = now()->month == $month ? 'yellow' : ''; @endphp
                                    <td class="text-right {{ $class }}">{{ number_format($stat['gross']) }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td>less: Discounts</td>
                                <td class="text-right">{{ number_format($lyear['gross']-$lyear['nett']) }}</td>
                                @if ( collect($stats)->sum('nett') > 0 || $curr_nett > 0 )
                                    <td class="text-right">{{ number_format(collect($stats)->sum('gross') - collect($stats)->sum('nett') - $curr_nett) }}</td>
                                @else
                                    <td></td>
                                @endif
                                @foreach ( $stats as $month => $stat )
                                    @php $class = now()->month == $month ? 'yellow' : ''; @endphp
                                    @if ( $stat['nett'] > 0 )
                                        <td class="text-right {{ $class }}">{{ number_format($stat['gross']-$stat['nett']) }}</td>
                                    @elseif ( $curr_nett > 0 )
                                        <td class="text-right {{ $class }}">{{ number_format($stat['gross'] - $curr_nett) }}</td>
                                    @else
                                        <td class="text-right {{ $class }}"></td>
                                    @endif
                                @endforeach
                            </tr>
                            <tr>
                                <td>Gross Income</td>
                                <td class="text-right">{{ number_format($lyear['nett']) }}</td>
                                @if ( collect($stats)->sum('nett') > 0 || $curr_nett > 0 )
                                    <td class="text-right">{{ number_format(collect($stats)->sum('nett') + $curr_nett) }}</td>
                                @else
                                    <td></td>
                                @endif
                                @foreach ( $stats as $month => $stat )
                                    @php $class = now()->month == $month ? 'yellow' : ''; @endphp
                                    @if ( $stat['nett'] > 0 )
                                        <td class="text-right {{ $class }}">{{ number_format($stat['nett']) }}</td>
                                    @elseif ( $curr_nett > 0 )
                                        <td class="text-right {{ $class }}">{{ number_format($curr_nett) }}</td>
                                    @else
                                        <td class="text-right {{ $class }}"></td>
                                    @endif
                                @endforeach
                            </tr>

                            <tr>
                                <td colspan="15">&nbsp;</td>
                            </tr>

                            <tr>
                                <td>less: COS Homeheroes <sup>*</sup></td>
                                <td class="text-right">{{ number_format($hhly) }}</td>
                                <td class="text-right">{{ number_format(collect($hh)->sum('value')) }}</td>
                                @foreach ( $hh as $month => $stat )
                                    @php $class = now()->month == $month ? 'yellow' : ''; @endphp
                                    <td class="text-right {{ $class }}">{{ number_format($stat['value']) }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td>Nett Income <sup>*</sup></td>
                                <td class="text-right">{{ number_format($lyear['nett'] - $hhly) }}</td>
                                @if ( collect($stats)->sum('nett') > 0 || $curr_nett > 0 )
                                    <td class="text-right">{{ number_format(collect($stats)->sum('nett') + $curr_nett - collect($hh)->sum('value')) }}</td>
                                @else
                                    <td></td>
                                @endif
                                @foreach ( $stats as $month => $stat )
                                    @php $class = now()->month == $month ? 'yellow' : ''; @endphp
                                    @if ( $stat['nett'] > 0 )
                                        <td class="text-right {{ $class }}">{{ number_format($stat['nett'] - $hh[$month]['value']) }}</td>
                                    @elseif ( $curr_nett > 0 )
                                        <td class="text-right {{ $class }}">{{ number_format($curr_nett - $hh[$month]['value']) }}</td>
                                    @else
                                        <td class="text-right {{ $class }}"></td>
                                    @endif
                                @endforeach
                            </tr>

                            <tr>
                                <td colspan="15">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="text-info">Avg Selling Price</td>
                                <td class="text-right text-info">{{ number_format($lyear['nett']/$lyear['bookings'],2) }}</td>
                                @if ( collect($stats)->sum('bookings') > 0 )
                                    <td class="text-right text-info">{{ number_format((collect($stats)->sum('nett') + $curr_nett) / collect($stats)->sum('bookings'),2) }}</td>
                                @else
                                    <td class="text-right text-info">0</td>
                                @endif
                                @foreach ( $stats as $month => $stat )
                                    @php $class = now()->month == $month ? 'yellow' : ''; @endphp
                                    @if ( $stat['bookings'] > 0 )
                                        @if ( $stat['nett'] > 0 )
                                            <td class="text-right text-info {{ $class }}">{{ number_format($stat['nett'] / $stat['bookings'],2) }}</td>
                                        @else
                                            <td class="text-right text-info {{ $class }}">{{ number_format($curr_nett / $stat['bookings'],2) }}</td>
                                        @endif
                                    @else
                                        <td class="text-right text-info {{ $class }}">0</td>
                                    @endif
                                @endforeach
                            </tr>
                            <tr>
                                <td class="text-info">Disc %</td>
                                <td class="text-right text-info">{{ number_format(($lyear['gross'] - $lyear['nett']) / $lyear['gross'] * 100,1) }}</td>
                                @if ( collect($stats)->sum('gross') > 0 )
                                    <td class="text-right text-info">{{ number_format((collect($stats)->sum('gross') - collect($stats)->sum('nett') - $curr_nett) / collect($stats)->sum('gross') * 100,1) }}</td>
                                @else
                                    <td class="text-right text-info">0</td>
                                @endif
                                @foreach ( $stats as $month => $stat )
                                    @php $class = now()->month == $month ? 'yellow' : ''; @endphp
                                    @if ( $stat['gross'] > 0 )
                                        @if ( $stat['nett'] > 0 )
                                            <td class="text-right text-info {{ $class }}">{{ number_format(($stat['gross'] - $stat['nett']) / $stat['gross'] * 100,1) }}</td>
                                        @else
                                            <td class="text-right text-info {{ $class }}">{{ number_format(($stat['gross'] - $curr_nett) / $stat['gross'] * 100,1) }}</td>
                                        @endif
                                    @else
                                        <td class="text-right text-info {{ $class }}">0</td>
                                    @endif
                                @endforeach
                            </tr>
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

        $(document).ready(function() {


        });
    </script>
@stop
