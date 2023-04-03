@extends('layouts.front')

@section('title')
    <title>Shuttle Bug Login</title>
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
                    <h2 class="text-center">Log In</h2>
                    <hr>
                    <div class="pt-3">
                        {!! Form::open(['url' => route('login')]) !!}

                        {{-- email ----------------------------------------------------------------------------------------}}

                        <div class="form-group">
                            @php $e_email = $errors->has('email') ? 'is-invalid' : ''; @endphp

                            {!! Form::label('email', 'Email Address') !!}
                            {!! Form::email('email', old('email'), ['class' => "form-control $e_email", 'autofocus', 'required']) !!}
                            <div class="invalid-feedback">
                                <small>{{ $errors->first('email') }}</small>
                            </div>
                        </div>

                        {{-- password -------------------------------------------------------------------------------------}}

                        <div class="form-group">
                            {!! Form::label('password', 'Password') !!}
                            {!! Form::password('password', ['class' => "form-control", 'required']) !!}
                            <div class="form-text text-right">
                                <a href="{!! route('password.request') !!}"><small>Forgotten Password ?</small></a>
                            </div>
                        </div>

                        {{-- remember ---------------------------------------------------------------------------------}}

                        <div class="form-check">
                            <label class="control control-checkbox">
                                Remember Me
                                {!! Form::checkbox('remember', 'remember', true) !!}
                                <span class="control_indicator"></span>
                            </label>
                        </div>

                        {{-- submit ----------------------------------------------------------------------------------}}

                        <div class="form-group mt-3">
                            {!! Form::submit('Login', ['class' => 'btn btn-primary btn-block']) !!}
                        </div>
                        {!! Form::close() !!}
                    </div>

                    {{-- register link -------------------------------------------------------------------------------}}

                    <div class="text-center mt-5">
                        <h4><a class="" href="{!! route('register') !!}">or Register</a></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
