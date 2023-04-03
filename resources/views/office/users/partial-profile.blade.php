{{-- form is shared by office and myaccount ------------------------------------------------------------------------}}

<div class="row">
    <div class="col-sm-11 col-md-11 col-lg-6 col-xl-5">

{{-- first name --------------------------------------------------------------------------------------}}

        <div class="form-group row">
            @php $e_fname = $errors->has('first_name') ? 'is-invalid' : ''; @endphp

            {!! Form::label('first_name', 'First Name', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::text('first_name', null, ['class' => "form-control $e_fname", 'autofocus']) !!}
                <div class="invalid-feedback"><small>{{ $errors->first('first_name') }}</small></div>
            </div>
        </div>

{{-- last name ---------------------------------------------------------------------------------------}}

        <div class="form-group row">
            @php $e_lname = $errors->has('last_name') ? 'is-invalid' : ''; @endphp

            {!! Form::label('last_name', 'Last Name', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::text('last_name', null, ['class' => "form-control $e_lname"]) !!}
                <div class="invalid-feedback"><small>{{ $errors->first('last_name') }}</small></div>
            </div>
        </div>

{{-- relationship ----------------------------------------------------------------------------------}}

        <div class="form-group row">
            @php $e_relation = $errors->has('relation') ? 'is-invalid' : ''; @endphp

            {!! Form::label('relation', 'Relationship', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::text('relation', null, ['class' => "form-control $e_relation", 'placeholder' => 'eg Mother, Farther']) !!}
                <div class="invalid-feedback"><small>{{ $errors->first('relation') }}</small></div>
            </div>
        </div>

{{-- email ----------------------------------------------------------------------------------------}}

        <div class="form-group row">
            @php $e_email = $errors->has('email') ? 'is-invalid' : ''; @endphp

            {!! Form::label('email', 'Email', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::email('email', null, ['class' => "form-control $e_email"]) !!}
                <div class="invalid-feedback"><small>{{ $errors->first('email') }}</small></div>
            </div>
        </div>

{{-- phone ----------------------------------------------------------------------------------------}}

        <div class="form-group row">
            {!! Form::label('phone', 'Phone', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::text('phone', null, ['class' => 'form-control']) !!}
            </div>
        </div>

{{-- mobile ----------------------------------------------------------------------------------------}}

        <div class="form-group row">
            @php $e_mobile = $errors->has('mobile') ? 'is-invalid' : ''; @endphp

            {!! Form::label('mobile', 'Mobile', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::text('mobile', null, ['class' => "form-control $e_mobile"]) !!}
                <div class="invalid-feedback"><small>{{ $errors->first('mobile') }}</small></div>
            </div>
        </div>

{{-- billing email ----------------------------------------------------------------------------------}}

        <div class="form-group row">
            @php $e_invemail = $errors->has('inv_email') ? 'is-invalid' : ''; @endphp

            {!! Form::label('inv_email', 'Email for Invoice', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::text('inv_email', null, ['class' => "form-control $e_invemail", 'placeholder' => 'if different to your email']) !!}
                <div class="invalid-feedback"><small>{{ $errors->first('inv_email') }}</small></div>
            </div>
        </div>

{{-- billing name ----------------------------------------------------------------------------------}}

        <div class="form-group row">
            {!! Form::label('inv_name', 'Name for Invoice', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::text('inv_name', null, ['class' => "form-control", 'placeholder' => 'if different to your name']) !!}
            </div>
        </div>

{{-- billing address ----------------------------------------------------------------------------------}}

        <div class="form-group row">
            {!! Form::label('inv_adrs', 'Adrs for Invoice', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::text('inv_adrs', null, ['class' => "form-control", 'placeholder' => 'if different to your address']) !!}
            </div>
        </div>

{{-- status ----------------------------------------------------------------------------------}}

        @if ( !is_null($user) && Auth::user()->id != $user->id )
            <div class="form-group row">
                {!! Form::label('role', 'Status', ['class' => 'col col-form-label']) !!}
                <div class="col-sm-8 col-lg-8">
                    <label class="control control-radio mt-1">Active
                        {!! Form::radio('status', 'active', null) !!}
                        <span class="control_indicator"></span>
                    </label>
                    <label class="control control-radio ml-3 mt-1">Inactive
                        {!! Form::radio('status', 'inactive', null) !!}
                        <span class="control_indicator"></span>
                    </label>
                </div>
            </div>
        @else
            {!! Form::hidden('status', 'active') !!}
        @endif
    </div>

    <div class="col-sm-11 col-md-11 col-lg-5 col-xl-5 ml-xl-3">
        <h6><strong>Address</strong></h6>

{{-- unit ----------------------------------------------------------------------------------------}}

        <div class="form-group row">
            {!! Form::label('unit', 'Building/Estate No & Name', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-7">
                {!! Form::text('unit', null, ['class' => 'form-control', 'placeholder' => 'only if applicable']) !!}
            </div>
        </div>

{{-- street ----------------------------------------------------------------------------------------}}

        <div class="form-group row">
            @php $e_street = $errors->has('street') ? 'is-invalid' : ''; @endphp

            {!! Form::label('street', 'Street No & Name', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-7">
                {!! Form::text('street', null, ['class' => "form-control $e_street"]) !!}
                <div class="invalid-feedback"><small>{{ $errors->first('street') }}</small></div>
            </div>
        </div>

{{-- suburb ----------------------------------------------------------------------------------------}}

        <div class="form-group row">
            @php $e_suburb = $errors->has('suburb') ? 'is-invalid' : ''; @endphp

            {!! Form::label('suburb', 'Suburb', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-7">
                {!! Form::text('suburb', null, ['class' => "form-control $e_suburb", 'placeholder' => 'eg. Hout Bay']) !!}
                <div class="invalid-feedback"><small>{{ $errors->first('suburb') }}</small></div>
            </div>
        </div>

{{-- city ----------------------------------------------------------------------------------------}}

        <div class="form-group row">
            @php $e_city = $errors->has('city') ? 'is-invalid' : ''; @endphp

            {!! Form::label('city', 'City', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-7">
                {!! Form::text('city', null, ['class' => "form-control $e_city", 'placeholder' => 'eg. Cape Town']) !!}
                <div class="invalid-feedback"><small>{{ $errors->first('city') }}</small></div>
            </div>
        </div>

{{-- geo location ----------------------------------------------------------------------------------}}

        <h6 class="mt-5"><strong>Map Location</strong></h6>
        <p class="text-muted"><small>Click the map button to see the address - drag the marker to the correct position.</small></p>

        <div class="form-group row">
            @php $e_geo = $errors->has('geo') ? 'is-invalid' : ''; @endphp

            {!! Form::label('geo', 'Map Location', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-7">
                <div class="input-group">
                    {!! Form::text('geo', null, ['class' => "form-control $e_geo", 'readonly']) !!}
                    <div class="input-group-addon btn btn-outline-dark" id="callLocationMap" role="button" title="view map">
                        <i class="fa fa-globe fa-lg"></i>
                    </div>
                </div>
                <div class="text-danger"><small>{{ $errors->first('geo') }}</small></div>
            </div>
        </div>

    </div>

    <div class="col-md-11 col-lg-12 col-xl-10"><hr class="mb-1"></div>
    <div class="col-xl-12"></div>

{{-- submit ----------------------------------------------------------------------------------------}}

    <div class="col-lg-6 col-xl-4 mt-4 mb-5">
        @if ( is_null($user) )
            {!! Form::hidden('role', 'customer') !!}
            {!! Form::hidden('joindate', date('Y-m-d')) !!}
            {!! Form::hidden('password', '', ['id' => 'password']) !!}
            {!! Form::hidden('password_confirmation', '', ['id' => 'cpassword']) !!}
            <label class="control control-checkbox">
                Send Customer an email with log in credentials.
                {!! Form::checkbox('send_email', 'send_email', true) !!}
                <span class="control_indicator"></span>
            </label><br>
            {!! Form::submit('Register Customer', ['class' => 'btn btn-primary']) !!}
        @else
            {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
        @endif
    </div>
</div>
