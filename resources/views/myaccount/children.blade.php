@extends('layouts.myaccount')

@section('title')
    <title>Children</title>
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
                        <h3>Children</h3>
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
                <p class="text-muted mb-5">Add the children who will use the shuttle service.<br>To book a shuttle for a friend, the friend needs to be added here.</p>

                <div class="row">
                    <div class="col-sm-4 col-xl-2 mb-2">
                        {!! Form::select('children', ['' => 'Children'] +$children, null, ['class' => "form-control custom-select callform", 'id' => 'actChild']) !!}
                    </div>

                    <div class="col-sm-4 col-xl-2 mb-2">
                        {!! Form::select('friends', ['' => 'Friends'] +$friends, null, ['class' => "form-control custom-select callform", 'id' => 'actFriend']) !!}
                    </div>

                    <div class="col-sm-4 col-xl-2 mb-2">
                        <button class="btn btn-outline-secondary addchild">Add Child</button>
                    </div>
                </div>

                {{-- form ----------------------------------------------------------------------------------------}}

                <div class="row mt-5">
                    <div class="form-container col-md-12 col-xl-9 ml-xl-5">
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
            {{-- new child form --}}
            $('.addchild').on('click', function() {
                let user = "{{ $user->id }}";
                $('.form-container').load('/myaccount/form/' + user + '/null', function () {
                    $('.form-container').show();
                });
            });

            {{-- load child form --}}
            $('.callform').on('change', function() {
                let child = $(this).val();
                let user = "{{ $user->id }}";
                if ( child > '' ) {
                    $('.form-container').load('/myaccount/form/' + user + '/' + child, function () {
                        $('.form-container').show();
                    });
                } else {
                    $('.form-container').empty();
                }
            });
        });
    </script>
@endsection