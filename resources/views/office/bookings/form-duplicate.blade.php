@extends('layouts/office')

@section('title')
    <title>Duplicating Form</title>
@stop

@section('css')
    @parent
    <link rel="stylesheet" href="{{ asset('css/jquery.timepicker.css') }}">
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-bookings')

{{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

{{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        <h3>
                            <small>Duplicate Bookings for</small> {{ $passenger->name }}
                        </h3>
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
                <hr class="mb-5">

{{-- form ----------------------------------------------------------------------------------------}}

                {!! Form::open(['url' => 'office/bookings/duplicate/store', 'id' => 'capture']) !!}
                    <div class="row">
                        <div class="col-12 col-lg-8 col-xl-7">

                    {{-- source bookings ---------------------------------------------------------------------}}

                            <h6><strong>Select the bookings to duplicate</strong></h6>
                            <ul class="text-muted d-lg-none">
                                <li><small>Bookings will be duplicated to matching days. i.e. Tuesdays to Tuesdays.</small></li>
                                <li><small>Existing bookings on the target date will be removed.</small></li>
                            </ul>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Day</th>
                                            <th>Pick Up</th>
                                            <th>At</th>
                                            <th>Drop Off</th>
                                            <th>By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $day = ''; @endphp
                                        @foreach($source as $row)
                                            <tr>
                                                @if ( $day != \Carbon\Carbon::createFromFormat('Y-m-d',$row->date)->format('l') )
                                                    <td class="text-center pt-0">
                                                        <label class="control control-checkbox mt-1">
                                                            {!! Form::checkbox('source[]', $row->id, null) !!}
                                                            <span class="control_indicator"></span>
                                                        </label>
                                                    </td>
                                                @else
                                                    <td></td>
                                                @endif
                                                <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d',$row->date)->format('l') }}</td>
                                                <td>{{ $row->puloc_type == 'user' ? 'home' : $row->puloc->name }}</td>
                                                <td>{{ $row['putime'] > 0 ? substr($row['putime'],0,5) : '' }}</td>
                                                <td>{{ $row->doloc_type == 'user' ? 'home' : $row->doloc->name }}</td>
                                                <td>{{ $row['dotime'] > 0 ? substr($row['dotime'],0,5) : '' }}</td>
                                            </tr>
                                            @php $day = \Carbon\Carbon::createFromFormat('Y-m-d',$row->date)->format('l'); @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                                <span class="ml-2 small"><a id="checkall" href="#">Select All</a></span>
                            </div>

                            @if ( count($source) > 0 )
                                <h6 class="mt-4"><strong>Choose Duplicating Option</strong></h6>
                                <div class="row">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="ml-3">
                                            <label class="control control-radio mt-2">Duplicate for a Week
                                                {!! Form::radio('option', 'week', true) !!}
                                                <span class="control_indicator"></span>
                                            </label><br>
                                            {{--<label class="control control-radio">Duplicate for a Month--}}
                                                {{--{!! Form::radio('option', 'month', null) !!}--}}
                                                {{--<span class="control_indicator"></span>--}}
                                            {{--</label><br>--}}
                                            <label class="control control-radio mt-1">Duplicate for a Term
                                                {!! Form::radio('option', 'term', null) !!}
                                                <span class="control_indicator"></span>
                                            </label><br>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-group mt-2">
                                            {!! Form::label("date", 'Starting From') !!}
                                            {!! Form::text('date', null, ['class' => 'form-control datepicker', 'placeholder' => 'date', 'required']) !!}
                                        </div>
                                    </div>
                                    <div class="col-lg-4 text-muted ml-3 ml-lg-0">
                                        Terms:<br>
                                        @foreach ( $terms as $term )
                                            <small>
                                            {{ $loop->index+1 }}
                                                <span class="ml-2">{{  $term->start }}</span> <i class="fa fa-long-arrow-right"></i>
                                                {{ $term->end }}
                                            </small><br>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="mt-5">There are no bookings in this week to duplicate.</p>
                            @endif
                        </div>

                        <div class="col-lg-4 col-xl-5 text-muted small d-none d-lg-block">
                            <ul class="mt-5">
                                <li>All the bookings on selected days will be duplicated.</li>
                                <li>Bookings will be duplicated to matching days. i.e. Tuesdays to Tuesdays.</li>
                                <li>Bookings will not be duplicated on holidays.</li>
                                <li>Existing bookings on the target date will be removed.</li>
                                <li class="dropdown-divider"></li>
                                <li>Your Starting date is the first day of duplication.</li>
                                <li>The end of the Option period is the last day of duplication e.g. last day of that week.</li>
                                <li>For example:<br>If you duplicate all the bookings in the week to a term, Monday's bookings will be duplicated on all the Mondays in the term starting from your Starting date etc.<br><br>If you select just Tuesday's bookings and duplicate for a Month, the bookings will be duplicated on all Tuesdays in that month starting from your Starting date.</li>
                            </ul>
                        </div>

                        <div class="col-12"></div>

                    {{-- submit ----------------------------------------------------------------------------------------}}

                        <div class="col-lg-6 col-xl-4 mt-4 mb-5">
                            {!! Form::submit('Duplicate', ['class' => 'btn btn-primary']) !!}
                        </div>
                    </div>
                {!! Form::close() !!}
            </section>
        </div>
    </div>
@stop

@section('script')
    @parent
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAX1xKuub_cJqm_icVEDQ_iqe1iESyVXNY"></script>
    <script src="{!! asset('js/jquery.timepicker.min.js') !!}"></script>
@stop


@section('jquery')
    @parent
    <script>
{{--        let holidays = {!! json_encode($holidays) !!};--}}

        $(function() {
            $('.datepicker').datepicker('setStartDate', "{!! now()->addDay()->toDateString() !!}");
            $('.datepicker').datepicker('setEndDate', "{!! now()->endOfYear()->toDateString() !!}");

            {{-- toggle checkboxes --}}
            $(document).on('click', '#checkall', function () {
                if ( $(this).html() === 'Select All' ) {
                    $('input:checkbox').prop('checked', true);
                    $(this).html('Select None');
                }
                else {
                    $('input:checkbox').prop('checked', false);
                    $(this).html('Select All');
                }
            });

        });
    </script>
@stop