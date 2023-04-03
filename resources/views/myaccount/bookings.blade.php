@extends('layouts.myaccount')

@section('title')
    <title>Bookings</title>
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
                        <h3>Current Bookings</h3>
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
                <hr class="mt-2 mb-1 mb-2">
                <p class="mb-3"><a href="mailto:bookings@shuttlebug.co.za">Notify us of changes</a></p>

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-sm mb-3">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Passenger</th>
                                <th>Pickup</th>
                                <th>Time</th>
                                <th>Drop Off</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $last_date = 1; @endphp
                            @foreach ( $bookings as $booking )
                                <tr>
                                    <td>
                                        {{ $booking->date == $last_date ? '' : \Carbon\Carbon::createFromFormat('Y-m-d',$booking->date)->format('l jS F') }}
                                    </td>
                                    <td>{{ $booking->passenger->first_name }}</td>
                                    <td>{{ $booking->puloc->id < 200000 ? 'home' : $booking->puloc->name }}</td>
                                    <td>
                                        {{ $booking->putime == '00:00:00' ? substr($booking->dotime,0,5).' (drop off)' : substr($booking->putime,0,5) }}
                                    </td>
                                    <td>{{ $booking->doloc->id < 200000 ? 'home' : $booking->doloc->name }}</td>
                                    <td>{{ $booking->price }}</td>
                                </tr>
                                @php $last_date = $booking->date; @endphp
                            @endforeach
                        </tbody>
                    </table>
                    @if ( count($bookings) == 0 )
                        <p>You have no bookings.</p>
                    @endif
                </div>
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