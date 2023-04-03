{{-- child form ----------------------------------------------------------------------------------------}}

@if ( ! is_null($children) )
    @if ( $user->id == Auth::user()->id )
        {!! Form::model($children, ['url' => ['myaccount/children/update', $children->id], 'id' => 'capture']) !!}
    @else
        {!! Form::model($children, ['url' => ['office/users/children/update', $children->id], 'id' => 'capture']) !!}
    @endif
@else
    @if ( $user->id == Auth::user()->id )
        {!! Form::open(['url' => 'myaccount/children/store', 'id' => 'capture']) !!}
    @else
        {!! Form::open(['url' => 'office/users/children/store', 'id' => 'capture']) !!}
    @endif
@endif

    <div class="row">
        <div class="col-sm-11 col-lg-8 col-xl-8 ml-xl-5">

        {{-- child/friend --------------------------------------------------------------------------}}

            <div class="form-group row">
                {!! Form::label('friend', 'Child or Friend?', ['class' => 'col col-form-label']) !!}
                <div class="col-sm-8 col-lg-8">
                    <label class="control control-radio mt-1">Child
                        {!! Form::radio('friend', '', is_null($children) ? true:null) !!}
                        <span class="control_indicator"></span>
                    </label>
                    <label class="control control-radio ml-3 mt-1">Friend
                        {!! Form::radio('friend', 'friend', null) !!}
                        <span class="control_indicator"></span>
                    </label>
                </div>
            </div>

        {{-- first name ---------------------------------------------------------------------}}

            <div class="form-group row">
                {!! Form::label('first_name', 'First Name', ['class' => 'col col-form-label']) !!}
                <div class="col-sm-8 col-lg-8">
                    {!! Form::text('first_name', null, ['class' => "form-control", 'required']) !!}
                </div>
            </div>

        {{-- last name ----------------------------------------------------------------------}}

            <div class="form-group row">
                {!! Form::label('last_name', 'Last Name', ['class' => 'col col-form-label']) !!}
                <div class="col-sm-8 col-lg-8">
                    {!! Form::text('last_name', null, ['class' => "form-control", 'required']) !!}
                </div>
            </div>

        {{-- dob ----------------------------------------------------------------------}}

            <div class="form-group row">
                {!! Form::label('', 'Date of Birth', ['class' => 'col col-form-label']) !!}
                <div class="col-sm-8 col-lg-8">
                    <div class="input-group">
                        <div class="input-group-addon my-auto px-1"><small>Y</small></div>
                        {!! Form::select('age[year]', ['' => ''] +$years, !is_null($children) && $children->dob > '0000-00-00' ? date('Y', strtotime($children->dob)):null, ['class' => 'form-control custom-select w28']) !!}
                        <div class="input-group-addon my-auto px-1"><small>M</small></div>
                        {!! Form::select('age[month]', ['' => ''] +$months, !is_null($children) && $children->dob > '0000-00-00' ? date('m', strtotime($children->dob)):null, ['class' => 'form-control custom-select w25']) !!}
                        <div class="input-group-addon my-auto px-1"><small>D</small></div>
                        {!! Form::select('age[day]', ['' => ''] +$days, !is_null($children) && $children->dob > '0000-00-00' ? date('d', strtotime($children->dob)):null, ['class' => 'form-control custom-select w25']) !!}
                    </div>
                </div>
            </div>

        {{-- gender --------------------------------------------------------------------------}}

            <div class="form-group row">
                {!! Form::label('gender', 'Gender', ['class' => 'col col-form-label']) !!}
                <div class="col-sm-8 col-lg-8">
                    <label class="control control-radio mt-1">Boy
                        {!! Form::radio('gender', 'boy', is_null($children) ? true:null) !!}
                        <span class="control_indicator"></span>
                    </label>
                    <label class="control control-radio ml-3 mt-1">Girl
                        {!! Form::radio('gender', 'girl', null) !!}
                        <span class="control_indicator"></span>
                    </label>
                </div>
            </div>

        {{-- mobile --------------------------------------------------------------------------}}

            <div class="form-group row">
                {!! Form::label('phone', 'Mobile', ['class' => 'col col-form-label']) !!}
                <div class="col-sm-8 col-lg-8">
                    {!! Form::text('phone', null, ['class' => "form-control"]) !!}
                </div>
            </div>

        {{-- school ----------------------------------------------------------------------}}

            <div class="form-group row">
                {!! Form::label('school_id', 'School', ['class' => 'col col-form-label']) !!}
                <div class="col-sm-8 col-lg-8">
                    {!! Form::select('school_id', ['' => '', 700009 => 'None'] +$schools, null, ['class' => 'form-control custom-select', 'required']) !!}
                </div>
            </div>

        {{-- medical --------------------------------------------------------------------------}}

            <div class="form-group row">
                {!! Form::label('medical', 'Medical', ['class' => 'col col-form-label']) !!}
                <div class="col-sm-8 col-lg-8">
                    {!! Form::textarea('medical', null, ['class' => 'form-control', 'rows' => "3"]) !!}
                    <div class="text-muted small">Inlude any medical conditions we need to be aware of.</div>
                </div>
            </div>

        {{-- status ----------------------------------------------------------------------------------}}

            @if ( !is_null($children) )
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
            @endif
        </div>

        {{-- photo ----------------------------------------------------------------------------------}}

        <div class="col-sm-11 col-lg-3 ml-lg-3">
            @if ( ! is_null($children) && $children->friend == '' )
                @if ( file_exists(public_path().'/images/passengers/'.$children->id.'.jpg') )
                    <img class="" src="{!! asset('/images/passengers/'.$children->id.'.jpg') !!}">
                @else
                    <img class="" src="{!! asset('/images/passengers/000000.jpg') !!}">
                @endif
            @endif
        </div>

        <div class="col-md-11 col-lg-12 col-xl-11">
            <hr class="mb-1">
        </div>
        <div class="col-xl-12"></div>

        {{-- submit ----------------------------------------------------------------------------------------}}

        <div class="col-lg-6 col-xl-4 mt-4 mb-5">
            {!! Form::hidden('user_id', $user->id) !!}
            {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
{!! Form::close() !!}
