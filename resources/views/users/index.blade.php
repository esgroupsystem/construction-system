@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="container-fluid px-0" data-layout="container">
        <div class="content">

            {{-- PAGE HEADER --}}
            <div class="card mb-4">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url({{ asset('assets/img/icons/spot-illustrations/corner-4.png') }});">
                </div>

                <div class="card-body position-relative">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Users Directory</h3>
                            <p class="text-muted mb-0">
                                Manage user accounts, roles, access level, and login status.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            @can('users.create')
                                <a href="{{ route('users.create') }}" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-1"></i> Add User
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            {{-- SUCCESS ALERT --}}
            @if (session('success'))
                <div class="alert alert-success border-0 d-flex align-items-center shadow-sm mb-4" role="alert">
                    <span class="fas fa-check-circle me-2"></span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            {{-- MAIN CARD --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="row g-3 align-items-center justify-content-between">
                        <div class="col-lg-auto">
                            <div>
                                <h5 class="mb-1 text-900">Users Directory</h5>
                                <p class="mb-0 fs--1 text-600">
                                    View all registered users and manage their access roles.
                                </p>
                            </div>
                        </div>

                        <div class="col-lg-5 col-xl-4">
                            <form method="GET" action="{{ route('users.index') }}">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <span class="fas fa-search text-400"></span>
                                    </span>
                                    <input type="text" class="form-control border-start-0" name="search"
                                        placeholder="Search employee name or email..." value="{{ request('search') }}">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-hover table-striped align-middle mb-0 fs--1">
                            <thead class="bg-200 text-800">
                                <tr>
                                    <th class="ps-3 py-3" width="35%">USER DETAILS</th>
                                    <th class="py-3" width="30%">EMAIL ADDRESS</th>
                                    <th class="py-3" width="20%">ASSIGNED ROLE</th>
                                    <th class="text-center pe-3 py-3" width="15%">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    @php
                                        $role = $user->roles->first()?->name;
                                    @endphp

                                    <tr>
                                        <td class="ps-3 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xl me-3">
                                                    <div class="avatar-name rounded-circle bg-soft-primary text-primary">
                                                        <span class="fw-bold fs-0">
                                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 text-800 fw-semibold">{{ $user->name }}</h6>
                                                    <div class="fs--2 text-600">
                                                        Registered system account
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="py-3">
                                            <div class="fw-medium text-800" style="color:#344050;">{{ $user->email }}</div>
                                            <div class="fs--2 text-600">Login email address</div>
                                        </td>

                                        <td class="py-3">
                                            @if ($role)
                                                <span class="badge bg-soft-primary text-800 px-3 py-2">
                                                    <span class="fas fa-user-shield me-1"></span>
                                                    {{ ucfirst($role) }}
                                                </span>
                                            @else
                                                <span class="badge bg-soft-secondary text-800 px-3 py-2">
                                                    <span class="fas fa-minus-circle me-1"></span>
                                                    No Role Assigned
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-center pe-3 py-3">
                                            @can('users.update')
                                                <a href="{{ route('users.edit', $user) }}"
                                                    class="btn btn-falcon-warning btn-sm px-3">
                                                    <span class="fas fa-user-cog me-1"></span>
                                                    Assign Role
                                                </a>
                                            @else
                                                <span class="badge rounded-pill badge-subtle-secondary px-3 py-2">
                                                    No Access
                                                </span>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="py-4">
                                                <div class="avatar avatar-4xl mb-3">
                                                    <div
                                                        class="avatar-name rounded-circle bg-soft-secondary text-secondary">
                                                        <span class="fas fa-users fs-1"></span>
                                                    </div>
                                                </div>
                                                <h5 class="mb-1 text-700">No users found</h5>
                                                <p class="text-600 mb-0">
                                                    No matching user records are available at the moment.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($users->hasPages())
                    <div class="card-footer bg-body-tertiary border-top py-3">
                        <div class="row g-2 align-items-center justify-content-between">
                            <div class="col-auto">
                                <div class="fs--1 text-600">
                                    Showing
                                    <strong>{{ $users->firstItem() }}</strong>
                                    to
                                    <strong>{{ $users->lastItem() }}</strong>
                                    of
                                    <strong>{{ $users->total() }}</strong>
                                    entries
                                </div>
                            </div>
                            <div class="col-auto">
                                {{ $users->withQueryString()->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
