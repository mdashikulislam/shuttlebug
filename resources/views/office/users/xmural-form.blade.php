{{-- xmural form ----------------------------------------------------------------------------------------}}

@if ( ! is_null($xmurals) )
    @if ( $user->id == Auth::user()->id )
        {!! Form::model($xmurals, ['url' => ['myaccount/xmurals/update', $xmurals->id], 'id' => 'capture']) !!}
    @else
        {!! Form::model($xmurals, ['url' => ['office/users/xmurals/update', $xmurals->id], 'id' => 'capture']) !!}
    @endif
@else
    @if ( $user->id == Auth::user()->id )
        {!! Form::open(['url' => 'myaccount/xmurals/store', 'id' => 'capture']) !!}
    @else
        {!! Form::open(['url' => 'office/users/xmurals/store', 'id' => 'capture']) !!}
    @endif
@endif

    <div class="row mt-5 mt-xl-0">
        <div class="col-md-11 col-lg-8 col-xl-8 ml-xl-5">

        {{-- known venues ---------------------------------------------------------------------}}

            <div class="form-group row">
                {!! Form::label('id', 'Choose from Known Venues', ['class' => 'col col-form-label']) !!}
                <div class="col-md-9 col-lg-8">
                    {!! Form::select('id', ['' => 'Known Venues'] + $xm_list, null, ['class' => "form-control custom-select"]) !!}
                </div>
            </div>

        {{-- new venue ----------------------------------------------------------------------}}

            @if ( !is_null($xmurals) )
                @if ( $user->id != Auth::user()->id || $xmurals->view == 'pvt' )
                    <h6 class="mt-5 mb-3"><strong>Edit the venue details if they are incorrect.</strong></h6>
                    @php $class=''; @endphp
                @else
                    <h6 class="mt-5 mb-3">This is a Known Venue and cannot be edited.</h6>
                    @php $class='d-none'; @endphp
                @endif
            @else
                <a id="newvenue" class="btn btn-outline-dark mt-5 mb-3">Or add a new venue</a>
                @php $class='d-none'; @endphp
            @endif

            <div id="newform" class="{{ $class }}">

            {{-- view ----------------------------------------------------------------------------------}}

                @if ( $user->id != Auth::user()->id )
                    <div class="form-group row">
                        {!! Form::label('view', 'Private or Public', ['class' => 'col col-form-label']) !!}
                        <div class="col-md-8 col-xl-7">
                            <label class="control control-radio mt-1">Public
                                {!! Form::radio('view', '', null) !!}
                                <span class="control_indicator"></span>
                            </label>
                            <label class="control control-radio ml-3 mt-1">Private
                                {!! Form::radio('view', 'pvt', is_null($xmurals) ? true:null) !!}
                                <span class="control_indicator"></span>
                            </label>
                            <p class="text-muted"><small>If used by this customer only, mark as Private else it will be listed in Known Venues and be available to all customers.</small></p>
                        </div>
                    </div>
                @else
                    {!! Form::hidden('view', 'pvt') !!}
                @endif

            {{-- time critical ----------------------------------------------------------------------------------------}}

                <div class="form-group row">
                    {!! Form::label('time', 'Time Critical', ['class' => 'col col-form-label']) !!}
                    <div class="col-md-8 col-xl-7">
                        <label class="control control-radio mt-1">No
                            {!! Form::radio('time', 0, is_null($xmurals) ? true:null) !!}
                            <span class="control_indicator"></span>
                        </label>
                        <label class="control control-radio ml-3 mt-1">Yes
                            {!! Form::radio('time', 1, null) !!}
                            <span class="control_indicator"></span>
                        </label>
                        <p class="text-muted"><small>Select 'Yes' if the activity starts at a specified time (eg. Lesson) else 'No' (eg. aftercare).</small></p>
                    </div>
                </div>

            {{-- venue ----------------------------------------------------------------------------------------}}

                <div class="form-group row">
                    {!! Form::label('venue', 'Name of Provider', ['class' => 'col col-form-label']) !!}
                    <div class="col-md-8 col-xl-7">
                        {!! Form::text('venue', null, ['class' => 'form-control', 'placeholder' => "e.g. Velocity Gym / Friend's name / Granny"]) !!}
                    </div>
                </div>

            {{-- unit ----------------------------------------------------------------------------------------}}

                <div class="form-group row">
                    {!! Form::label('unit', 'Building/Estate No & Name', ['class' => 'col col-form-label']) !!}
                    <div class="col-md-8 col-xl-7">
                        {!! Form::text('unit', null, ['class' => 'form-control', 'placeholder' => 'if applicable']) !!}
                    </div>
                </div>

            {{-- street ----------------------------------------------------------------------------------------}}

                <div class="form-group row">
                    {!! Form::label('street', 'Street No & Name', ['class' => 'col col-form-label']) !!}
                    <div class="col-md-8 col-xl-7">
                        {!! Form::text('street', null, ['class' => "form-control address"]) !!}
                    </div>
                </div>

            {{-- suburb ----------------------------------------------------------------------------------------}}

                <div class="form-group row">
                    {!! Form::label('suburb', 'Suburb', ['class' => 'col col-form-label']) !!}
                    <div class="col-md-8 col-xl-7">
                        {!! Form::text('suburb', null, ['class' => "form-control address", 'placeholder' => 'eg. Hout Bay']) !!}
                    </div>
                </div>

            {{-- city ----------------------------------------------------------------------------------------}}

                <div class="form-group row">
                    {!! Form::label('city', 'City', ['class' => 'col col-form-label']) !!}
                    <div class="col-md-8 col-xl-7">
                        {!! Form::text('city', null, ['class' => "form-control address", 'placeholder' => 'eg. Cape Town']) !!}
                    </div>
                </div>

            {{-- geo location ----------------------------------------------------------------------------------}}

                <div class="form-group row">
                    {!! Form::label('geo', 'Map Location', ['class' => 'col col-form-label']) !!}
                    <div class="col-md-8 col-xl-7">
                        <div class="input-group">
                            {!! Form::text('geo', null, ['class' => "form-control", 'readonly']) !!}
                            <div class="input-group-addon btn btn-outline-dark" id="callLocationMap" role="button" title="view map">
                                <i class="fa fa-globe fa-lg"></i>
                            </div>
                        </div>
                        <p class="text-muted"><small>Click the map button to see the address - drag the marker to the correct position.</small></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-11 col-lg-12 col-xl-11">
            <hr class="mb-1">
        </div>
        <div class="col-xl-12"></div>

        {{-- submit ----------------------------------------------------------------------------------------}}

        <div class="col-12 mt-4 mb-5">
            {!! Form::hidden('user_id', $user->id) !!}

            @if ( $user->id != Auth::user()->id )
                @if ( !is_null($xmurals) )
                    {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
                @else
                    {!! Form::submit('Add Xmural', ['class' => 'btn btn-primary']) !!}
                @endif
            @else
                @if ( !is_null($xmurals) && $xmurals->view == 'pvt' )
                    {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
                @elseif ( is_null($xmurals) )
                    {!! Form::submit('Add Xmural', ['class' => 'btn btn-primary']) !!}
                @endif
            @endif
        </div>
    </div>
{!! Form::close() !!}
