@extends('layouts.office')

@section('title')
    <title>Day Plan Loader</title>
@stop

@section('css')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-tripplans')

        {{-- content ----------------------------------------------------------------------------------------}}

            <section class="col-lg col-12 pa-1 content d-none d-md-block" id="content">

        {{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg-4 col-xl-4 offset-4">
                        <div class="text-muted text-center mt-5">
                            <h1>Planning the trips . . .</h1>
                            <div class="progress mt-5" style="height:30px">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0"></div>
                            </div>
                        </div>
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
        let pbi = 0;
        $(function() {
            window.location.href = '/office/operations/tripplans/plan/build';
            setInterval(function(){
                pbi++;
                $('.progress-bar').attr('aria-valuenow', pbi).css('width', pbi + '%');
            }, 150);
        });
    </script>
@stop