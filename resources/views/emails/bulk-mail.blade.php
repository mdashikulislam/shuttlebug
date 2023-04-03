@component('mail::message')
{{ $data->salutation }} {!! $user->first_name !!}

{!! $data->message !!}

Regards,<br>
<span style="color: #32A387;">Shuttle Bug</span><br>
[www.shuttlebug.co.za](http://www.shuttlebug.co.za)
@endcomponent
