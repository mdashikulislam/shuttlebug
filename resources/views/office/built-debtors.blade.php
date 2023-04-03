@extends('layouts/office')

@section('title')
    <title>Built Debtors</title>
@stop

@section('css')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

{{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

{{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col">
                        <h3>Built Debtors vs Sbug4 Balances As At 28 Dec 2017</h3>
                    </div>
                </div>

                <hr class="mt-2 mb-5">

{{-- table ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-xl-6">
                        <div class="table-responsive">
                            <table class="dtable display" data-order='' cellspacing="0" width="100%" id="index-table">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Customer</th>
                                        <th class="text-right">Built</th>
                                        <th class="text-right">SBug4</th>
                                        <th class="text-right">Diff</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th class="text-right">
                                            {{ number_format(array_sum(array_column($balances, 'built')),0) }}
                                        </th>
                                        <th class="text-right">
                                            {{ number_format(array_sum(array_column($balances, 'orig')),0) }}
                                        </th>
                                        <th class="text-right">
                                            {{ number_format(array_sum(array_column($balances, 'built'))-array_sum(array_column($balances, 'orig')),0) }}
                                        </th>
                                    </tr>
                                    @foreach ( $customers as $id => $customer )
                                        @if ( $balances[$id]['built'] != $balances[$id]['orig'] )
                                            <tr>
                                                <td>{{ $id }}</td>
                                                <td>{{ $customer }}</td>
                                                <td class="text-right">
                                                    {{ number_format($balances[$id]['built'],0) }}
                                                </td>
                                                <td class="text-right">
                                                    {{ number_format($balances[$id]['orig'],0) }}
                                                </td>
                                                <td class="text-right">
                                                    {{ number_format($balances[$id]['built']-$balances[$id]['orig'],0) }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <ul>
                            <li>Only shows debtors with differences.</li>
                            <li>Add reconciliations to debtors_journal table and then rerun build process.</li>
                            <li>When ok, copy debtors_statement table to _built_debtors_statement table</li>
                            <li>Remove create_debtors_statement_table migration</li>
                            <li>Remove DebtorsStatementTableSeeder</li>
                            <li>SeederController should only process months in 2018 to update debtors_statement</li>
                        </ul>
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

        });
    </script>
@stop