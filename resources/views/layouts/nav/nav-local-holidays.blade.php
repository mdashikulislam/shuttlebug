{{--local nav--}}
<nav class="d-none d-md-block sidebar">
    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link {!! set_full_active('office/schools/holidays') !!}" href="{!! url('office/holidays') !!}">Manage</a>
        </li>
        @if ( Request::is('*/public/create') || Request::is('*/public/edit*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Public Form</a>
            </li>
        @endif
        @if ( Request::is('*/holidays/create') || Request::is('*/holidays/edit*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Terms Form</a>
            </li>
        @endif
    </ul>
</nav>