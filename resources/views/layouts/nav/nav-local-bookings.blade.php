{{--local nav--}}
<nav class="d-none d-md-block sidebar">
    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link {!! set_full_active('office/bookings') !!}" href="{!! url('office/bookings') !!}">Manage</a>
        </li>
        @if ( Request::is('*/bookings/edit/*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Booking</a>
            </li>
        @endif
        @if ( Request::is('*/bookings/duplicate/*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Duplicating</a>
            </li>
        @endif
        @if ( Request::is('*/list/daily/*') )
            <li class="nav-item d-none d-lg-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Daily List</a>
            </li>
        @endif
        @if ( Request::is('*/list/customer/*') )
            <li class="nav-item d-none d-lg-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Customer list</a>
            </li>
        @endif
        @if ( Request::is('*/bookings/email/*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Bookings Email</a>
            </li>
        @endif
    </ul>
</nav>