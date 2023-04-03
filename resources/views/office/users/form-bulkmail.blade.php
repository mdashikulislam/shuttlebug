@extends('layouts/office')

@section('title')
    <title>Bulk Mail</title>
@stop

@section('css')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

{{-- local nav ----------------------------------------------------------------------------------------}}

            <nav class="d-none d-md-block sidebar">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a class="nav-link {!! set_full_active('office/users/bulkemail') !!}" href="#">Bulk Mail</a>
                    </li>
                </ul>
            </nav>

{{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

{{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        <h3>Bulk Mail</h3>
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
                <hr class="mt-2 mb-3">

{{-- form ----------------------------------------------------------------------------------------}}

                {!! Form::open(['url' => 'office/users/bulkmail/send', 'id' => 'capture']) !!}
                <div class="row">
                    <div class="col-xl-3">
                        <p class="text-muted">Select who the email should be sent to.</p>
                        <label class="control control-radio">All Customers
                            {!! Form::radio('customers', 'all', null) !!}
                            <span class="control_indicator"></span>
                        </label><br>
                        <label class="control control-radio">Active Customers
                            {!! Form::radio('customers', 'active', true) !!}
                            <span class="control_indicator"></span>
                        </label><br>
                        <label class="control control-radio">Inactive Customers
                            {!! Form::radio('customers', 'inactive', null) !!}
                            <span class="control_indicator"></span>
                        </label><br>
                        <label class="control control-radio">Selected Customers
                            {!! Form::radio('customers', 'selected', null) !!}
                            <span class="control_indicator"></span>
                        </label><br><br>
                        <label class="control control-checkbox">Copy to Admins ?
                            {!! Form::checkbox('admins', 'admins', null) !!}
                            <span class="control_indicator"></span>
                        </label>
                    </div>
                    <div class="col-xl-3">
                        <p class="text-muted">If you choose Selected Customers, choose the customers here. <small>(Active customers only)</small></p>
                        <table class="dtable display" data-order='[[ 1, "asc" ]]' cellspacing="0" width="100%" id="index-table">
                            <thead>
                            <tr>
                                <th class="id">Select</th>
                                <th>Customer</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ( $customers as $id => $customer )
                                <tr>
                                    <td class="text-center">
                                        <label class="control control-checkbox">
                                            {!! Form::checkbox('selected[]', $id, null) !!}
                                            <span class="control_indicator"></span>
                                        </label>
                                    </td>
                                    <td>{{ $customer }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-xl-5 ml-3">
                        <p class="text-muted">Compose email</p>
                        <div class="form-group row">
                            {!! Form::label('from', 'From', ['class' => 'col col-form-label']) !!}
                            <div class="col-sm-8 col-lg-8">
                                {!! Form::select('from', ['' => 'Select From'] +$admins, null, ['class' => 'form-control custom-select']) !!}
                            </div>
                        </div>
                        <div class="form-group row">
                            {!! Form::label('subject', 'Subject', ['class' => 'col col-form-label']) !!}
                            <div class="col-sm-8 col-lg-8">
                                {!! Form::text('subject', null, ['class' => "form-control"]) !!}
                            </div>
                        </div>
                        <div class="form-group row">
                            {!! Form::label('salutation', 'Salutation', ['class' => 'col col-form-label']) !!}
                            <div class="col-sm-4 col-lg-4">
                                {!! Form::text('salutation', null, ['class' => "form-control"]) !!}
                            </div>
                            <div class="col-sm-4 col-lg-4">
                                <span class="text-muted small">Dear, Hi, Hello</span>
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('message', 'Message', ['class' => 'form-label']) !!}
                            {!! Form::textarea('message', null, ['class' => "form-control", 'rows' => '14']) !!}
                            <span class="text-muted small">The salutation with customer's first name will be inserted as well as a footer<br>
                                Hi Mary, ~~~ Regards, Shuttle Bug
                            </span>
                        </div>
                        <div class="form-group">
                            {!! Form::submit('Send Email', ['class' => 'btn btn-primary']) !!}
                        </div>
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
            {{-- datatable --}}
            table = $('#index-table').DataTable( {"dom": 'lrtip'} );
        });
    </script>
@stop