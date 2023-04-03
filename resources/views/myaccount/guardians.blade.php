@extends('layouts.myaccount')

@section('title')
    <title>Guardians</title>
@endsection

@section('css')
    @parent
@endsection

@section('style')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <nav class="d-none d-md-block sidebar"></nav>

    {{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

    {{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        <h3>Guardians</h3>
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
                <hr class="mt-2 mb-1 mb-md-2">
                <p class="text-muted mb-5">Provide an alternative guardian in case you can't be reached<br>
                and the details of the person who receives your children at home.</p>

    {{-- form  ----------------------------------------------------------------------------------------}}
                {!! Form::open(['url' => 'myaccount/guardians/store', 'id' => 'capture']) !!}
                    @include('office.users.partial-guardians')
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