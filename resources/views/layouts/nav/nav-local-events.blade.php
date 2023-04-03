{{--local nav--}}
<nav class="d-none d-md-block sidebar">
    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link {!! set_full_active('office/events') !!}" href="{!! url('office/events') !!}">Manage</a>
        </li>
        @if ( Request::is('*/events/edit/*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Booking</a>
            </li>
        @endif
        @if ( Request::is('*/list/customer/*') )
            <li class="nav-item d-none d-lg-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Customer list</a>
            </li>
        @endif
        @if ( Request::is('*/events/email/*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Bookings Email</a>
            </li>
        @endif
    </ul>
</nav>