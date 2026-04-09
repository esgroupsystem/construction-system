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
                <span class="navbar-toggle-icon">
                    <span class="toggle-line"></span>
                </span>
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

                {{-- DASHBOARD --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                        href="{{ route('dashboard') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><span class="fas fa-chart-pie"></span></span>
                            <span class="nav-link-text ps-1">Dashboard</span>
                        </div>
                    </a>
                </li>

                {{-- EMPLOYEE MANAGEMENT --}}
                <li class="nav-item mt-3">
                    <div class="row navbar-vertical-label-wrapper mb-2">
                        <div class="col-auto navbar-vertical-label">Employee Management</div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider" />
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link dropdown-indicator" href="#employeeMenu" role="button" data-bs-toggle="collapse"
                        aria-expanded="{{ request()->is('employees*') ? 'true' : 'false' }}"
                        aria-controls="employeeMenu">

                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-id-badge"></span>
                            </span>
                            <span class="nav-link-text ps-1">Employees</span>
                        </div>
                    </a>

                    <ul class="nav collapse {{ request()->is('employees*') ? 'show' : '' }}" id="employeeMenu">

                        {{-- EMPLOYEE LIST --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('employees.index') ? 'active' : '' }}"
                                href="{{ route('employees.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-text ps-4">Employee List</span>
                                </div>
                            </a>
                        </li>

                        {{-- FACE REGISTRATION --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('face-registration*') ? 'active' : '' }}"
                                href="{{ route('face-registration.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-text ps-4">Face Registration</span>
                                </div>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('face-recognition*') ? 'active' : '' }}"
                                href="{{ route('face-recognition.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-text ps-4">Face Recognition</span>
                                </div>
                            </a>
                        </li>

                        {{-- ATTENDANCE LOGS --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('face-logs*') ? 'active' : '' }}"
                                href="#">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-text ps-4">Attendance Logs</span>
                                </div>
                            </a>
                        </li>

                    </ul>
                </li>

                {{-- USER MANAGEMENT --}}
                @canany(['users.view', 'roles.view'])
                    <li class="nav-item mt-3">
                        <div class="row navbar-vertical-label-wrapper mb-2">
                            <div class="col-auto navbar-vertical-label">User Management</div>
                            <div class="col ps-0">
                                <hr class="mb-0 navbar-vertical-divider" />
                            </div>
                        </div>
                    </li>
                @endcanany

                {{-- USERS --}}
                @can('users.view')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                            href="{{ route('users.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span class="fas fa-users"></span></span>
                                <span class="nav-link-text ps-1">Users</span>
                            </div>
                        </a>
                    </li>
                @endcan

                {{-- ROLES --}}
                @can('roles.view')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                            href="{{ route('roles.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span class="fas fa-user-shield"></span></span>
                                <span class="nav-link-text ps-1">Roles & Permissions</span>
                            </div>
                        </a>
                    </li>
                @endcan

            </ul>
        </div>
    </div>
</nav>
