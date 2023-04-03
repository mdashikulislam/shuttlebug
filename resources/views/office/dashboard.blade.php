@extends('layouts.office')

@section('title')
    <title>Dashboard</title>
@stop

@section('css')
    @parent
@stop

@section('style')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">

            {{--local nav--}}
            <nav class="d-none d-md-block sidebar">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{!! url('office/analytics') !!}"> Analytics</a>
                    </li>
                    <li class="nav-item">
                        {{--<a class="nav-link" href="{!! url('office/faq/') !!}"> FAQ</a>--}}
                    </li>
                </ul>
            </nav>

            {{--content--}}
            <section class="col-md col-12 pa-1 content">
                <h3 class="page-header d-inline">Dashboard</h3>
                <span class="float-right">{{ now()->format('l d F') }}</span>
                <hr class="mb-3">
                <div class="row">

            {{-- to do ----------------------------------------------------------------------------------------}}

                    @if ( !is_null($todo) )
                        <div class="col-md-6 col-lg-4 col-xl-3 mb-3">
                            <div class="card">
                                <h5 class="card-header">To Do</h5>
                                <ul class="list-group list-group-flush">
                                    @foreach ( $todo as $key => $cta )
                                        <li class="list-group-item">
                                            <strong>{{ ucwords($key) }}</strong><br>
                                            {!! $cta->item !!}<br>
                                            <a class="btn btn-danger btn-sm pull-right mt-1 mb-1" href="{!! url($cta->link) !!}">Do It</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

            {{-- trip plans -----------------------------------------------------------------------------------}}

                    @if ( !is_null($trips) )
                        <div class="col-md-6 col-lg-4 col-xl-3 mb-3">
                            <div class="card">
                                <h5 class="card-header">Trip Plans</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        {!! $trips->item !!}<br>
                                        <a class="btn btn-danger btn-sm pull-right mt-1 mb-1" href="{!! url($trips->link) !!}">Do It</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif

            {{-- vehicles -------------------------------------------------------------------------------------}}

                    {{--@if ( !is_null($vehicles) )--}}
                        {{--<div class="col-md-6 col-lg-4 col-xl-3 mb-3">--}}
                            {{--<div class="card">--}}
                                {{--<h5 class="card-header">Vehicle Licences</h5>--}}
                                {{--<ul class="list-group list-group-flush">--}}
                                    {{--@foreach ( $vehicles as $cta )--}}
                                        {{--<li class="list-group-item">--}}
                                            {{--{!! $cta->item !!}<br>--}}
                                            {{--<a class="btn btn-danger btn-sm pull-right mt-1 mb-1" href="{!! url($cta->link) !!}">Do It</a>--}}
                                        {{--</li>--}}
                                    {{--@endforeach--}}
                                {{--</ul>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    {{--@endif--}}

            {{-- prices -------------------------------------------------------------------------------------}}

                    @if ( !is_null($prices) )
                        <div class="col-md-6 col-lg-4 col-xl-3 mb-3">
                            <div class="card">
                                <h5 class="card-header">Prices</h5>
                                <ul class="list-group list-group-flush">
                                    @foreach ( $prices as $cta )
                                        <li class="list-group-item">
                                            {!! $cta->item !!}<br>
                                            <a class="btn btn-danger btn-sm pull-right mt-1 mb-1" href="{!! url($cta->link) !!}">Do It</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

            {{-- sms credits --------------------------------------------------------------------------------}}

                    @if ( !is_null($sms) )
                        <div class="col-md-6 col-lg-4 col-xl-3 mb-3">
                            <div class="card">
                                <h5 class="card-header">Sms Credits</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        {!! $sms->item !!}<br>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif
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

        $(document).ready(function() {


        });
    </script>
@stop