@extends('layouts.myaccount')

@section('title')
    <title>Billing</title>
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
                        <h3>Billing</h3>
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
                <div class="row mb-5">
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
                        <div id="showInvoice" class="d-none"></div>
                    </div>
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
                        url: '/myaccount/billing/invoice/' + id + '/' + $(this).val(),
                        success: function (data) {
                            $(elemStat).addClass('d-none');
                            $(elemInv).empty().html(data).removeClass('d-none');
                            $(elemEmail).removeClass('d-none');
                        }
                    });
                }
            });
        });
    </script>
@endsection