@extends('layouts/office')

@section('title')
    <title>Deliveries</title>
@stop

@section('css')
    @parent
@stop

@section('content')
    <div class="container-fluid">
        <div class="row flex-nowrap">

            @include('layouts.nav.nav-local-debtors')

{{-- contents ----------------------------------------------------------------------------------------}}

            <section class="col-md col-12 pa-1 content" id="content">

{{-- header ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-sm-5">
                        <h3>Deliveries: <small class="d-inline-block">{{ $passenger->name }} - {{ $month }}</small></h3>
                    </div>

{{-- messages ----------------------------------------------------------------------------------------}}

                    <div class="col-sm-4">
                        @if ( session()->has('confirm') )
                            <div class="alert-success alert-temp">{{ session('confirm') }}</div>
                        @elseif ( session()->has('warning') )
                            <div class="alert-warning alert-temp">{{ session('warning') }}</div>
                        @elseif ( session()->has('danger') )
                            <div class="alert-danger alert-temp">{{ session('danger') }}</div>
                        @endif
                    </div>
                </div>

                <hr class="mt-2 mb-5">

{{-- table ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg-8 col-xl-6">
                        <p class="text-muted"></p>
                        @if ( count($bookings) == 0 )
                            <hr>
                            <p>No Bookings for {{ $passenger->name }} in {{ $month }}.</p>
                        @else

                            <div class="table-responsive mt-5">
                                <table class="table table-striped table-bordered table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ $month }}</th>
                                            <th>Pickup</th>
                                            <th>Drop Off</th>
                                            <th class="text-center">Sms</th>
                                            <th class="text-center">Signed</th>
                                            <th class="text-center">No-show</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @php $last_date = 1; @endphp
                                    @foreach ( $bookings as $booking )
                                        <tr>
                                            <td>
                                                {{  $booking->date == $last_date ? '' : \Carbon\Carbon::parse($booking->date)->format('jS D') }}
                                            </td>
                                            <td>{{ substr($booking->putime,0,5) }} {{ $booking->puloc->venue }}</td>
                                            <td>{{ $booking->doloc->venue }}</td>
                                            @if ( !isset($booking->delivery) )
                                                <td colspan="3">No feedback from vehicle</td>
                                            @elseif ( $booking->delivery->data == 'noshow' )
                                                <td></td><td></td><td class="text-center text-danger">No Show</td>
                                            @else
                                                <td class="text-center text-success">sent</td>
                                                @if ( file_exists(public_path().'/images/signatures/'.$booking->delivery->id.'.jpg') )
                                                    <td><img class="" src="{!! asset('/images/signatures/'.$booking->delivery->id.'.jpg') !!}" height="40px"></td>
                                                @else
                                                    <td></td>
                                                @endif
                                                <td></td>
                                            @endif
                                        </tr>
                                        @php $last_date = $booking->date; @endphp
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
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
        $(function() {

        });
    </script>
@stop