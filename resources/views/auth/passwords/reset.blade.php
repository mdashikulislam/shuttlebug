@extends('layouts.front')

@section('title')
    <title>Password Reset</title>
@endsection

@section('css')
    @parent
    <link rel="stylesheet" href="{!! asset('css/front.css') !!}">
@endsection

@section('content')
    <div class="container">
        @include('layouts.nav.front-nav')

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-4 col-xl-4">
                <div class="mt-5">
                    <h2 class="text-center">Reset Your Password</h2>
                    <hr>

                    {{-- instructions / success message ---------------------------------------------------------------}}

                    @if ( session('status') )
                        <div class="alert alert-primary text-center">{!! session('status') !!}</div>
                    @else
                        <p class="white"><small>Create a new password. You will be logged in on completion.</small></p>
                    @endif
                </div>
                <div class="pt-3">

                    {!! Form::open(['url' => route('password.request')]) !!}

                    {{-- email --------------------------------------------------------------------------------}}

                    <div class="form-group">
                        @php $e_email = $errors->has('email') ? 'is-invalid' : ''; @endphp

                        {!! Form::label('email', 'Email Address') !!}
                        {!! Form::email('email', old('email'), ['class' => "form-control $e_email", 'autofocus', 'required']) !!}
                        <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                    </div>

                    {{-- password -----------------------------------------------------------------------------}}

                    <div class="form-group">
                        @php $e_password = $errors->has('password') ? 'is-invalid' : ''; @endphp

                        {!! Form::label('password', 'Password') !!}
                        {!! Form::password('password', ['class' => "form-control $e_password", 'required']) !!}
                        <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                        <small class="form-text">Suggestion: length 8+; include uppercase, lowercase, alpha, numeric & symbols.</small>
                    </div>

                    {{-- confirm password ---------------------------------------------------------------------}}

                    <div class="form-group">
                        @php $e_pwconf = $errors->has('password_confirmation') ? 'is-invalid' : ''; @endphp

                        {!! Form::label('password_confirmation', 'Confirm Password') !!}
                        {!! Form::password('password_confirmation', ['class' => "form-control $e_pwconf", 'required']) !!}
                        <div class="invalid-feedback">{{ $errors->first('password_confirmation') }}</div>
                    </div>

                    {{-- submit ----------------------------------------------------------------------------------}}

                    <div class="form-group mt-5">
                        {!! Form::hidden('token', $token) !!}
                        {!! Form::submit('Reset Password', ['class' => 'btn btn-primary btn-block']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
