<nav class="navbar navbar-light navbar-glass navbar-top navbar-expand">
    <button class="btn navbar-toggler-humburger-icon navbar-toggler me-1 me-sm-3" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbarVerticalCollapse" aria-controls="navbarVerticalCollapse" aria-expanded="false"
        aria-label="Toggle Navigation">
        <span class="navbar-toggle-icon"><span class="toggle-line"></span></span>
    </button>

    <a class="navbar-brand me-1 me-sm-3" href="{{ route('dashboard') }}">
        <div class="d-flex align-items-center">
            <img class="me-2" src="{{ asset('assets/img/icons/spot-illustrations/falcon.png') }}" alt=""
                width="40" />
            <span class="font-sans-serif">Construction System</span>
        </div>
    </a>

    <ul class="navbar-nav navbar-nav-icons ms-auto flex-row align-items-center">
        <li class="nav-item dropdown">
            <a class="nav-link pe-0" id="navbarDropdownUser" role="button" data-bs-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <div class="avatar avatar-xl">
                    <img class="rounded-circle"
                        src="{{ Auth::user()->profile_picture ?? asset('assets/img/team/default.jpg') }}"
                        alt="User Avatar">
                </div>
            </a>

            <div class="dropdown-menu dropdown-menu-end py-0" aria-labelledby="navbarDropdownUser">
                <div class="bg-white rounded-2 py-2">
                    <a class="dropdown-item" href="#">Profile</a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </li>
    </ul>
</nav>
