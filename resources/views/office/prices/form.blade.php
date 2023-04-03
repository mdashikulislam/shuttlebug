@extends('layouts/office')

@section('title')
    <title>Price Form</title>
@stop

@section('css')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-prices')

{{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

{{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        @if ( !is_null($price) )
                            <h3>Edit Future Standard Price</h3>
                        @else
                            <h3>Create Future Standard Price</h3>
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

                @if ( ! is_null($price) )
                    {!! Form::model($price, ['url' => ['office/prices/store', $price->id], 'id' => 'capture']) !!}
                @else
                    {!! Form::open(['url' => 'office/prices/store', 'id' => 'capture']) !!}
                @endif
                <div class="row">
                    <div class="col-md-11 col-lg-6 col-xl-5">

                {{-- date --------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('date', 'Effective Date', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('date', null, ['class' => "form-control datepicker", 'required']) !!}
                            </div>
                        </div>

                {{-- price ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('basic_rate', 'Price', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('basic_rate', null, ['class' => 'form-control', 'required']) !!}
                                <p class="text-muted"><small>Current Price = {{ $current->basic_rate }}</small></p>
                            </div>
                        </div>

                {{-- sibling ----------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('sibling_disc', 'Sibling Disc %', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('sibling_disc', null, ['class' => "form-control"]) !!}
                                <p class="text-muted"><small>Current Disc = {{ $current->sibling_disc }}</small></p>
                            </div>
                        </div>

                {{-- volume ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('volume_disc', 'Volume Disc %', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('volume_disc', null, ['class' => "form-control"]) !!}
                                <p class="text-muted"><small>Current Disc = {{ $current->volume_disc }}</small></p>
                            </div>
                        </div>

                {{-- hh ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('hh', 'Homeheroes Cost', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('hh', null, ['class' => 'form-control', 'required']) !!}
                                <p class="text-muted"><small>Current Cost = {{ $current->hh }}</small></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 col-xl-5 ml-xl-3">
                    </div>

                    <div class="col-md-11 col-lg-12 col-xl-10"><hr class="mb-1"></div>
                    <div class="col-xl-12"></div>

                {{-- submit ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-6 col-xl-4 mt-4 mb-5">
                        @if ( is_null($price) )
                            {!! Form::submit('Save Price', ['class' => 'btn btn-primary']) !!}
                        @else
                            {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
                            {!! Form::submit('Remove Price', ['name' => 'remove', 'class' => 'btn btn-danger']) !!}
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
        $(function() {
            $('.datepicker').datepicker('setStartDate', '+1d');
        });
    </script>
@stop