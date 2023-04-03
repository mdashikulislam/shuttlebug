<nav class="navbar navbar-expand-lg navbar-inverse fixed-top">
    <a class="navbar-brand" href="{!! url('office') !!}"><img src="/images/brand-icon.png" alt="Shuttle Bug"> Office:</a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navmenu" aria-controls="navmenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navmenu">

        {{-- left menu ----------------------------------------------------------------------------------------}}

        <ul class="navbar-nav mr-auto">

            {{-- customers ----------------------------------------------------------------------------------------}}

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {!! set_start_active('office/users') !!}" href="#" data-toggle="dropdown">Customers</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{!! url('office/users/') !!}">Customers</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{!! url('office/users/bulkmail') !!}">Bulk Mail</a>
                </div>
            </li>

            {{-- bookings ----------------------------------------------------------------------------------------}}

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {!! set_start_active('office/bookings') !!}" href="#" data-toggle="dropdown">Bookings</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{!! url('office/bookings') !!}">Shuttle Bookings</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{!! url('office/events') !!}">Event Bookings</a>
                </div>
            </li>

            {{-- operations ----------------------------------------------------------------------------------------}}

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {!! set_start_active('office/operations') !!}" href="#" data-toggle="dropdown">Operations</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{!! url('office/operations/tripplans') !!}">Trip Planning</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{!! url('office/operations/tripsheets') !!}">Trip Sheets</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{!! url('office/operations/vehicles') !!}">Vehicles & Attendants</a>
                </div>
            </li>

            {{-- schools ----------------------------------------------------------------------------------------}}

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {!! set_start_active('office/schools') !!} {!! set_start_active('office/holidays') !!}" href="#" data-toggle="dropdown">Schools</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{!! url('office/schools') !!}">Schools</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{!! url('office/holidays') !!}">Holidays</a>
                </div>
            </li>

            {{-- debtors ----------------------------------------------------------------------------------------}}

            <li class="nav-item">
                <a class="nav-link {!! set_start_active('office/debtors') !!}" href="{!! url('office/debtors') !!}">Debtors</a>
            </li>

            {{-- prices ----------------------------------------------------------------------------------------}}

            <li class="nav-item">
                <a class="nav-link {!! set_start_active('office/prices') !!}" href="{!! url('office/prices') !!}">Prices</a>
            </li>
        </ul>

        {{-- right menu ----------------------------------------------------------------------------------------}}

        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="{!! url('/') !!}">Website</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{!! url('auth/logout') !!}">Logout</a>
            </li>
        </ul>
    </div>
</nav>