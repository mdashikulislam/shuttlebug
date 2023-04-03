@extends('layouts.front')

@section('title')
    <title>FAQ</title>
@endsection

@section('css')
    @parent
    <link rel="stylesheet" href="{!! asset('css/front.css') !!}">
@endsection

@section('style')
    <style>
        dt { font-weight: 400; margin-bottom: 15px; cursor: pointer; }
        dd { display: none; color: #6fb3f3; padding: 15px; }
    </style>
@endsection

@section('content')
    <div class="container">
        @include('layouts.nav.front-nav')

        <header>
            <div class="row justify-content-end h-100">
                <div class="col-6 col-md-9 col-lg-12 my-auto">
                    <h1 class="text-center">safely transporting your children</h1>
                </div>
            </div>
        </header>

        <section class="leader text-center mt-5">
            @include('layouts/leader-menu')
        </section>

        <section class="article pt-5">
            <h1 class="">Frequently Asked Questions</h1>
            <br>
            <dl>
                <dt>Which schools are included in your shuttle service?</dt>
                <dd>Take a look at the Schools link above.</dd>
                <dt>Is there a restriction on age?</dt>
                <dd>No, all ages are welcome. All our vehicle are equipped with car and booster seats for our younger passengers.</dd>
                <dt>What times do you pick up and drop off?</dt>
                <dd>From 7am to 5pm.</dd>
                <dt>What happens if my child is late for collection?</dt>
                <dd>Due to our busy schedules we require parents to inform us of time changes the evening before. We will do our utmost to accommodate these unforeseen problems but cannot guarantee that the time slots will be available. We are in touch with the schools and are informed of time changes by them.</dd>
                <dt>Will you collect my child after extramural activities?</dt>
                <dd>Absolutely, we have dedicated schedules for each child which includes extramurals and play dates with different drop-off locations.</dd>
                <dt>What if after-school activity times vary?</dt>
                <dd>Our service allows you the flexibility to change schedules to suite.</dd>
                <dt>Can my child take a friend on a shuttle?</dt>
                <dd>Yes, provided the friend is booked timeously. There will be an extra charge for the friend.<br>
                    For safety reasons the destination address will be the same as your child's booking.</dd>
                <dt>I need your shuttle service but my child's school is not on your list – what do I do?</dt>
                <dd><a href="{!! url('contact') !!}">Contact us</a> to see if it's possible to provide a shuttle service.</dd>
                <dt>If my child is not going to school on a particular day, by when should I notify you?</dt>
                <dd>We understand that things change and we ask parents to please let us know the evening before if possible, alternatively first thing in the morning. If notice is received after 9am you will still be charged as normal.</dd>
                <dt>Can I use your service on an ad-hoc basis?</dt>
                <dd>Absolutely, as long as your schedule is given to us timeously.</dd>
                <dt>Can the pick-up and drop-off locations vary?</dt>
                <dd>Yes, as long the venue is within the serviced area.</dd>
                <dt>Is there a minimum sign-up period and notice period?</dt>
                <dd>There is no minimum period and no notice period.</dd>
                <dt>Where can I get reviews from other parents who use your service?</dt>
                <dd>You are most welcome to contact our office for references.</dd>
                <dt>I want to meet your staff before I use your service, how do we arrange this?</dt>
                <dd>Please call our office and we will gladly set up a convenient time for you to join us for coffee.</dd>
                {{--<dt>What happens if your vehicle breaks down en route?</dt>--}}
                {{--<dd>We do have back up vehicles and available drivers at a moments notice.</dd>--}}
                <dt>Do I sign any indemnities or waivers when I sign-up for your service?</dt>
                <dd>No, we are fully licensed and insured.</dd>
                <dt>How do I make payment?</dt>
                <dd>You will receive an invoice on the 28th of each month which is payable on presentation. Payment by EFT is preferred.</dd>
                <dt>How do I know where my child is?</dt>
                <dd>You are notified by SMS every time your child is dropped off. Should we experience major traffic delays we will also notify you by SMS.</dd>
                <dt>What happens during school holidays?</dt>
                <dd>The shuttle service is still available during holidays, to any venue within the shuttle area.</dd>
            </dl>
        </section>
    </div>

    <section class="footer">
        <div class="container">
            @include('layouts/footer')
        </div>
    </section>
@endsection

@section('script')
@endsection

@section('jquery')
    <script>
        $(function() {
            $('dt').on('click', function() {
                $(this).next().toggle();
            });
        });
    </script>
@endsection