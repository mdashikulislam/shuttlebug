@component('mail::message')
Hi {{ $user->first_name }}

@if ( $data['ver'] == 'month-end' )
Your current statement and invoice are attached and the balance due is R {{ number_format($data['bal'],2) }}.

@if ( $data['bal'] > 0 )
Please note that invoices are payable on presentation.
@endif

The attachment can be read using any pdf viewer such as Adobe Reader.

@elseif ( $data['ver'] == 'followup' )
We notice there is still an outstanding balance on your account of R {{ number_format($data['bal'],2) }}.

Just a reminder that our invoices are payable on presentation.

Your latest statement is attached which reflects all transactions up to yesterday. Your last invoice was emailed at the end of last month but if you don't have it, please contact us and we'll send it again.

@else
Attached is the invoice you requested.
@endif

You can also view your invoices & statements at any time by logging into your account at [www.shuttlebug.co.za](https://shuttlebug.co.za) and going to MyAccount -> Billing,

@component('mail::panel')
Our banking details are:

Bank: <strong>Standard Bank</strong><br>
A/c Name: <strong>Busy Bus (Pty) Ltd</strong><br>
A/c No: <strong>071 710 205</strong><br>
Branch: <strong>025309</strong>

Please quote your name or client number as reference.<br>
@endcomponent

Regards,<br>
<span style="color: #32A387;">The Shuttle Bug Team</span><br>
[www.shuttlebug.co.za](https://shuttlebug.co.za)
@endcomponent
