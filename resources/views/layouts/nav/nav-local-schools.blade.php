{{--local nav--}}
<nav class="d-none d-md-block sidebar">
    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link {!! set_full_active('office/schools') !!}" href="{!! url('office/schools') !!}">Manage</a>
        </li>
        <li class="nav-item d-none d-lg-inline-block">
            <a class="nav-link {!! set_incl_active('users/schools/index') !!}" href="{!! url('office/schools/index') !!}">School List</a>
        </li>
        @if ( Request::is('*/create') || Request::is('*/edit*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">School Form</a>
            </li>
        @endif
    </ul>
</nav>