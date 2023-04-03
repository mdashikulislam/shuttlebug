{{--local nav--}}
<nav class="d-none d-md-block sidebar">
    <ul class="nav nav-pills">
        <li class="nav-item">
            <a class="nav-link" href="#">ANALYTICS</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {!! set_full_active('office/analytics') !!}" href="{!! url('office/analytics') !!}">Bookings</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {!! set_incl_active('analytics/financials') !!}" href="{!! url('office/analytics/financials') !!}">Financials</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {!! set_incl_active('analytics/trips') !!}" href="{!! url('office/analytics/trips') !!}">Trips</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {!! set_incl_active('analytics/customers') !!}" href="{!! url('office/analytics/customers') !!}">Customers</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {!! set_incl_active('analytics/vehicles') !!}" href="{!! url('office/analytics/vehicles') !!}">Vehicles</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {!! set_incl_active('analytics/history') !!}" href="{!! url('office/analytics/history') !!}">Booking History</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {!! set_incl_active('analytics/schools') !!}" href="{!! url('office/analytics/schools') !!}">School History</a>
        </li>
    </ul>
</nav>