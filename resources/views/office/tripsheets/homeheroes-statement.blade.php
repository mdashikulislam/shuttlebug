@extends('layouts.front')

@section('title')
    <title>Home Heroes Statement</title>
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
                <h4>Summary Statement for Week <small>{{ \Carbon\Carbon::parse(Arr::first($dates))->weekOfYear }}:</small>
                    {{ \Carbon\Carbon::parse(Arr::first($dates))->format('j M') }} - {{ \Carbon\Carbon::parse(Arr::last($dates))->format('j M') }}</h4>
            </div>

            <div class="col-md">
                <div class="text-right mb-3">
                    <span class="mr-3">View Statement for</span>
                    {!! Form::text('week', null, ['class' => 'form-control form-control-sm datepicker float-right w-25', 'placeholder' => 'select week', 'id' => 'week']) !!}
                </div>
            </div>
        </div>

        <div class="row">
            @if ( count($statement) > 0 )
                <div class="table-responsive mt-5">
                    <table class="table table-striped table-bordered table-sm mb-4">
                        <thead>
                            <tr>
                                <th class="text-center">Week Total</th>
                                @foreach ( $dates as $date )
                                    <th class="text-center">
                                        {{ \Carbon\Carbon::parse($date)->format('j M') }}
                                    </th>
                                @endforeach
                            </tr>
                            <tr>
                                <td colspan="{{ count($dates) + 1 }}"></td>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <th class="text-center">R {{ array_sum(array_column($statement, 'day')) }}</th>
                                @foreach ( $dates as $date )
                                    @if ( isset($statement[$date]) && $statement[$date]['day'] > 0 )
                                        <td>
                                            @foreach ( $statement[$date] as $entry )
                                                @if ( isset($entry['value']) && $entry['value'] > 0 )
                                                    {{ $entry['passengers'] }} passengers @ R{{ $entry['price'] }} = R{{ $entry['value'] }}<br>
                                                @endif
                                            @endforeach
                                        </td>
                                    @else
                                        <td></td>
                                    @endif
                                @endforeach
                            </tr>
                            <tr style="background-color: #ffffcc;">
                                <th class="text-center"></th>
                                @foreach ( $dates as $date )
                                    @if ( isset($statement[$date]) )
                                        <td class="text-center">{{ (int) ($statement[$date]['day']) }}</td>
                                    @else
                                        <td></td>
                                    @endif
                                @endforeach
                            </tr>
                        <tbody>
                    </table>
                </div>
                <div>
                    <p class="small">The statement is final only on completion of the lifts on {{ \Carbon\Carbon::parse(Arr::last($dates))->format('j M') }}.<br>
                    Up until then the statement is subject to change.<br>
                    This statement is not available to Homeheroes.</p>
                </div>

            @elseif ( \Carbon\Carbon::parse(Arr::last($dates))->toDateString() > now()->toDateString() )
                <div class="alert alert-danger text-center mt-5">The trips for the week have not yet been processed<br>or there are no trips this week.</div>

            @else
                <div class="alert alert-info mt-5">No vehicles were required in the week.</div>
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
            let element = '.datepicker';

            $(element).datepicker('destroy');
            $(element).datepicker({
                format:         'yyyy-mm-dd',
                autoclose:      true,
                weekStart:      1,
                // startDate:      '2019-01-01',
                startView:      'days',
                todayHighlight: 'true',
            });

            {{-- load the statement for --}}
            $('#week').on('change', function() {
                if ( $(this).val() > '' ) {
                    window.location.href = '/hh/statement/' + $(this).val();
                }
            });
        });
    </script>
@stop
