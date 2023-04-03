@extends('layouts.office')

@section('title')
    <title>Day Plan</title>
@stop

@section('css')
    @parent
    <style>
        .bg-light { background-color: #dee2e6 !important; }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-tripplans')

        {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-lg col-12 pa-1 content d-none d-md-block" id="content">

        {{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-md-6 col-xl-3 my-auto">
                        <h3>Trip Plan: Day</h3>
                        <h4>{{ Carbon\Carbon::parse($request->date)->format('l j F Y') }}</h4>
                        <h5 class="text-muted">{{ collect($vehicles)->sum('pass') }} Passengers</h5>
                    </div>

        {{-- message ----------------------------------------------------------------------------------------}}

                    <div class="col-md-6 col-xl-3 my-auto">
                        @if ( $report )
                            <span class="text-muted small">
                                Updated {{ Carbon\Carbon::parse($report->updated_day)->diffForHumans() }}.<br>
                                @if ( isset($runtime) )
                                    Runtime: {{ $runtime }} secs.<br>
                                @endif
                                Vehicles: {{ count($report->day_vehicles) }}<br>
                                Passengers:
                                @foreach ( $vehicles as $vid => $vehicle )
                                    {{ $vid }}: {{ $vehicle['pass'] }},
                                @endforeach
                            </span>
                        @endif
                    </div>

        {{-- buttons ----------------------------------------------------------------------------------------}}

                    <div class="col-xl-6 my-auto text-center">
                        <button class="btn btn-primary btn-sm hack" data-call="update">Update Vehicle Changes</button>
                        <button class="btn btn-primary btn-sm hack" data-call="remove">Remove Vehicle Changes</button>
                        <button id="rankbtn" class="btn btn-info btn-sm">Show Ranking</button>
                        <a class="btn btn-danger btn-sm ml-2" href="{!! url('office/operations/tripplans/plan/rerun') !!}">Force Re-run</a>
                    </div>
                </div>
                <hr class="mb-3">

        {{-- errors and warnings ----------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-xl-6">
                        @if ( $report === false )
                            There are no trips on the {{ Carbon\Carbon::parse($request->date)->format('j F') }}.
                        @elseif ( is_null($report) )
                            <div class="alert alert-danger mt-5 text-center">
                                <p>There was a problem producing this trip plan.</p>
                                <p>The webmaster has been notified.</p>
                            </div>
                        @endif
                        @if ( count($report->warnings) > 0 )
                            <div class="text-warning">
                                @foreach ( $report->warnings as $key => $warning )
                                    {{ $warning['warning'] }}<br>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="col-xl-6">
                        @if ( !is_null($warn_vehicles) )
                            <span class="text-danger">{{ $warn_vehicles }}</span>
                            <small class="text-muted ml-3">Re-run using your new vehicles</small>
                            <a class="btn btn-danger btn-sm ml-2" href="{!! url('office/operations/tripplans/plan/rerun') !!}">Re-run</a>
                        @endif
                    </div>
                </div>

        {{-- table ----------------------------------------------------------------------------------------}}

@if ( $report && !is_null($report) )
    {!! Form::open(['url' => 'office/operations/tripplans/hack', 'id' => 'hackform']) !!}
        <table class="table table-bordered table-sm mt-3">
            <tbody>
            @php $p = 0; @endphp
            @foreach( $report->day as $pickup )
                <tr>

    {{-- trips ----------------------------------------------------------------------------------}}

                    <td class="bg-light w-75">

                        <table class="table table-bordered table-sm">
                            <tbody>

                            {{-- head --}}
                            <tr>
                                <td colspan="{{ count($report->day_vehicles) * 2 + 1 }}" class="">
                                    <small class="text-muted">Pickup: {{ $pickup['pickup']['leg'] }}</small>
                                    <strong class="ml-3">
                                        {{ $pickup['pickup']['time'] }} at {{ $pickup['pickup']['venue'] }}. {{ array_sum($pickup['pickup']['passengers']) }} {{ array_sum($pickup['pickup']['passengers']) > 1 ? 'passengers' : 'passenger' }}
                                        @if ( $pickup['pickup']['zone'] != 'in' )
                                            <span class="text-danger ml-3">({{ $pickup['pickup']['zone'] }})</span>
                                        @endif
                                    </strong>
                                    @php
                                       $selected = $pickup['ranking'][key($pickup['free'])];
                                    @endphp
                                    {{--<span class="float-right">--}}
                                        {{--@if ( strpos($selected, 'reserved') === false &&--}}
                                            {{--strpos($selected, 'waiting') === false &&--}}
                                            {{--count($pickup['available']) > 1 )--}}
                                            {{--<select class="form-control-sm custom-select">--}}
                                                {{--<option value="0">Default</option>--}}
                                                {{--@foreach ( array_keys($pickup['available']) as $v )--}}
                                                    {{--@if ( $vehicles[$v]['seats'] >= array_sum($pickup['pickup']['passengers']) )--}}
                                                        {{--@if ( key($pickup['free']) == $v )--}}
                                                            {{--<option selected value="{{ $v }}">{{ $vehicles[$v]['att'] }}</option>--}}
                                                        {{--@else--}}
                                                            {{--<option value="{{ $v }}">{{ $vehicles[$v]['att'] }}</option>--}}
                                                        {{--@endif--}}
                                                    {{--@endif--}}
                                                {{--@endforeach--}}
                                            {{--</select>--}}
                                        {{--@endif--}}
                                    {{--</span>--}}
                                </td>
                            </tr>

                            {{-- vehicles --}}
                            <tr>
                                <td class=""></td>
                                @foreach( $vehicles as $vid => $vehicle )
                                    @php $class= isset($pickup['free'][$vid]) ? 'table-success' : ''; @endphp
                                    <td colspan="2" class="text-center {{ $class }}">
                                        <strong>
                                            {{ $vid == 102 ? 'Innova' : $vehicle['seats'].' seater' }}
                                            <small>( {{ $vehicle['att'] }} )</small>
                                            @if ( isset($pickup['vzone'][$vid]) && $pickup['vzone'][$vid] != 'in' )
                                                <span class="text-danger">({{ $pickup['vzone'][$vid] }})</span>
                                            @endif
                                        </strong>
                                    </td>
                                @endforeach
                            </tr>
                            <tr>
                                <td>
                                @foreach( $vehicles as $vid => $vehicle )
                                    @php $class= isset($pickup['free'][$vid]) ? 'table-success' : ''; @endphp
                                    <td class="text-center {{ $class }}"><strong>seats</strong></td>
                                    <td class="text-center {{ $class }}"><strong>arrives</strong></td>
                                @endforeach
                            </tr>

                            @foreach( $pickup as $key => $data )

                            {{-- available --}}
                                @if( $key == 'available' )
                                    <tr>
                                        <td>Available</td>
                                        @foreach( $vehicles as $vid => $vehicle )
                                            @php $i = 0; @endphp
                                            @foreach( $data as $v => $item )
                                                @if( $v == $vid )
                                                    @php
                                                        $class = isset($pickup['free'][$vid]) ? 'table-success' : '';
                                                        $flag = substr($item,0,strpos($item,'~')) < $vehicle['seats'] && $class == 'table-success' ? 'table-danger' : $class;
                                                    @endphp
                                                    <td class="text-center {{ $flag }}">
                                                        @if ( $class == '' && substr($item,0,strpos($item,'~')) < $vehicle['seats'] )
                                                            <span class="text-danger">{{ substr($item,0,strpos($item,'~')) }}</span>
                                                        @else
                                                            {{ substr($item,0,strpos($item,'~')) }}
                                                        @endif
                                                    </td>
                                                    <td class="text-center {{ $class }}">{{ substr($item,strpos($item,'~')+1) }}</td>
                                                    @php $i = 1; @endphp
                                                @endif
                                            @endforeach
                                            @if ( $i == 0 )
                                                <td></td>
                                                <td></td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endif

                            {{-- ranking --}}
                                @if( $key == 'ranking' )
                                    <tr class="rankdesc d-none">
                                        <td>Ranking</td>
                                        @foreach( $vehicles as $vid => $vehicle )
                                            @php $i = 0; @endphp
                                            @foreach( $data as $v => $item )
                                                @if( $v == $vid )
                                                    @php $class = isset($pickup['free'][$vid]) ? 'table-success' : ''; @endphp
                                                    <td colspan="2" class="text-center {{ $class }}">
                                                        <small class="text-muted">{{ substr($item, 0, strrpos($item, ',')) }}</small><br>
                                                        {{ substr($item, strrpos($item, ',')+1) }}
                                                    </td>
                                                    @php $i = 1; @endphp
                                                @endif
                                            @endforeach
                                            @if ( $i == 0 )
                                                <td colspan="2"></td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endif

                            {{-- selected --}}
                                @if( $key == 'pass' )
                                    <tr>
                                        <td>Pass / Free</td>
                                        @foreach( $vehicles as $vid => $vehicle )
                                            @php $i = 0; @endphp
                                            @foreach( $data as $v => $item )
                                                @if( $v == $vid )
                                                    @php $class = isset($pickup['free'][$vid]) ? 'table-success' : ''; @endphp
                                                    <td colspan="2" class="text-center {{ $class }}">
                                                        {{ $item }} pass
                                                        @foreach( $pickup['free'] as $fv => $t )
                                                            @if( $fv == $vid )
                                                                <small>( free @ {{ $t }} )</small>
                                                            @endif
                                                            @php $i = 1; @endphp
                                                        @endforeach
                                                    </td>
                                                @endif
                                            @endforeach
                                            @if ( $i == 0 )
                                                <td colspan="2"></td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </td>

    {{-- hacks ----------------------------------------------------------------------------------}}

                    <td class="align-middle text-center bg-light">
                        @php
                            $options = [];
                            $time = $pickup['pickup']['time'];
                            $short = $pickup['pickup']['venue'];
                            $hack = collect($hacks)->where('putime', $time)->where('pushort', $short)->first();
                            foreach ( $vehicles as $ix => $data ) {
                                if ( in_array($ix, array_keys($pickup['available'])) ) {
                                    $options[$ix] = $data['att'];
                                }
                            }
                        @endphp
                        @if ( !is_null($hack) )
                            <div class="alert alert-danger text-center">Vehicle changed to {{ $vehicles[$hack->vehicle]['att'] }}</div>
                            Change to:<br>
                            <span class="">{!! Form::select("hacks[$hack->id][$time][$short]", ['' => 'Auto'] + $options, $hack->vehicle, ['class' => 'form-control-sm custom-select', 'style' => 'width:70%']) !!}</span>
                            @php $n = 1; @endphp
                        @elseif ( strpos($selected, 'reserved') === false &&
                            strpos($selected, 'waiting') === false &&
                            count($pickup['available']) > 1 &&
                            count($pickup['free']) == 1 )
                            Change Vehicle to:<br>
                            <span class="">{!! Form::select("hacks[$p][$time][$short]", ['' => 'Auto'] +$options, null, ['class' => 'form-control-sm custom-select', 'style' => 'width:70%']) !!}</span>
                            @php $p++; @endphp
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="{{ count($report->day_vehicles) * 2 + 1 }}" class="bg-light">&nbsp;</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {!! Form::hidden('call', '', ['id' => 'formcall']) !!}
        {!! Form::hidden('date', $request->date) !!}
    {!! Form::close() !!}
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
    $(function() {
        {{-- rank detail --}}
            $('#rankbtn').on('click', function() {
                if ( $(this).text().trim() === 'Show Ranking' ) {
                    $('.rankdesc').removeClass('d-none');
                    $(this).text('Hide Ranking');
                } else {
                    $('.rankdesc').addClass('d-none');
                    $(this).text('Show Ranking');
                }
            });

            {{-- submit hacks --}}
            $('.hack').on('click', function() {
                $('#formcall').val($(this).data('call'));
                $( "#hackform" ).submit();
            });
        });
    </script>
@stop