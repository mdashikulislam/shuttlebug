@extends('layouts.office')

@section('title')
    <title>Photos</title>
@stop

@section('css')
    @parent
@stop

@section('style')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            <nav class="d-none d-md-block sidebar">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a class="nav-link {!! set_full_active('office/users') !!}" href="{!! url('office/users') !!}">Manage</a>
                    </li>
                </ul>
            </nav>

            {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

                {{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        <h3>Children Photos</h3>
                        <p>{{ count($children) }} Children active this year.</p>
                    </div>
                </div>
                <hr class="mt-2 mb-5">

                <div class="row">
                    <div class="col-sm-12">
                        @foreach ( $children as $child )
                            <figure class="figure">
                                @if ( file_exists(public_path().'/images/passengers/'.$child->id.'.jpg') )
                                    <img src="{!! asset('/images/passengers/'.$child->id.'.jpg') !!}" class="figure-img img-fluid">
                                @else
                                    <img src="{!! asset('/images/passengers/000000.jpg') !!}" class="figure-img img-fluid">
                                @endif
                                <figcaption class="figure-caption text-center">{{ $child->name }}<br>
                                    {{ $child->age }}
                                    <small class="text-right ml-2">{{ $child->id }}</small></figcaption>
                            </figure>
                        @endforeach
                    </div>
                </div>
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
        let table;

        $(function() {
            {{-- datatable --}}
            table = $('#index-table').DataTable( {"dom": 'lrtip'} );
            table.on('select', function ( e, dt, type, indexes ) {
                handleRowSelect(table.rows( indexes ).data().toArray()[0][0]);
            }).on('deselect', function () {
                handleRowDeselect();
            });
            $('#records').html('(' + table.page.info().recordsDisplay + ')');

            {{-- filter --}}
            $('.filter').multifilter();
            @if ( session()->has('userfilter') )
                @foreach ( session('userfilter') as $filter => $value )
                    @php $selector = '.'.$filter.'filter'; @endphp
                    $("{{ $selector }}").val("{!! $value !!}").trigger('change');
                @endforeach
            @endif

            {{-- switch away from index page when window re-sized to md --}}
            $( window ).resize(function() {
                if ($(window).width() < 990) {
                    window.location.href = '/office/users';
                }
            });
        });
    </script>
@stop