@extends('layouts.front')

@section('title')
    <title>Shuttle Bug: Register</title>
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
                    <h2 class="text-center">Register</h2>
                    <hr>
                    <p><small>In your account area you can update information, manage bookings and view your invoices and statement.</small></p>
                </div>
                <div class="pt-3">
                    {!! Form::open(['url' => route('register')]) !!}

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

                    <div class="form-group">
                        {!! Form::label('bottest', 'bottest', ['class' => 'sr-only']) !!}
                        {!! Form::text('bottest', '', ['class' => 'd-none form-control']) !!}
                    </div>

                    {{-- submit ----------------------------------------------------------------------------------}}

                    <div class="form-group mt-5">
                        {!! Form::hidden('role', 'customer') !!}
                        {!! Form::hidden('joindate', date('Y-m-d')) !!}
                        {!! Form::submit('Register', ['class' => 'btn btn-primary btn-block']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
