<nav class="navbar navbar-expand-sm navbar-dark">
    <a class="navbar-brand" href=""></a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navmenu" aria-controls="navmenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navmenu">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item"><a class="nav-link" href="{!! url('/') !!}">Home</a></li>
            <li class="nav-item d-inline-block d-lg-none"><a class="nav-link" href="{!! url('faq') !!}">FAQ</a></li>
            <li class="nav-item"><a class="nav-link" href="{!! url('contact') !!}">Contact Us</a></li>
        </ul>

        <ul class="navbar-nav">
            @if( !Auth::check() )
                <li class="nav-item ml-3"><a class="nav-link" href="{{ route('login') }}"><i class="fa fa-user"></i> Login</a></li>
            @else
                @if ( Auth::user()->role == 'admin' )
                    <li class="nav-item ml-3"><a class="nav-link" href="{!! url('office') !!}"><i class="fa fa-user"></i> Office</a></li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{!! url('myaccount/profile',[Auth::user()->id]) !!}"><i class="fa fa-user"></i> My Account</a></li>
                @endif
                <li class="nav-item"><a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        {{ csrf_field() }}
                    </form>
            @endif
        </ul>
    </div>
</nav>