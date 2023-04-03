<div class="d-lg-flex flex-row-reverse toolbar">

    {{--type of index link--}}
    <div class="">
        @if ( !is_null($listing) )
            <a href="{!! URL::to("office/schools/index") !!}" class="btn btn-outline-dark btn-sm">List Active Schools</a>
        @else
            <a href="{!! URL::to("office/schools/index/inactive") !!}" class="btn btn-outline-dark btn-sm">List Inactive Schools</a>
        @endif
    </div>

    {{--city filter--}}
    <div class="mr-3">
        <div class="input-group">
            <div class="input-group-addon form-control-sm">
                @if ( session()->has('schoolfilter.city') )
                    <i class="icity text-danger fa fa-filter"></i>
                @else
                    <i class="icity fa fa-filter"></i>
                @endif
            </div>
            {!! Form::select('city', ['' => 'By City'] +$cities, session('schoolfilter.city') ?:null, ['class' => 'form-control form-control-sm custom-select filter cityfilter', 'data-index' => 'school', 'data-col' => 'city']) !!}
        </div>
    </div>

    {{--suburb filter--}}
    <div class="mr-3">
        <div class="input-group">
            <div class="input-group-addon form-control-sm">
                @if ( session()->has('schoolfilter.suburb') )
                    <i class="isuburb text-danger fa fa-filter"></i>
                @else
                    <i class="isuburb fa fa-filter"></i>
                @endif
            </div>
            {!! Form::select('suburb', ['' => 'By Suburb'] +$suburbs, session('schoolfilter.suburb') ?:null, ['class' => 'form-control form-control-sm custom-select filter suburbfilter', 'data-index' => 'school', 'data-col' => 'suburb']) !!}
        </div>
    </div>

    {{--search--}}
    <div class="d-none d-xl-inline-block mr-3">
        <div class="input-group">
            <div class="input-group-addon form-control-sm"><i class="fa fa-search"></i></div>
            <input type="search" id="tablesearch" placeholder="search table" size="15" class="form-control form-control-sm">
        </div>
    </div>
</div>