<nav class="navbar navbar-light navbar-vertical navbar-expand-xl">
    <script>
        var navbarStyle = localStorage.getItem("navbarStyle");
        if (navbarStyle && navbarStyle !== 'transparent') {
            document.querySelector('.navbar-vertical').classList.add(`navbar-${navbarStyle}`);
        }
    </script>

    <div class="d-flex align-items-center">
        <div class="toggle-icon-wrapper">
            <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle" data-bs-toggle="tooltip"
                data-bs-placement="left" title="Toggle Navigation">
                <span class="navbar-toggle-icon"><span class="toggle-line"></span></span>
            </button>
        </div>

        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <div class="d-flex align-items-center py-3">
                <img class="me-2" src="{{ asset('assets/img/icons/spot-illustrations/falcon.png') }}" alt=""
                    width="40" />
                <span class="font-sans-serif">Construction System</span>
            </div>
        </a>
    </div>

    <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
        <div class="navbar-vertical-content scrollbar">
            <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-chart-pie"></span></span>
                            <span class="nav-link-text ps-1">Dashboard</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-users"></span></span>
                            <span class="nav-link-text ps-1">Employees</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-user-check"></span></span>
                            <span class="nav-link-text ps-1">Attendance</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-money-check-alt"></span></span>
                            <span class="nav-link-text ps-1">Payroll</span>
                        </div>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>
