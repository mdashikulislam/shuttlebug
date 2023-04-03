@extends('layouts/office')

@section('title')
    <title>Outstanding Debtors</title>
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
                    <div class="col-sm-5">
                        <h3>Outstanding Debtors <small class="d-inline-block">As At {{ is_null($month) ? 'Today' : Carbon\Carbon::createFromFormat('Y-m-d', $month)->format('d F Y') }}</small></h3>
                    </div>

{{-- buttons ----------------------------------------------------------------------------------------}}

                    <div class="col-sm-3">
                        {!! Form::select('month', $dates, $month, ['class' => "form-control form-control-sm custom-select", 'id' => 'month']) !!}
                    </div>

{{-- messages ----------------------------------------------------------------------------------------}}

                    <div class="col-sm-4">
                        @if ( session()->has('confirm') )
                            <div class="alert-success alert-temp">{{ session('confirm') }}</div>
                        @elseif ( session()->has('warning') )
                            <div class="alert-warning alert-temp">{{ session('warning') }}</div>
                        @elseif ( session()->has('danger') )
                            <div class="alert-danger alert-temp">{{ session('danger') }}</div>
                        @endif
                    </div>
                </div>

                <hr class="mt-2 mb-5">

{{-- table ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg-8 col-xl-6">
                        @if ( count($balances) == 0 )
                            <hr>
                            <p>No Outstanding balances at this date.</p>
                        @else

                            <div class="table-responsive">
                                {{--<table class="table table-striped table-sm mt-3" cellspacing="0" width="100%">--}}
                                <table class="dtable display" data-order='' cellspacing="0" width="100%" id="index-table">
                                    <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Customer</th>
                                        <th class="text-right">Outstanding</th>
                                        @if ( is_null($month) )
                                            <th class="text-center">Email Statement</th>
                                        @endif
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th class="text-right">{{ number_format(array_sum($balances),0) }}</th>
                                        @if ( is_null($month) )
                                            <th></th>
                                        @endif
                                    </tr>
                                    @foreach ( $customers as $customer )
                                        <tr>
                                            <td>{{ $customer->id }}</td>
                                            <td>{{ $customer->alpha_name }}</td>
                                            <td class="text-right">{{ number_format($balances[$customer->id],0) }}</td>
                                            @if ( is_null($month) )
                                                <td class="text-center">
                                                    <a id="send{{ $customer->id }}" href="#" class="resend" data-id="{{ $customer->id }}">
                                                        <i class="fa fa-envelope-o"></i>
                                                    </a>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
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

            table = $('#index-table').DataTable( {"dom": 'lrtip'} );

            {{-- load the selected month --}}
            $('#month').on('change', function() {
                window.location.href = '/office/debtors/outstanding/' + $(this).val();
            });

            {{-- send pdf statement & invoice --}}
            $('.resend').on('click', function() {
                let caller = $(this).attr('id');
                $('#ajaxloader').show();
                $.ajax({
                    type: "GET",
                    url: '/office/debtors/pdf/stat/' + $(this).data('id'),
                    success: function (data) {
                        $('#ajaxloader').hide();
                        $('#' + caller).html('sent').removeAttr('href');
                    }
                });
            });

        });
    </script>
@stop