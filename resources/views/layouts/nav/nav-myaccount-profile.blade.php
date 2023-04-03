{{--local nav--}}
<nav class="d-none d-md-block sidebar">
    <ul class="nav nav-pills flex-column">
        <li class="nav-item">
            <a class="nav-link {!! set_incl_active('myaccount/profile') !!}" href="{!! url('myaccount/profile',[Auth::user()->id]) !!}">Profile</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {!! set_incl_active('myaccount/password') !!}" href="{!! url('myaccount/password') !!}">Password</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {!! set_incl_active('myaccount/account') !!}" href="{!! url('myaccount/account') !!}">Account</a>
        </li>
    </ul>
</nav>
