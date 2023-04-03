{{--local nav--}}
<nav class="d-none d-md-block sidebar">
    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link {!! set_full_active('office/prices') !!}" href="{!! url('office/prices') !!}">Manage</a>
        </li>
        <li class="nav-item d-none d-lg-inline-block">
            <a class="nav-link {!! set_incl_active('prices/index') !!} {!! set_incl_active('prices/edit') !!}" href="{!! url('office/prices/index') !!}">Standard Prices</a>
        </li>
        <li class="nav-item d-none d-lg-inline-block">
            <a class="nav-link {!! set_incl_active('special/index') !!}  {!! set_incl_active('special/edit') !!}" href="{!! url('office/prices/special/index') !!}">Special Prices</a>
        </li>
        <li class="nav-item d-none d-lg-inline-block">
            <a class="nav-link {!! set_incl_active('promotion/index') !!}  {!! set_incl_active('promotion/edit') !!}" href="{!! url('office/prices/promotion/index') !!}">Promotions</a>
        </li>
        @if ( Request::is('*/edit*') || Request::is('*/create*') )
            <li class="nav-item d-none d-md-inline-block">
                <a class="nav-link active" href="">Form</a>
            </li>
        @endif
    </ul>
</nav>