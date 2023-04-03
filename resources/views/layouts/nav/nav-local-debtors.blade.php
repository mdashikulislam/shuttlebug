{{--local nav--}}
<nav class="d-none d-md-block sidebar">
    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link {!! set_full_active('office/debtors') !!}" href="{!! url('office/debtors') !!}">Manage</a>
        </li>
        @if ( Request::is('*/journal/*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Journal</a>
            </li>
        @endif
        @if ( Request::is('*/financials/*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Invoice</a>
            </li>
        @endif
        @if ( Request::is('*/outstanding') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Outstanding</a>
            </li>
        @endif
        @if ( Request::is('*/deliveries/*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active"  style="color:#fff" href="">Deliveries</a>
            </li>
        @endif
    </ul>
</nav>