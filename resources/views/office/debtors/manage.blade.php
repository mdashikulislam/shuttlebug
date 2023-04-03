@extends('layouts.office')

@section('title')
    <title>Manage Debtors</title>
@stop

@section('css')
    @parent
@stop

@section('style')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-debtors')

        {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 mb-4 content" id="content">
                <div class="row">
                    <div class="col-md">
                        <h3 class="page-header">Manage Debtors</h3>
                    </div>

                    <div class="col-md">
                        @if ( session()->has('confirm') )
                            <div class="alert-success alert-temp">{{ session('confirm') }}</div>
                        @elseif ( session()->has('warning') )
                            <div class="alert-warning alert-temp">{{ session('warning') }}</div>
                        @elseif ( session()->has('danger') )
                            <div class="alert-danger alert-temp">{{ session('danger') }}</div>
                        @endif
                    </div>
                </div>
                <hr class="mt-2">

                <div class="row">

                {{-- notes ----------------------------------------------------------------------------------------}}

                    <div class="col col-xl-6 text-muted small mt-3">
                        <ul>
                            <li>Invoices & statements are sent to customers on the <u>28th</u> of each month.</li>
                            <li>Reminders are sent to customers with outstanding balances on the <u>7th</u> of each month.</li>
                            <li>It should only be necessary to follow-up outstanding customers after the <u>12th</u> of the month.</li>
                            <li>All payments should be processed before these dates to make sure balances are up to date.</li>
                        </ul>
                    </div>

                    <div class="col-12"></div>

                    <div class="col-sm-8 col-md-6 col-lg-5 col-xl-3">

                {{-- invoice ----------------------------------------------------------------------------------------}}

                        <h5 class="mt-5">View Customer Statement/Invoice</h5>
                        <p class="text-muted ml-3 mb-0"><small>Invoice & Statement for selected customer.</small></p>
                        <form class="mt-2 ml-3">
                            <label class="sr-only" for="role">customer</label>
                            <div class="input-group">
                                {!! Form::select('customer', ['' => 'Select Customer'] +$customers, null, ['class' => 'form-control custom-select', 'id' => 'customer']) !!}
                                <div class="input-group-addon btn btn-outline-dark" id="invoice" role="button" data-route="/office/debtors/financials/">
                                    <i class="fa fa-list"></i>
                                </div>
                            </div>
                        </form>

                {{-- deliveries ----------------------------------------------------------------------------------------}}

                        <h5 class="mt-5">View Passenger Deliveries</h5>
                        <p class="text-muted ml-3 mb-0"><small>This shows successful deliveries and no shows for selected month.</small></p>
                        <div class="ml-3">
                            <div class="mt-2">
                                {!! Form::select('parent', ['' => 'Select Customer'] +$customers, null, ['class' => 'form-control custom-select', 'id' => 'parent']) !!}
                            </div>
                            <div class="mt-1">
                                {!! Form::select('passenger', ['' => 'Select Passenger'] +$passengers, null, ['class' => 'form-control custom-select', 'id' => 'passengers']) !!}
                            </div>
                            <div class="mt-1">
                                {!! Form::text('date', null, ['class' => 'form-control datepicker', 'placeholder' => 'click for date', 'id' => 'date']) !!}
                            </div>
                            <div class="mt-1">
                                <button class="btn btn-outline-dark" id="getdeliveries">View Deliveries</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-8 col-md-6 col-lg-5 col-xl-3 offset-lg-1">

                {{-- journal -------------------------------------------------------------------------------}}

                        <h5 class="mt-5">
                            <a class="btn btn-outline-dark" href="{!! url('office/debtors/journal/create') !!}">Post Journal Entry</a>
                        </h5>

                {{-- outstanding ----------------------------------------------------------------------------------}}

                        <h5 class="mt-5">
                            <a class="btn btn-outline-dark" href="{!! url('office/debtors/outstanding') !!}">View Outstanding Debtors</a>
                        </h5>

                {{-- emergency month end ------------------------------------------------------------------------}}

                        <h5 class="mt-5">
                            <a class="btn btn-outline-dark" href="{!! url('office/debtors/emergency') !!}">Mail Last Month's Invoices</a>
                        </h5>
                        <p class="text-muted small ml-3">For emergency use when automated month-end mailing on 28th failed to run.</p>
                    </div>

                    <div class="col-sm-8 col-md-6 col-lg-5 col-xl-4">
                        <h5 class="mt-5">Last 3 Month-end Runs</h5>
                        <table class="dtable display compact small" data-order='' cellspacing="0" width="100%" id="index-table">
                            <thead>
                            <tr>
                                <th>Month</th>
                                <th>Action</th>
                                <th>Journals</th>
                                <th>Invoices</th>
                                <th>Mailed</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ( $monthend as $entry )
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($entry->processed)->toDateString() }}</td>
                                    <td>{{ $entry->action }}</td>
                                    <td class="text-center">{{ $entry->journals }}</td>
                                    <td class="text-center">{{ $entry->invoices }}</td>
                                    <td class="text-center">{{ $entry->mailed }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
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
        $(function() {
            {{-- custom datepicker --}}
            $('.datepicker').datepicker('remove');
            $('.datepicker').datepicker({
                format:         'yyyy-mm',
                autoclose:      true,
                weekStart:      1,
                startView:      'month',
                minViewMode:    'months'
            });

            {{-- get passengers for selected parent --}}
            $('#parent').on('change', function() {
                if ( $(this).val() > '' ) {
                    $.ajax({
                        type: "GET",
                        url: '/office/users/children/select/' + $(this).val(),
                        success: function (data) {
                            $('#passengers').empty().html(data);
                        }
                    });
                } else {
                    $('#passengers').empty().html('<option value="">Select Passenger</option>');
                }
            });

            {{-- get invoice --}}
            $('#invoice').on('click', function () {
                let customer = $('#customer').val();
                if ( customer > '' ) {
                    window.location.href = $(this).data('route') + customer;
                }
            });

            {{-- get deliveries --}}
            $('#getdeliveries').on('click', function() {
                window.location.href = '/office/debtors/deliveries/' + $('#passengers').val() + '/' + $('#date').val();
            });
        });
    </script>
@stop
