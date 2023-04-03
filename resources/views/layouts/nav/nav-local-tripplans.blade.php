{{--local nav--}}
<nav class="d-none d-md-block sidebar">
    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link {!! set_full_active('office/operations/tripplans') !!}" href="{!! url('office/operations/tripplans') !!}">Manage</a>
        </li>
        <li class="nav-item d-none d-lg-inline-block">
            <a class="nav-link {!! set_incl_active('tripplans/settings') !!}" href="{!! url('office/operations/tripplans/settings') !!}">Settings</a>
        </li>
        @if ( Request::is('*/tripplans/settings/*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Settings</a>
            </li>
        @endif
        @if ( Request::is('*/tripplans/plan*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Trip Plan</a>
            </li>
        @endif
    </ul>
</nav>