@extends('layouts/office')

@section('title')
    <title>Create Special Price</title>
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
                        <h3>New Special Price</h3>
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

                <div class="row">
                    <div class="col-md-11 col-lg-6 col-xl-5">

                {{-- Create from existing -----------------------------------------------------------------------}}

                        @if ( !is_null($existing) && count($existing) > 0 )
                            <h6 class="mt-3 mt-xl-0"><strong>Create a Future Price for an existing Special</strong></h6>
                            <p class="text-muted"><small>Select an item from previous special prices.</small></p>
                            <div class="form-group mb-5">
                                <div class="col-md-9 col-lg-8">
                                    {!! Form::select('existing', ['' => 'Select an Existing Special'] +$existing, null, ['class' => "form-control custom-select", 'id' => 'existing']) !!}
                                </div>
                            </div>

                            <button id="submit" class="btn btn-primary">Create Price</button>
                        @endif
                    </div>

                    <div class="col-md-11 col-lg-12 col-xl-10"><hr class="mb-1"></div>
                    <div class="col-xl-12"></div>
                </div>

                @if ( Auth::user()->email == 'webmaster@shuttlebug.co.za' )
                    <div class="row">
                        <div class="col-md-11 col-lg-6 col-xl-5 mt-5">
                            <h6 class="mt-3 mt-xl-0"><strong>For Webmaster Use</strong></h6>
                            <p class="text-muted"><small>Create a new special price.<br>
                            1. Write the code to extract ids of bookings that qualify for this promotion in Promotion Model.<br>
                            2. Create the new special here.<br>
                            3. Code the implementation in BookingPrice.</small></p>
                            <button id="new" class="btn btn-primary">Create New Special</button>
                        </div>
                    </div>
                @endif
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
            {{-- submit --}}
            $('#submit').on('click', function() {
                if ( $('#existing').val() > '' ) {
                    window.location.href = '/office/prices/special/edit/null/' + $('#existing').val();
                }
            });

            {{-- webmaster new --}}
            $('#new').on('click', function() {
                window.location.href = '/office/prices/special/edit';
            });
        });
    </script>
@stop