<nav class="navbar navbar-expand-lg navbar-inverse fixed-top">
    <a class="navbar-brand" href=""><img src="/images/brand-icon.png" alt="Shuttle Bug"> {{ Auth::user()->name }}</a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navmenu" aria-controls="navmenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navmenu">

        {{-- left menu ----------------------------------------------------------------------------------------}}

        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link {!! set_start_active('myaccount/profile') !!}" href="{!! url('myaccount/profile',[Auth::user()->id]) !!}">Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {!! set_start_active('myaccount/guardians') !!}" href="{!! url('myaccount/guardians',[Auth::user()->id]) !!}">Guardians</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {!! set_start_active('myaccount/children') !!}" href="{!! url('myaccount/children',[Auth::user()->id]) !!}">Children</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {!! set_start_active('myaccount/xmurals') !!}" href="{!! url('myaccount/xmurals',[Auth::user()->id]) !!}">Extramurals</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {!! set_start_active('myaccount/bookings') !!}" href="{!! url('myaccount/bookings',[Auth::user()->id]) !!}">Bookings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {!! set_start_active('myaccount/billing') !!}" href="{!! url('myaccount/billing', [Auth::user()->id]) !!}">Billing</a>
            </li>
        </ul>

        {{-- right menu ----------------------------------------------------------------------------------------}}

        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="{!! url('/') !!}">Home</a></li>
            {{--<li class="nav-item"><a class="nav-link" href="{!! url('auth/logout') !!}">Logout</a></li>--}}
            <li class="nav-item"><a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    {{ csrf_field() }}
                </form>
        </ul>
    </div>
</nav>