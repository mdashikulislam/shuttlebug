@component('mail::message')
Hi {{ $user->first_name }}

These are your bookings until the end of next week.<br>
<span style="font-size:0.7rem">(* when no time is shown the time depends on journey duration.)</span>

@component('mail::panel')
<table style="font-size:0.8rem; border-spacing:10px">
<thead>
<tr>
<th>Date</th>
<th style="text-align:left">From</th>
<th>At</th>
<th style="text-align:left">To</th>
<th>By</th>
</tr>
</thead>
<tbody>
@php $name = ''; $date = ''; @endphp
@foreach($bookings as $booking)
@if ( $booking->passenger->name != $name )
<tr>
<td colspan="5"><strong>{{ $booking->passenger->first_name }}</strong></td>
</tr>
@endif
<tr>
@if ( $booking->date != $date )
<td>{{ \Carbon\Carbon::createFromFormat('Y-m-d',$booking->date)->format('D j M') }}</td>
@else
<td></td>
@endif
<td style="text-align:left">{{ $booking->puloc_type == 'user' ? 'home' : $booking->puloc->name }}</td>
<td>{{ $booking->putime > 0 ? substr($booking->putime,0,5) : '*' }}</td>
<td style="text-align:left">{{ $booking->doloc_type == 'user' ? 'home' : $booking->doloc->name }}</td>
<td>{{ $booking->putime > 0 ? '*' : substr($booking->doloc->dropby,0,5) }}</td>
</tr>
@php $name = $booking->passenger->name; $date = $booking->date; @endphp
@endforeach
</tbody>
</table>
@endcomponent

Regards,<br>
<span style="color: #32A387;">The Shuttle Bug Team</span><br>
[www.shuttlebug.co.za](http://www.shuttlebug.co.za)
@endcomponent
