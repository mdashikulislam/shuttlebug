<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{!! csrf_token() !!}">

    <title>Invoice</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <style>
        body, table { font-size: 14px; }
        h3, h5 { font-family: sans-serif; }
        .table-sm td, .table-sm th { padding: .1rem; }
        .small { font-size: 12px; }
        .page-break { page-break-after: always; }
    </style>
</head>

<body>
<div class="container">
    @include('layouts.statement')
    <hr>
    <div class="small">
        <p>Payments should be made by EFT to the following account:<br>
            Please quote your name or Client # ({{ $customer->id }}) as reference.</p>
        <p>
            Bank : Standard Bank<br>
            A/c Name : Busy Bus (Pty) Ltd<br>
            A/c No : 071 710 205<br>
            Branch : 02 53 09<br>
        </p>
    </div>

    <div class="page-break"></div>

    @include('layouts.invoice')
</div>

</body>
</html>