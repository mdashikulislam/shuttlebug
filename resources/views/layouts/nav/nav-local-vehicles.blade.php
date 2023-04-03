{{--local nav--}}
<nav class="d-none d-md-block sidebar">
    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link {!! set_full_active('office/operations/vehicles') !!}" href="{!! url('office/operations/vehicles') !!}">Manage</a>
        </li>
        <li class="nav-item d-none d-lg-inline-block">
            <a class="nav-link {!! set_incl_active('vehicles/index') !!}" href="{!! url('office/operations/vehicles/index') !!}">Vehicle List</a>
        </li>
        <li class="nav-item d-none d-lg-inline-block">
            <a class="nav-link {!! set_incl_active('attendants/index') !!}" href="{!! url('office/operations/attendants/index') !!}">Attendant List</a>
        </li>
        @if ( Request::is('*vehicles/create') || Request::is('*vehicles/edit*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Vehicle Form</a>
            </li>
        @endif
        @if ( Request::is('*attendants/create') || Request::is('*attendants/edit*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Attendant Form</a>
            </li>
        @endif
    </ul>
</nav>