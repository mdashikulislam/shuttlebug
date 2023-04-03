@extends('layouts/office')

@section('title')
    <title>Public Holidays Form</title>
@stop

@section('css')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-holidays')

{{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

{{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        @if ( !is_null($public) )
                            <h3>Edit {{ $year }} Public Holidays</h3>
                        @else
                            <h3>Add {{ $year }} Public Holidays</h3>
                        @endif
                    </div>

{{-- messages ----------------------------------------------------------------------------------------}}

                    <div class="col-lg">
                        @if ( session()->has('confirm') )
                            <div class="alert-success alert-temp">{{ session('confirm') }}</div>
                        @elseif ( session()->has('warning') )
                            <div class="alert-warning alert-temp">{{ session('warning') }}</div>
                        @elseif ( session()->has('danger') )
                            <div class="alert-danger alert-temp">{{ session('danger') }}</div>
                        @elseif ( count($errors) > 0 )
                            <div class="alert-danger alert-temp">Some required data is missing.</div>
                        @endif
                    </div>
                </div>
                <hr class="mt-2 mb-5">

{{-- form ----------------------------------------------------------------------------------------}}

                {!! Form::open(['url' => 'office/holidays/public/store', 'id' => 'capture']) !!}

                    <div class="row">
                        <div class="col-md-11 col-lg-8 col-xl-6">

                            <p class="text-muted pb-3"><small>To remove a holiday delete its date.</small></p>

                {{-- existing --------------------------------------------------------------------------------------}}

                            @if ( !is_null($public) )
                                @foreach ( $public as $holiday )
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <div class="input-group">
                                                <div class="input-group-addon my-auto">Name&nbsp;</div>
                                                {!! Form::text("hol[$holiday->id][day]", $holiday->day, ['class' => "form-control"]) !!}
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="input-group mt-1 mt-sm-0 ml-3 ml-sm-0">
                                                <div class="input-group-addon my-auto">Date&nbsp;</div>
                                                {!! Form::text("hol[$holiday->id][date]", $holiday->date, ['class' => "form-control datepicker"]) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else

                {{-- new ----------------------------------------------------------------------------------------}}

                                @php $i = 0; @endphp
                                @foreach ( $days as $day )
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <div class="input-group">
                                                <div class="input-group-addon my-auto">Name&nbsp;</div>
                                                {!! Form::text("newhol[$i][day]", $day, ['class' => "form-control"]) !!}
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="input-group mt-1 mt-sm-0 ml-3 ml-sm-0">
                                                <div class="input-group-addon my-auto">Date&nbsp;</div>
                                                {!! Form::text("newhol[$i][date]", null, ['class' => "form-control datepicker"]) !!}
                                            </div>
                                        </div>
                                    </div>
                                    @php $i++; @endphp
                                @endforeach
                            @endif

                {{-- spares -----------------------------------------------------------------------------------}}

                            @php $n = !isset($i) ? 16-count($public) : 16-$i; @endphp
                            @for ( $i = 0; $i <= $n; $i++ )
                                <div class="form-group row">
                                    <div class="col-sm-6">
                                        <div class="input-group">
                                            <div class="input-group-addon my-auto">Name&nbsp;</div>
                                            {!! Form::text("newhol[$i][day]", null, ['class' => "form-control"]) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="input-group mt-1 mt-sm-0 ml-3 ml-sm-0">
                                            <div class="input-group-addon my-auto">Date&nbsp;</div>
                                            {!! Form::text("newhol[$i][date]", null, ['class' => "form-control datepicker"]) !!}
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <div class="col-lg-5 col-xl-5 ml-xl-3">
                        </div>

                        <div class="col-md-11 col-lg-12 col-xl-10"><hr class="mb-1"></div>
                        <div class="col-xl-12"></div>

                {{-- submit ----------------------------------------------------------------------------------------}}

                        <div class="col-lg-6 col-xl-4 mt-4 mb-5">
                            {!! Form::hidden('year', $year) !!}
                            @if ( is_null($public) )
                                {!! Form::submit('Save Holidays', ['class' => 'btn btn-primary']) !!}
                            @else
                                {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
                            @endif
                        </div>
                    </div>
                {!! Form::close() !!}
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
        let year = "{{ $year.'-01-01' }}";
        let endofyear = "{{ $year.'-12-31' }}";

        $(function() {
            $('.datepicker').datepicker('setStartDate', year);
            $('.datepicker').datepicker('setEndDate', endofyear);
        });
    </script>
@stop