@extends('layouts/office')

@section('title')
    <title>Debtors Journal</title>
@stop

@section('css')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-debtors')

{{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

{{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        <h3>Post Journal Entry</h3>
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

                {!! Form::open(['url' => 'office/debtors/journal/store', 'id' => 'capture']) !!}

                    <div class="row">
                        <div class="col-md-11 col-lg-6 col-xl-4">
                            <div style="background:#f9f9f9">
                                <p class="text-muted"><small>If the bank statement doesn't show the customer name you can find the customer by their id, or by the child's surname if it differs from the customer.</small></p>
                                <div class="row">
                                    <div class="col text-muted">
                                        {!! Form::label('byid', 'Find Customer by ID', ['class' => 'form-label small']) !!}
                                        <div class="input-group">
                                            {!! Form::text('byid', '', ['class' => "form-control form-control-sm"]) !!}
                                            <div class="input-group-addon btn btn-sm btn-outline-dark" id="idbtn" role="button">
                                                <i class="fa fa-search"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col text-muted">
                                        <div class="form-group">
                                            {!! Form::label('bychild', 'Find Customer by Child', ['class' => 'form-label small']) !!}
                                            {!! Form::select('bychild', ['' => ' '] +$children, '', ['class' => "form-control form-control-sm custom-select"]) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="mt-0">

                    {{-- customer ----------------------------------------------------------------------------------}}

                            <div class="form-group row mt-5">
                                {!! Form::label('user_id', 'Customer', ['class' => 'col col-form-label']) !!}
                                <div class="col-md-9 col-lg-8">
                                    {!! Form::select('user_id', ['' => 'Select Customer'] +$customers, null, ['class' => "form-control custom-select", 'required']) !!}
                                </div>
                            </div>

                    {{-- entry --------------------------------------------------------------------------------------}}

                            <div class="form-group row">
                                {!! Form::label('entry', 'Transaction', ['class' => 'col col-form-label']) !!}
                                <div class="col-md-9 col-lg-8">
                                    {!! Form::select('entry', $entries, null, ['class' => "form-control custom-select", 'required']) !!}
                                    <div class="text-muted"><small>To credit a cancelled booking, cancel it in Bookings.</small></div>
                                </div>
                            </div>

                    {{-- amount ----------------------------------------------------------------------------------}}

                            <div class="form-group row">
                                {!! Form::label('amount', 'Amount', ['class' => 'col col-form-label']) !!}
                                <div class="col-md-9 col-lg-8">
                                    {!! Form::text('amount', null, ['class' => "form-control", 'required']) !!}
                                    <div class="text-muted"><small>Last Statement Balance: <span id="bal"></span></small></div>
                                </div>
                            </div>

                    {{-- type ----------------------------------------------------------------------------------------}}

                            <div class="form-group row">
                                {!! Form::label('type', 'Type', ['class' => 'col col-form-label']) !!}
                                <div class="col-md-9 col-lg-8">
                                    <label class="control control-radio mt-1">Bank
                                        {!! Form::radio('type', '', true) !!}
                                        <span class="control_indicator"></span>
                                    </label>
                                    <label class="control control-radio ml-2 mt-1">Cash
                                        {!! Form::radio('type', 'cash', null) !!}
                                        <span class="control_indicator"></span>
                                    </label>
                                </div>
                            </div>

                    {{-- date ----------------------------------------------------------------------------------------}}

                            <div class="form-group row">
                                {!! Form::label('date', 'Date', ['class' => 'col col-form-label']) !!}
                                <div class="col-md-9 col-lg-8">
                                    {{-- handle postings on month end --}}
                                    @if ( now()->day == 28 )
                                        @if ( now()->hour >= 12 )
                                            {{-- force date to tomorrow --}}
                                            {!! Form::text('date', now()->addDay()->toDateString(), ['class' => "form-control", 'readonly']) !!}
                                            <p class="text-muted"><small>Month-end has closed so transaction will be dated tomorrow to include it in next month's invoice.</small></p>
                                        @else
                                            {{-- force date to yesterday --}}
                                            {!! Form::text('date', now()->subDay()->toDateString(), ['class' => "form-control", 'readonly']) !!}
                                            <p class="text-muted"><small>Today is month-end so transaction will be dated yesterday so it's included in current invoice.</small></p>
                                        @endif
                                    @else
                                        {!! Form::text('date', null, ['class' => "form-control datepicker", 'required']) !!}
                                        <p class="text-muted"><small>Transaction dates are restricted to the current invoicing month.</small></p>
                                    @endif
                                </div>
                            </div>

                    {{-- submit -------------------------------------------------------------------------------------}}

                            <hr class="mt-4 mb-2">
                            {!! Form::submit('Save Transaction', ['class' => 'btn btn-primary']) !!}
                        </div>

                        <div class="col-lg-6 col-xl-5 offset-xl-1">

                    {{-- table ----------------------------------------------------------------------------------------}}

                            <div class="row mt-3 mt-xl-0">
                                <div class="col-7">
                                    <h6><strong>Recent Transactions</strong></h6>
                                </div>
                                <div class="col-5">
                                    <input type="search" id="tablesearch" placeholder="search table" class="form-control form-control-sm">
                                </div>
                            </div>
                            {{--<h6 class="mt-3 mt-xl-0"><strong>Recent Transactions</strong></h6>--}}
                            <span class="text-muted"><small>This table is shown just in case you need to check if a transaction has already been processed.</small></span>
                            <table class="dtable display compact small" data-order='' cellspacing="0" width="100%" id="index-table">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Transaction</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ( $transactions as $entry )
                                    <tr>
                                        <td>{{ $entry->user->alpha_name }}</td>
                                        <td>{{ $entry->entry }}</td>
                                        <td class="text-center">{{ $entry->amount }}</td>
                                        <td>{{ $entry->date }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
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

        $(function() {

            {{-- datepicker --}}
            $('.datepicker').datepicker('setStartDate', "{!! $invMonth->start !!}");
            $('.datepicker').datepicker('setEndDate', "{!! now()->toDateString() !!}");

            {{-- datatable --}}
            table = $('#index-table').DataTable( {"dom": 'lrtip'} );

            {{-- find customer by id --}}
            $('#idbtn').on('click', function() {
                let value = $('#byid').val();
                if ( value > '' ) {
                    $('#user_id').val(value);
                } else {
                    $('#user_id').val('');
                }
            });

            {{-- find customer by child --}}
            $('#bychild').on('change', function() {
                let value = $(this).val();
                if ( value > '' ) {
                    $('#user_id').val(value);
                } else {
                    $('#user_id').val('');
                }
            });

            {{-- load the selected customer's latest balance --}}
            $('#user_id').on('change', function() {
                if ( $(this).val() > '' ) {
                    $.ajax({
                        type: "GET",
                        url: '/office/debtors/balance/' + $(this).val(),
                        success: function (data) {
                            $('#bal').empty().html(data);
                        }
                    });
                } else {
                    $('#bal').empty();
                }
            });

        });
    </script>
@stop