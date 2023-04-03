{{-- form is shared by office and myaccount -------------------------------------------------------------------------}}

<div class="row">

{{-- guardian ---------------------------------------------------------------------------}}

    <div class="col-sm-11 col-md-11 col-lg-6 col-xl-5">
        <h6 class=""><strong>Guardian</strong></h6>

        {{-- first name ---------------------------------------------------------------------}}

        <div class="form-group row">
            {!! Form::label('guardian[first_name]', 'First Name', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::text('guardian[first_name]', $guardian->first_name ?? null, ['class' => "form-control", 'autofocus']) !!}
            </div>
        </div>

        {{-- last name ----------------------------------------------------------------------}}

        <div class="form-group row">
            {!! Form::label('guardian[last_name]', 'Last Name', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::text('guardian[last_name]', $guardian->last_name ?? null, ['class' => "form-control"]) !!}
            </div>
        </div>

        {{-- relation ----------------------------------------------------------------------}}

        <div class="form-group row">
            {!! Form::label('guardian[relation]', 'Relationship', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::text('guardian[relation]', $guardian->relation ?? null, ['class' => "form-control", 'placeholder' => '(eg Mother, Farther)']) !!}
            </div>
        </div>

        {{-- phone --------------------------------------------------------------------------}}

        <div class="form-group row">
            {!! Form::label('guardian[phone]', 'Phone', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::text('guardian[phone]', $guardian->phone ?? null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

{{-- receiver --------------------------------------------------------------------------}}

    <div class="col-sm-11 col-md-11 col-lg-6 col-xl-5 ml-xl-3 mt-3 mt-lg-0">
        <h6><strong>Receiver</strong></h6>

        {{-- first name ---------------------------------------------------------------------}}

        <div class="form-group row">
            {!! Form::label('receiver[first_name]', 'First Name', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::text('receiver[first_name]', $receiver->first_name ?? null, ['class' => "form-control"]) !!}
            </div>
        </div>

        {{-- last name ----------------------------------------------------------------------}}

        <div class="form-group row">
            {!! Form::label('receiver[last_name]', 'Last Name', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::text('receiver[last_name]', $receiver->last_name ?? null, ['class' => "form-control"]) !!}
            </div>
        </div>

        {{-- phone --------------------------------------------------------------------------}}

        <div class="form-group row">
            {!! Form::label('receiver[phone]', 'Phone', ['class' => 'col col-form-label']) !!}
            <div class="col-sm-8 col-lg-8">
                {!! Form::text('receiver[phone]', $receiver->phone ?? null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="col-md-11 col-lg-12 col-xl-10"><hr class="mb-1"></div>
    <div class="col-xl-12"></div>

{{-- submit ----------------------------------------------------------------------------------------}}

    <div class="col-lg-6 col-xl-4 mt-4 mb-5">
        {!! Form::hidden('guardian[user_id]', $user->id) !!}
        {!! Form::hidden('guardian[id]', $guardian->id ?? null) !!}
        {!! Form::hidden('guardian[role]', 'Guardian') !!}
        {!! Form::hidden('receiver[user_id]', $user->id) !!}
        {!! Form::hidden('receiver[id]', $receiver->id ?? null) !!}
        {!! Form::hidden('receiver[role]', 'Receiver') !!}
        {!! Form::hidden('receiver[relation]', 'Receiver') !!}
        {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
    </div>
</div>
