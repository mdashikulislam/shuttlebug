{{--local nav--}}
<nav class="d-none d-md-block sidebar">
    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link {!! set_full_active('office/users') !!}" href="{!! url('office/users') !!}">Manage</a>
        </li>
        <li class="nav-item d-none d-lg-inline-block">
            <a class="nav-link {!! set_incl_active('users/customers/index') !!}" href="{!! url('office/users/customers/index') !!}">Customer List</a>
        </li>
        <li class="nav-item d-none d-lg-inline-block">
            <a class="nav-link {!! set_incl_active('users/admins/index') !!}" href="{!! url('office/users/admins/index') !!}">Admin List</a>
        </li>
        @if ( Request::is('*/customers/create') || Request::is('*/customers/edit*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Customer Form</a>
            </li>
        @endif
        @if ( Request::is('*/admins/create') || Request::is('*/admins/edit*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Admin Form</a>
            </li>
        @endif
    </ul>
</nav>