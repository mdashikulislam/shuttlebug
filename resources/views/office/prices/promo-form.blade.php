@extends('layouts/office')

@section('title')
    <title>Promotion Price Form</title>
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
                        @if ( !is_null($promotion) )
                            <h3>Edit Promotion</h3>
                        @else
                            <h3>New Promotion</h3>
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

                @if ( ! is_null($promotion) )
                    {!! Form::model($promotion, ['url' => ['office/prices/promotion/store', $promotion->id], 'id' => 'capture']) !!}
                @else
                    {!! Form::open(['url' => 'office/prices/promotion/store', 'id' => 'capture']) !!}
                @endif
                <div class="row">
                    <div class="col-md-11 col-lg-6 col-xl-5">

                {{-- Create from existing -----------------------------------------------------------------------}}

                        @if ( !is_null($existing) && count($existing) > 0 )
                            <h6 class="mt-3 mt-xl-0"><strong>Create a Future Promotion for an existing Promotion</strong></h6>
                            <p class="text-muted"><small>The list includes previous promotions that don't have a future price. Selecting an item will fill the form with its name and description.</small></p>
                            <div class="form-group mb-5">
                                <div class="col-md-9 col-lg-8">
                                    {!! Form::select('existing', ['' => 'Select an Existing Special'] +$existing, null, ['class' => "form-control custom-select", 'id' => 'existing']) !!}
                                </div>
                            </div>
                        @endif

                {{-- name --------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('name', 'Name', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                @if ( is_null($promotion) )
                                    {!! Form::text('name', null, ['class' => "form-control"]) !!}
                                @else
                                    {!! Form::text('name', null, ['class' => "form-control", 'readonly']) !!}
                                    <span class="text-muted"><small>The name of an existing Promotion cannot be changed.</small></span>
                                @endif
                            </div>
                        </div>

                {{-- description -------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('description', 'Description', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('description', null, ['class' => "form-control", 'required']) !!}
                            </div>
                        </div>

                {{-- price ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('rate', 'Price', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('rate', null, ['class' => 'form-control', 'required']) !!}
                                <span class="text-muted"><small>Latest Standard Price = {{ $current->basic_rate }}</small></span>
                            </div>
                        </div>

                {{-- start ----------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('start', 'Start Date', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('start', null, ['class' => "form-control datepicker", 'required']) !!}
                            </div>
                        </div>

                {{-- expire ----------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('expire', 'Expiry Date', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('expire', null, ['class' => "form-control datepicker", 'required', 'placeholder' => 'The Last Day of the Promotion']) !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 col-xl-5 ml-xl-3">
                    </div>

                    <div class="col-md-11 col-lg-12 col-xl-10"><hr class="mb-1"></div>
                    <div class="col-xl-12"></div>

                {{-- submit ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-6 col-xl-4 mt-4 mb-5">
                        {!! Form::hidden('type', 'promo') !!}
                        {!! Form::hidden('view', 'pub') !!}
                        @if ( is_null($promotion) )
                            {!! Form::submit('Save Promotion', ['class' => 'btn btn-primary']) !!}
                        @else
                            {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
                            {!! Form::submit('Remove Promotion', ['name' => 'remove', 'class' => 'btn btn-danger']) !!}
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

            {{-- populate from with existing selection --}}
            $('#existing').on('change', function() {
                let name = $(this).val();
                let descrip = $(this).find(':selected').text();
                $('#name').val(name);
                $('#description').val(descrip);
            });
        });
    </script>
@stop