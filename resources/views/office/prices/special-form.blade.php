@extends('layouts/office')

@section('title')
    <title>Special Price Form</title>
@stop

@section('css')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-prices')

{{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

{{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        <h3>{{ $edit }} Special</h3>
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
                <hr class="mt-2">

                @if ( $edit == 'Edit Existing' )
                    <p class="text-info mb-5"><strong>This special is active so only the description, view and Expiry Date can be modified.<br>(Expiry Date can be extended only, ie. new expiry must be greater than existing expiry.)</strong><br>
                        <small>To discontinue this special, enter a discontinue date & click the Discontinue button.</small>
                        @if ( $promotion->rate < $standard )
                            <small><u>Bookings after the discontinue date will revert to the ruling Standard Price ({{ $standard }}).</u></small>
                        @else
                            <small><u>The special will only be discontinued if there are no bookings after the discontinue date.</u></small>
                        @endif
                        <br>
                        <small>To change the price, cost or restricted list, create a new future price for this special (so that changes do not effect completed invoices).</small></p>
                @endif


{{-- form ----------------------------------------------------------------------------------------}}

                @if ( !is_null($promotion) )
                    {!! Form::model($promotion, ['url' => ['office/prices/special/store', $promotion->id], 'id' => 'capture']) !!}
                @else
                    {!! Form::open(['url' => 'office/prices/special/store', 'id' => 'capture']) !!}
                @endif
                <div class="row">
                    <div class="col-md-11 col-lg-6 col-xl-5">

                {{-- name --------------------------------------------------------------------------------------}}

                        @php $readonly = is_null($promotion) ? '' : 'readonly'; @endphp
                        <div class="form-group row">
                            {!! Form::label('name', 'Name', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('name', $promotion->name ?? '', ['class' => "form-control", $readonly]) !!}
                            </div>
                        </div>

                        @php $readonly = $edit == 'Edit Existing' ? 'readonly' : ''; @endphp

                {{-- description -------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('description', 'Description', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::textarea('description', $promotion->description ?? '', ['class' => 'form-control', 'rows' => "3", 'required']) !!}
                            </div>
                        </div>

                {{-- price ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('rate', 'Price', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('rate', null, ['class' => 'form-control', 'required', $readonly]) !!}
                            </div>
                        </div>

                {{-- hh ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('hh', 'Homeheroes Cost', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('hh', null, ['class' => 'form-control', 'required', $readonly]) !!}
                            </div>
                        </div>

                {{-- start ----------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('start', 'Effective Date', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                {!! Form::text('start', null, ['class' => "form-control datepicker", 'required', $readonly]) !!}
                            </div>
                        </div>

                {{-- expiry ----------------------------------------------------------------------------------}}

                        @if ( !is_null($promotion) && $promotion->start < now()->toDateString() )
                            <div class="form-group row">
                                {!! Form::label('expire', 'Expiry Date', ['class' => 'col col-form-label']) !!}
                                <div class="col-md-9 col-lg-8">
                                    {!! Form::text('expire', null, ['class' => "form-control datepicker"]) !!}
                                    <p class="text-muted"><small>Will be added if left blank.</small></p>
                                </div>
                            </div>
                        @endif

                {{-- view ----------------------------------------------------------------------------------------}}

                        <div class="form-group row">
                            {!! Form::label('view', 'View', ['class' => 'col col-form-label']) !!}
                            <div class="col-md-9 col-lg-8">
                                <label class="control control-radio mt-1">Publish
                                    {!! Form::radio('view', 'pub', !is_null($promotion) && $promotion->view == 'pub' ? true:false) !!}
                                    <span class="control_indicator"></span>
                                </label>
                                <label class="control control-radio ml-2 mt-1">Private
                                    {!! Form::radio('view', 'prv', !is_null($promotion) && $promotion->view == 'prv' ? true:false) !!}
                                    <span class="control_indicator"></span>
                                </label>
                                <p class="text-muted"><small>Private prices are NOT published on the web pages.</small></p>
                            </div>
                        </div>

                {{-- discontinue ----------------------------------------------------------------------------------}}

                        @if ( $edit == 'Edit Existing' )
                            <div class="form-group row">
                                {!! Form::label('discontinue', 'Discontinue', ['class' => 'col col-form-label']) !!}
                                <div class="col-md-9 col-lg-8">
                                    {!! Form::text('discontinue', null, ['class' => "form-control datepicker"]) !!}
                                    <p class="text-muted"><small>Effective date. End of term is recommended.</small></p>
                                </div>
                            </div>
                        @endif
                    </div>

                {{-- restrictions --------------------------------------------------------------------------------}}


                    <div class="col-lg-5 col-xl-5 ml-lg-3">
                        @php $disabled = $edit == 'Edit Existing' ? 'disabled' : ''; @endphp
                        @if ( is_null($promotion) )
                            <p>If this special is subject to restrictions, select the restriction to apply:</p>
                            <ul>
                                <li><a id="selcustomers" href="#" class="restricted">Customers</a></li>
                                <li><a id="selschools" href="#" class="restricted">Schools</a></li>
                                <li><a id="selsuburbs" href="#" class="restricted">Suburbs</a></li>
                                <li><a id="selnone" href="#" class="restricted">None</a></li>
                            </ul>
                        @endif

                        @if ( is_null($promotion) || $promotion->restriction == 'customers' )
                            <div class="options customers {{ is_null($promotion) ? 'd-none' : '' }}">
                                <h6 class=""><strong>Restricted to Selected Customers</strong></h6>
                                @if ( $edit != 'Edit Existing' )
                                    <p class="text-muted ml-3"><small>Select a customer to add to the special.</small></p>
                                    <div class="form-group row">
                                        <div class="col-md-9 col-lg-8 ml-3">
                                            {!! Form::select('sellist', ['' => ' '] +$customers, null, ['class' => "form-control custom-select", 'id' => 'lcus', 'data-target' => 'list_customers']) !!}
                                            </div>
                                    </div>
                                @endif
                                <div id="list_customers" class="list ml-3">
                                    @if ( !is_null($promotion) )
                                        @foreach ($promotion->list as $id)
                                            <label class="control control-checkbox">{{ $customers[$id] ?? 'Customer not found' }}
                                                {!! Form::checkbox("list[]", $id, true, [$disabled]) !!}
                                                <span class="control_indicator"></span>
                                            </label><br>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ( is_null($promotion) || $promotion->restriction == 'schools' )
                            <div class="options schools {{ is_null($promotion) ? 'd-none' : '' }}">
                                <h6 class=""><strong>Restricted to Selected Schools</strong></h6>
                                @if ( $edit != 'Edit Existing' )
                                    <p class="text-muted ml-3"><small>Select a school to add to the special.</small></p>
                                    <div class="form-group row">
                                        <div class="col-md-9 col-lg-8 ml-3">
                                            {!! Form::select('sellist', ['' => ' '] +$schools, null, ['class' => "form-control custom-select", 'id' => 'lsch', 'data-target' => 'list_schools']) !!}
                                        </div>
                                    </div>
                                @endif
                                <div id="list_schools" class="list ml-3">
                                    @if ( !is_null($promotion) )
                                        @foreach ($promotion->list as $id)
                                            <label class="control control-checkbox">{{ $schools[$id] ?? 'School not found' }}
                                                {!! Form::checkbox("list[]", $id, true, [$disabled]) !!}
                                                <span class="control_indicator"></span>
                                            </label><br>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ( is_null($promotion) || $promotion->restriction == 'suburbs' )
                            <div class="options suburbs {{ is_null($promotion) ? 'd-none' : '' }}">
                                <h6 class=""><strong>Restricted to Selected Suburbs</strong></h6>
                                @if ( $edit != 'Edit Existing' )
                                    <p class="text-muted ml-3"><small>Select a suburb to add to the special.</small></p>
                                    <div class="form-group row">
                                        <div class="col-md-9 col-lg-8 ml-3">
                                            {!! Form::select('sellist', ['' => ' '] +$suburbs, null, ['class' => "form-control custom-select", 'id' => 'lsub', 'data-target' => 'list_suburbs']) !!}
                                        </div>
                                    </div>
                                @endif
                                <div id="list_suburbs" class="list ml-3">
                                    @if ( !is_null($promotion) )
                                        @foreach ($promotion->list as $id)
                                            <label class="control control-checkbox">{{ $suburbs[$id] ?? 'Suburb not found' }}
                                                {!! Form::checkbox("list[]", $id, true, [$disabled]) !!}
                                                <span class="control_indicator"></span>
                                            </label><br>
                                        @endforeach
                                    @endif
                                </div>
                                @if ( $edit != 'Edit Existing' )
                                    <p class="text-muted ml-3"><small>Add a non-listed Suburb.</small></p>
                                    <div class="form-group row">
                                        <div class="col-md-9 col-lg-8 ml-3">
                                            {!! Form::text('add', null, ['class' => "form-control", 'id' => 'asub', 'data-target' => 'list_suburbs']) !!}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="col-md-11 col-lg-12 col-xl-10"><hr class="mb-1"></div>
                    <div class="col-xl-12"></div>

                {{-- submit ----------------------------------------------------------------------------------------}}

                    <div class="col-lg-6 col-xl-4 mt-4 mb-5">
                        {!! Form::hidden('edit', $edit) !!}
                        {!! Form::hidden('type', 'spec') !!}
                        @if ( $edit == 'Edit Existing' )
                            {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
                            {!! Form::submit('Discontinue', ['name' => 'stop', 'class' => 'btn btn-danger ml-3']) !!}
                        @elseif ( $edit == 'New' || $edit == 'New Future' )
                            {!! Form::hidden('restricted', null, ['id' => 'restricted']) !!}
                            {!! Form::hidden('restriction', null, ['id' => 'restriction']) !!}
                            {!! Form::submit('Save Special', ['class' => 'btn btn-primary']) !!}
                        @elseif ( $edit == 'Edit Future' )
                            {!! Form::hidden('restricted', $promotion->restricted) !!}
                            {!! Form::submit('Save Changes', ['class' => 'btn btn-primary']) !!}
                            {!! Form::submit('Remove Special Price', ['name' => 'remove', 'class' => 'btn btn-danger ml-3']) !!}
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
        $(function() {
            $('.datepicker').datepicker('setStartDate', '+1d');

            {{-- populate from with existing selection --}}
            $('#existing').on('change', function() {
                let name = $(this).val();
                let descrip = $(this).find(':selected').text();
                $('#name').val(name);
                $('#description').val(descrip);
            });

            {{-- toggle restrictions --}}
            $('.restricted').on('click', function() {
                $('.options').addClass('d-none');
                let restriction = $(this).text().toLowerCase();
                if ( restriction === 'none' ) {
                    $('#restriction').val('');
                    $('#restricted').val(0);
                } else {
                    $('.' + restriction).removeClass('d-none');
                    $('#restriction').val(restriction);
                    $('#restricted').val(1);
                }
            });

            {{-- add new list checkbox --}}
            $('#lcus, #lsch, #lsub, #asub').on('change', function() {
                let listid = $(this).attr('id');
                let target = $(this).data('target');
                let id   = $(this).val();
                let name = $(this).attr('id') === 'asub' ? $('#' + listid).val() : $('#' + listid).find(':selected').text();

                if ( name > ' ' ) {
                    $('<label class="control control-checkbox">' + name +
                        '<input name="list[]" value="' + id + '" type="checkbox" checked>' +
                        '<span class="control_indicator"></span>' +
                        '</label><br>').appendTo('#' + target);
                }
            });
        });
    </script>
@stop
