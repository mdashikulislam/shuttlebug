@component('mail::message')

@if ( $user->exadmin )
Hi {{ $user->first_name }}

We have created an account for you at Shuttle Bug.

You can now login to [Shuttle Bug](http://www.shuttlebug.co.za) and access your account pages to update information and bookings as well as view your billing.
@else
Hi,

Thank you for registering at Shuttle Bug.

You are now able to login with your email and password to access your Account pages.
@endif

@component('mail::panel')
Log in using these credentials:

+ Email: {{ $user->email }}
+ Password: {{ $user->rawpw }}
@endcomponent

@if ( $user->exadmin )
You can change this password on your account page when you next login.
@else
If you forget your password it will have to be changed, so we suggest you save it.
@endif

Regards,<br>
<span style="color: #32A387;">The Shuttle Bug Team</span><br>
[www.shuttlebug.co.za](http://www.shuttlebug.co.za)
@endcomponent
