<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{!! csrf_token() !!}">

    @section('title')
    @show

    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.16/sl-1.2.5/datatables.min.css"/>
    <link rel="stylesheet" href="{{ asset('css/bootstrap-datepicker3.standalone.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/back.css') }}">
    @section('css')
    @show

    @section('style')
    @show
</head>

<body>
@include('layouts.nav.nav-office')
@yield('content')

<div id="ajaxloader"></div>
{{-- modals --}}
<div class="modal-container"></div>

<div class="modal fade" id="exception" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h3 class="modal-title"><em>No Record !</em></h3>
            </div>
            <div id="exbody" class="modal-body text-center text-info"></div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

@section('script')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.16/sl-1.2.5/datatables.min.js"></script>
    <script src="{!! asset('js/multifilter.js') !!}"></script>
    <script src="{!! asset('js/bootstrap-datepicker.min.js') !!}"></script>
    <script src="{!! asset('js/back.js') !!}"></script>
@show

@section('jquery')
    <script>
        let holidays = @php echo json_encode(Cache::get('holidays'.now()->year)); @endphp

        $(function() {
            // findOrFail exceptions
            @if ( session()->has('exception') )
                $("#exbody").html("{!! session()->get('exception') !!}");
                $('#exception').modal('show');
            @endif

            // hijack links (used on md devices to disable sidebar links)
            $(document).on('click', '.hijack', function (event) {
                event.preventDefault();
            });

            // standard datepicker
            $('.datepicker').datepicker({
                format:         'yyyy-mm-dd',
                autoclose:      true,
                weekStart:      1,
                todayHighlight: 'true',
                beforeShowDay: function (date) {
                    let calendar_date = (date.getFullYear() + '-0' + (date.getMonth() + 1) + '-0' + date.getDate()).replace(/-0(\d\d)/g, '-$1');
                    let search_index = $.inArray(calendar_date, holidays);
                    if ( search_index !== -1 || $.inArray(date.getDay(), [0,6]) !== -1 ) {
                        return {classes: 'mask-cal-dates'};
                    }
                }
            });
        });
    </script>
@show
</body>
</html>