@extends('layouts/office')

@section('title')
    <title>Admin Form</title>
@stop

@section('css')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-users')

{{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

{{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        @if ( !is_null($admin) )
                            <h3>
                                <span class="small">{{ $admin->id }}:</span>
                                {{ $admin->name }}
                            </h3>
                        @else
                            <h3>Register a New Admin</h3>
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

                @if ( ! is_null($admin) )
                    {!! Form::model($admin, ['url' => ['office/users/admins/update', $admin->id], 'id' => 'capture']) !!}
                @else
                    {!! Form::open(['url' => 'office/users/admins/store', 'id' => 'capture']) !!}
                @endif
                <div class="row">
                    <div class="col-md-11 col-lg-6 col-xl-5">

                {{-- first name --------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            @php $e_fname = $errors->has('first_name') ? 'is-invalid' : ''; @endphp

                            {!! Form::label('first_name', 'First Name', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('first_name', null, ['class' => "form-control $e_fname", 'autofocus']) !!}
                                <div class="invalid-feedback"><small>{{ $errors->first('first_name') }}</small></div>
                            </div>
                        </div>

                {{-- last name ---------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            @php $e_lname = $errors->has('last_name') ? 'is-invalid' : ''; @endphp

                            {!! Form::label('last_name', 'Last Name', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('last_name', null, ['class' => "form-control $e_lname"]) !!}
                                <div class="invalid-feedback"><small>{{ $errors->first('last_name') }}</small></div>
                            </div>
                        </div>

                {{-- phone ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('phone', 'Phone', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('phone', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>

                {{-- relation ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('', 'Admin Role', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8 {{ $errors->has('relation') ? 'text-danger' : '' }}">
                                @if ( Auth::user()->relation == 'System' )
                                    @foreach ( $relations as $relation )
                                        <label class="control control-radio mt-1">{{ $relation }}
                                            {!! Form::radio("relation", $relation, null) !!}
                                            <span class="control_indicator"></span>
                                        </label>
                                    @endforeach
                                @else
                                    {{ ucfirst($admin->role) }}
                                @endif
                                <div class="text-danger"><small>{{ $errors->first('relation') }}</small></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 col-xl-5 ml-xl-3">

                {{-- login creds -------------------------------------------------------------------------------------}}

                        @if ( is_null($admin) )
                            <h6 class="mt-4 mt-lg-0"><strong>Admin Login Credentials</strong></h6>
                            <p class="text-muted ml-3"><small>The admin will need the email address and this password to login to the website.</small></p>
                            <dl class="row ml-0 mb-0">
                                <dt class="col-sm-3">Password</dt>
                                <dd class="col-sm-9" id="pwCred"></dd>
                            </dl>
                        @endif

                {{-- email creds -------------------------------------------------------------------------------------}}

                        <h6 class="mt-3"><strong>Admin Email Account</strong></h6>
                        @if ( is_null($admin) )
                            <p class="text-muted ml-3"><small>The admin will need these credentials to setup the mail account in their email client.</small></p>
                            <dl class="row ml-0 mb-2">
                                <dt class="col-sm-3">Address</dt>
                                <dd class="col-sm-9" id="emailAdrs"></dd>
                                <dt class="col-sm-3">Password</dt>
                                <dd class="col-sm-9" id="emailPw"></dd>
                            </dl>
                        @else
                            <span class="ml-3">{{ $admin->email }}</span>
                        @endif

                {{-- mail settings -----------------------------------------------------------------------------------}}

                        @if ( is_null($admin) )
                            <a href="#" class="ml-3" id="mailSettings">
                                Full mail account settings <i class="fa fa-caret-down"></i>
                            </a>
                            <dl class="row mt-2 ml-0 small d-none" id="settings">
                                <dt class="col-sm-3">IMAP / POP3</dt>
                                <dd class="col-sm-9">IMAP</dd>
                                <dt class="col-sm-3">Username:</dt>
                                <dd class="col-sm-9" id="settingEmail"></dd>
                                <dt class="col-sm-3">Password:</dt>
                                <dd class="col-sm-9" id="settingPw"></dd>
                                <dt class="col-sm-3">Incoming Server:</dt>
                                <dd class="col-sm-9">
                                    {{ config('app.env') == 'production' ? config('mail.host') : env('MAIL_DOMAIN')}}<br>
                                    IMAP Port: 993</dd>
                                <dt class="col-sm-3">Security:</dt>
                                <dd class="col-sm-9">TLS/SSL</dd>
                                <dt class="col-sm-3">SMTP Server:</dt>
                                <dd class="col-sm-9">
                                    {{ config('app.env') == 'production' ? config('mail.host') : env('MAIL_DOMAIN')}}<br>
                                    SMTP Port: 587</dd>
                                <dt class="col-sm-3">Security:</dt>
                                <dd class="col-sm-9">TLS/SSL</dd>
                                <dt class="col-sm-3">Authentication:</dt>
                                <dd class="col-sm-9">Required for Incoming &amp; Outgoing</dd>
                                <dt class="col-sm-3">Auth Type:</dt>
                                <dd class="col-sm-9">Username & Password</dd>
                            </dl>
                        @endif

                {{-- password ----------------------------------------------------------------------------------------}}

                        @if ( !is_null($admin) )
                            <h6 class="mt-4"><strong>Change Password</strong></h6>
                            {!! Form::label('password', 'New Password', ['class' => 'ml-3']) !!}
                            <div class="row pt-1 pb-1">
                                <div class="col-lg-8 col-xl-4 ml-3">
                                    {!! Form::password('password', ['class' => 'form-control']) !!}
                                </div>
                            </div>
                            {!! Form::label('password_confirmation', 'Confirm Password', ['class' => 'ml-3']) !!}
                            <div class="row pt-1 pb-1">
                                <div class="col-lg-8 col-xl-4 ml-3">
                                    {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="col-md-11 col-lg-12 col-xl-10"><hr class="mb-1"></div>
                    <div class="col-xl-12"></div>

                {{-- submit ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-6 col-xl-4 mt-4 mb-5">
                        {!! Form::hidden('role', 'admin') !!}
                        @if ( is_null($admin) )
                            {!! Form::hidden('email', '', ['id' => 'hiddenEmail']) !!}
                            {!! Form::hidden('emailpw', '', ['class' => 'emailPw']) !!}
                            {!! Form::hidden('joindate', date('Y-m-d')) !!}
                            {!! Form::hidden('bupw', '', ['id' => 'bupPword']) !!}
                            {!! Form::hidden('password', '', ['id' => 'password']) !!}
                            {!! Form::hidden('password_confirmation', '', ['id' => 'cpassword']) !!}
                            {!! Form::submit('Register Admin', ['class' => 'btn btn-primary']) !!}
                        @else
                            {!! Form::hidden('email', $admin->email) !!}
                            {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
                            @if ( Auth::user()->relation == 'System' )
                                {!! Form::submit('Remove Admin', ['name' => 'remove', 'class' => 'btn btn-outline-dark']) !!}
                            @endif
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
        let names   = {!! json_encode($names) !!}

        $(function() {
            let
                elemFirstName   = $('#first_name'),
                elemLastName    = $('#last_name'),
                elemSettings    = $('#settings'),
                elemPwCred      = $('#pwCred');

            {{--changing fields which effect email--}}
            $(document).on('change', '#first_name,#last_name', function () {
                @if ( !isset($admin) )
                if (elemFirstName.val() > '' && elemLastName.val() > '') {
                    let domain = "{{ config('app.env') == 'production' ? config('mail.host') : env('MAIL_DOMAIN')}}";
                    let pwext = '@sbug40';
                    let email, emailPw;
                    let fname = elemFirstName.val().toLowerCase();
                    let init = elemLastName.val().toLowerCase().substring(0, 1);
                    if ($.inArray(fname.substr(0, 1).toUpperCase() + fname.substr(1), names) !== -1) {
                        email = fname + '.' + init + '@' + domain;
                        emailPw = fname + '.' + init + pwext;
                    }
                    else {
                        email = fname + '@' + domain;
                        emailPw = fname + pwext;
                    }

                    $('#emailAdrs, #settingEmail').html(email);
                    $('#hiddenEmail').val(email);
                    $('#emailPw, #settingPw').html(emailPw);
                    $('.emailPw').val(emailPw);
                }
                else {
                    $('#emailAdrs, #settingEmail, #emailPw, #settingPw').html('');
                    $('#hiddenEmail').val('');
                    $('.emailPw').val('');
                }
                @endif
            });

            {{--show|hide email settings--}}
            $('#mailSettings').on('click', function () {
                if (elemSettings.hasClass('d-none')) {
                    elemSettings.removeClass('d-none');
                } else {
                    elemSettings.addClass('d-none');
                }
            });

            {{--generate password for create form--}}
            @if ( !isset($admin) && count($errors) == 0 )
                let pw = generatePassword(8);
                elemPwCred.html(pw);
                $('#password').val(pw);
                $('#cpassword').val(pw);
                {{--validation does not return password fields so this backup will be used
                when there are errors to re-instate the generated password--}}
                $('#bupPword').val(pw);
            @endif

            @if ( count($errors) > 0 )
                {{--trigger display email credentials--}}
                elemLastName.trigger('change');

                {{--recover password backup--}}
                elemPwCred.html("{{ old('bupw') }}");
                $('#password, #cpassword').val("{{ old('bupw') }}");
            @endif
        });
    </script>
@stop