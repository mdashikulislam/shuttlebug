@extends('layouts/office')

@section('title')
    <title>Event Bookings Email</title>
@stop

@section('css')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-events')

{{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

{{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg">
                        <h3>Email Event Bookings to {{ $customer->name }}</h3>
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
                <hr class="mt-2 mb-5">

{{-- review ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-md-11 col-lg-6 col-xl-5">

                        <p>To: <strong>{{ $customer->email }}</strong></p>
                        <p>From: <strong>bookings@shuttlebug.co.za</strong></p>
                        <p>Subject: <strong>Bookings Cofirmation</strong></p>
                        <strong>Message:</strong><br><br>
                        <p>Hi {{ $customer->first_name }}</p>
                        <p>These are your upcoming event bookings.</p>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>From</th>
                                    <th>At</th>
                                    <th>To</th>
                                    <th>Trip Fee</th>
                                </tr>
                            </thead>
                            <tbody>
                            @php $date = ''; @endphp
                                @foreach($bookings as $booking)
                                    <tr>
                                        @if ( $booking->date != $date )
                                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d',$booking->date)->format('D j M') }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                        <td>{{ $booking->puloc }}</td>
                                        <td>{{ substr($booking->putime,0,5) }}</td>
                                        <td>{{ $booking->doloc }}</td>
                                        <td>{{ $booking->tripfee }}</td>
                                    </tr>
                                    @php $date = $booking->date; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="col-lg-5 col-xl-5 ml-xl-3">
                    </div>

                    <div class="col-md-11 col-lg-12 col-xl-10"><hr class="mb-1"></div>
                    <div class="col-xl-12"></div>

                    <div class="col-lg-6 col-xl-4 mt-4 mb-5">
                        <a class="btn btn-primary" href="{!! url('office/events/email/'.$customer->id) !!}">Send Email</a>
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

        });
    </script>
@stop