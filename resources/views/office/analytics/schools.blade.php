@extends('layouts.office')

@section('title')
    <title>School Bookings</title>
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
                <h3 class="page-header"><small>Analytics:</small> School Bookings</h3>
                <hr>
                @if ( isset($delayed) )
                    <h4>Stats will be available from the 4th of January.</h4>
                @else
                    <p class="text-muted mt-1 mb-3"><small>The current year is also shown with annualised bookings.<br>Click on heading to sort ascending, click again to sort descending.</small></p>
                    <table class="dtable display mt-3" data-order='[[ 1, "desc" ]]' cellspacing="0" width="100%" id="index-table">
                        <thead>
                        <tr>
                            <th>School<br><small>days</small></th>
                            @foreach ( $stats as $year => $stat )
                                @if ( $year == now()->year )
                                    <th class="text-center">{{ $year }} ytd.<br><small>{{ $days['ytd'] }}</small></th>
                                    <th class="text-center">{{ $year }} pa.<br><small>{{ $days[$year] }}</small></th>
                                @else
                                    <th class="text-center">{{ $year }}<br><small>{{ $days[$year] }}</small></th>
                                @endif
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ( $schools as $id => $school )
                            <tr>
                                <td>{{ $school }}</td>
                                @foreach ( $stats as $year => $stat )
                                    @if ( $year == now()->year )
                                        @php
                                            $count = collect($stat)->where('id', $id)->first()['bookings_count'] ?? '';
                                            $count = $count == '' ? '' : round($count / $days['ytd'] * $days[$year]);
                                        @endphp
                                        <td class="text-center">{{ collect($stat)->where('id', $id)->first()['bookings_count'] ?? '' }}
                                        <td class="text-center">{{ $count }}
                                    @else
                                        <td class="text-center">{{ collect($stat)->where('id', $id)->first()['bookings_count'] ?? '' }}
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
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
        $(document).ready(function() {
            {{-- datatable --}}
            table = $('#index-table').DataTable( {"dom": 'lrtip'} );
            table.on('select', function ( e, dt, type, indexes ) {
                handleRowSelect(table.rows( indexes ).data().toArray()[0][0]);
            }).on('deselect', function () {
                handleRowDeselect();
            });

        });
    </script>
@stop