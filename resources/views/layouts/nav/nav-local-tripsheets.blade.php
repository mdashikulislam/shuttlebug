{{--local nav--}}
<nav class="d-none d-md-block sidebar">
    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link {!! set_full_active('office/operations/tripsheets') !!}" href="{!! url('office/operations/tripsheets') !!}">Manage</a>
        </li>
        @if ( Request::is('*/summary') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Summary</a>
            </li>
        @endif
        @if ( Request::is('*/vehicle') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Trip Sheet</a>
            </li>
        @endif
    </ul>
</nav>