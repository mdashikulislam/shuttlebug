@extends('layouts/office')

@section('title')
    <title>Invoice</title>
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
                        <h3><small>{{ $customer->id }}:</small> {{ $customer->name }}</h3>
                    </div>

{{-- buttons ----------------------------------------------------------------------------------------}}

                    <div class="col-sm-7">
                        <div class="row">
                            <div class="col-4 text-center">
                                {!! Form::select('month', $months, null, ['class' => "form-control form-control-sm custom-select", 'id' => 'month']) !!}
                            </div>
                            <div class="col-8 text-center">
                                <button class="btn btn-outline-dark btn-sm" id="callinv">Invoice</button>
                                <button class="btn btn-outline-dark btn-sm ml-2" id="callstat">Statement</button>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="mt-2 mb-5">

{{-- statement ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg-8 col-xl-6">
                        <div id="showStatement">
                            @include('layouts.statement')
                        </div>
                    </div>
                </div>

{{-- invoice ----------------------------------------------------------------------------------------}}

                <div class="row">
                    <div class="col-lg-8 col-xl-6">
                        <div id="emailbtn" class="d-none">
                            <button class="btn btn-outline-dark btn-sm mb-3" id="sendinv">Email This Invoice</button>
                        </div>
                        <div id="showInvoice" class="d-none"></div>
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

            let elemInv = $('#showInvoice');
            let elemStat = $('#showStatement');
            let elemEmail = $('#emailbtn');

            {{-- toggle view, calls inv if not yet loaded --}}
            $('#callinv, #callstat').on('click', function() {
                if ( $(this).attr('id') === 'callinv' ) {
                    if ($(elemInv).is(':empty')) {
                        $('#month').trigger('change');
                    } else {
                        $(elemStat).addClass('d-none');
                        $(elemInv).removeClass('d-none');
                        $(elemEmail).removeClass('d-none');
                    }
                } else {
                    $(elemInv).addClass('d-none');
                    $(elemEmail).addClass('d-none');
                    $(elemStat).removeClass('d-none');
                }
            });

            {{-- load the selected invoice --}}
            $('#month').on('change', function() {
                if ( $(this).val() > '' ) {
                    let id = "{!! $customer->id !!}";
                    $.ajax({
                        type: "GET",
                        url: '/office/debtors/invoice/' + id + '/' + $(this).val(),
                        success: function (data) {
                            $(elemStat).addClass('d-none');
                            $(elemInv).empty().html(data).removeClass('d-none');
                            $(elemEmail).removeClass('d-none');
                        }
                    });
                }
            });

            {{-- send pdf invoice --}}
            $('#sendinv').on('click', function() {
                let date = $('#month').val();
                let id = "{!! $customer->id !!}";
                $('#ajaxloader').show();

                $.ajax({
                    type: "GET",
                    url: '/office/debtors/pdf/inv/' + id + '/' + date,
                    success: function (data) {
                        $('#ajaxloader').hide();
                        $('#sendinv').prop('disabled', true);
                    }
                });
            });

        });
    </script>
@stop