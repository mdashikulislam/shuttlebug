@extends('layouts.office')

@section('title')
    <title>Testing</title>
@stop

@section('css')
    @parent
@stop

@section('style')
    @parent
    <style>
        .yellow { background-color: yellow; }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <section class="col-md col-12 pa-1 content">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Time</th>
                            @foreach ( $venues as $venue )
                                <th class="text-center">{{ $venue }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $locations as $time => $places )
                            <tr>
                                <td>{{ $time }}</td>
                                @foreach ( $venues as $venue )
                                    @if ( isset($places[$venue]) )
                                        @php $class = $places[$venue] > 104 ? 'yellow' : ''; @endphp
                                        <td class="text-center {{ $class }}">{{ $places[$venue] }}</td>
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