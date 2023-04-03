@extends('layouts.myaccount')

@section('title')
    <title>Change Password</title>
@endsection

@section('css')
    @parent
@endsection

@section('style')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">
            @include('layouts.nav.nav-myaccount-profile')

    {{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

    {{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        <h3>Change Password</h3>
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
                <hr class="mt-2 mb-1 mb-md-5">

    {{-- sm - local nav ----------------------------------------------------------------------------------------}}

                <div class="btn-group d-md-none mb-5" role="group">
                    <a type="button" class="btn btn-outline-dark btn-sm ml-1" href="{!! url('myaccount/profile',[Auth::user()->id]) !!}">Profile</a>
                    <a type="button" class="btn btn-outline-dark btn-sm disabled">Password</a>
                    <a type="button" class="btn btn-outline-dark btn-sm ml-1" href="{!! url('myaccount/account') !!}">Account</a>
                </div>

    {{-- form  ----------------------------------------------------------------------------------------}}

                {!! Form::model($user, ['url' => ['myaccount/password/update', $user->id], 'id' => 'capture']) !!}
                    <div class="row">
                        <div class="col-sm-11 col-md-11 col-lg-6 col-xl-5">
                            <p class="text-muted pb-3">We recommend that the password be at least 8 characters long and include alpha characters, numerals and symbols.</p>
                            <div class="form-group row">
                                @php $e_pwd = $errors->has('password') ? 'is-invalid' : ''; @endphp

                                {!! Form::label('password', 'New Password', ['class' => 'col col-form-label']) !!}
                                <div class="col-sm-8 col-lg-8">
                                    {!! Form::password('password', ['class' => "form-control $e_pwd", 'autofocus']) !!}
                                    <div class="invalid-feedback"><small>{{ $errors->first('password') }}</small></div>
                                </div>
                            </div>

                            <div class="form-group row">
                                @php $e_pwdc = $errors->has('password_confirmation') ? 'is-invalid' : ''; @endphp

                                {!! Form::label('password_confirmation', 'Confirm Password', ['class' => 'col col-form-label']) !!}
                                <div class="col-sm-8 col-lg-8">
                                    {!! Form::password('password_confirmation', ['class' => "form-control $e_pwdc"]) !!}
                                    <div class="invalid-feedback"><small>{{ $errors->first('password_confirmation') }}</small></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-11 col-lg-12 col-xl-10"><hr class="mb-1"></div>
                        <div class="col-xl-12"></div>

                        <div class="col-lg-6 col-xl-4 mt-4 mb-5">
                            {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
                        </div>
                    </div>
                {!! Form::close() !!}
            </section>
        </div>
    </div>
@endsection

@section('script')
    @parent
@endsection

@section('jquery')
    <script>
        $(function() {

        });
    </script>
@endsection