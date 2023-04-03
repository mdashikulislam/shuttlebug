@component('mail::message')
Hi {{ $user->first_name }}

These are your upcoming event bookings.

@component('mail::panel')
<table style="font-size:0.8rem; border-spacing:10px">
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
@endcomponent

Regards,<br>
<span style="color: #32A387;">The Shuttle Bug Team</span><br>
[www.shuttlebug.co.za](http://www.shuttlebug.co.za)
@endcomponent
