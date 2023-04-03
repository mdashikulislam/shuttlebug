@extends('layouts.front')

@section('title')
    <title>Request Password</title>
@endsection

@section('css')
    @parent
    <link rel="stylesheet" href="{!! asset('css/front.css') !!}">
@endsection

@section('content')
    <div class="container">
        @include('layouts.nav.front-nav')

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-4">
                <div class="mt-5">
                    <h2 class="text-center">Request Password</h2>
                    <hr>
                    <div class="pt-3">

                        {{-- instructions / success message ---------------------------------------------------------------}}

                        @if ( session('status') )
                            <div class="alert alert-primary text-center">{!! session('status') !!}<br>
                                <small>(Check your junk/spam folder if it's not in your inbox.)</small></div>
                        @else
                            <p class=""><small>Provide your email address and we'll send you a link to reset your password.</small></p>
                        @endif

                        {!! Form::open(['url' => route('password.email'), 'id' => 'pwordform']) !!}

                        {{-- email ----------------------------------------------------------------------------------------}}

                        <div class="form-group minimum mt-5">
                            @php $e_email = $errors->has('email') ? 'is-invalid' : ''; @endphp

                            {!! Form::label('email', 'Email Address') !!}
                            {!! Form::email('email', old('email'), ['class' => "form-control $e_email", 'autofocus', 'required']) !!}
                            <div class="invalid-feedback"><small>{{ $errors->first('email') }}</small></div>
                        </div>

                        {{-- submit ----------------------------------------------------------------------------------}}

                        <div class="form-group mt-5">
                            {!! Form::submit('Send Email', ['class' => 'btn btn-primary btn-block']) !!}
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
