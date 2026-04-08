@extends('layouts.app')

@section('title', 'Roles')

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
                            <h3 class="mb-2">Roles Management</h3>
                            <p class="text-muted mb-0">
                                Manage system roles, permissions, and user access levels.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            @can('roles.create')
                                <a href="{{ route('roles.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Create Role
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            {{-- ALERTS --}}
            @if (session('success'))
                <div class="alert alert-success border-0 d-flex align-items-center shadow-sm mb-4">
                    <span class="fas fa-check-circle me-2"></span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger border-0 d-flex align-items-center shadow-sm mb-4">
                    <span class="fas fa-exclamation-circle me-2"></span>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            {{-- MAIN CARD --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="row g-3 align-items-center justify-content-between">
                        <div class="col-lg-auto">
                            <div>
                                <h5 class="mb-1 text-900">Roles Directory</h5>
                                <p class="mb-0 fs--1 text-body-secondary">
                                    View all roles and manage their permissions and assigned users.
                                </p>
                            </div>
                        </div>

                        <div class="col-lg-5 col-xl-4">
                            <form method="GET">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <span class="fas fa-search text-400"></span>
                                    </span>
                                    <input type="text" class="form-control border-start-0" name="search"
                                        placeholder="Search role name..." value="{{ request('search') }}">
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
                                    <th class="ps-3 py-3" width="25%">ROLE NAME</th>
                                    <th class="py-3" width="45%">PERMISSIONS</th>
                                    <th class="py-3 text-center" width="10%">USERS</th>
                                    <th class="text-center pe-3 py-3" width="20%">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        {{-- ROLE NAME --}}
                                        <td class="ps-3 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xl me-3">
                                                    <div class="avatar-name rounded-circle bg-soft-primary text-primary">
                                                        <span class="fw-bold fs-0">
                                                            {{ strtoupper(substr($role->name, 0, 1)) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 text-800 fw-semibold">
                                                        {{ ucfirst($role->name) }}
                                                    </h6>
                                                    <div class="fs--2 text-body-secondary">
                                                        System Role
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- PERMISSIONS --}}
                                        <td class="py-3">
                                            @forelse ($role->permissions as $permission)
                                                <span class="badge bg-soft-secondary text-800 border me-1 mb-1">
                                                    {{ $permission->name }}
                                                </span>
                                            @empty
                                                <span class="text-muted fs--1">No permissions</span>
                                            @endforelse
                                        </td>

                                        {{-- USERS COUNT --}}
                                        <td class="text-center py-3">
                                            <span class="badge bg-soft-info text-800">
                                                {{ $role->users_count }}
                                            </span>
                                        </td>

                                        {{-- ACTION --}}
                                        <td class="text-center pe-3 py-3">
                                            @can('roles.update')
                                                <a href="{{ route('roles.edit', $role) }}"
                                                    class="btn btn-falcon-warning btn-sm px-3 me-1">
                                                    <span class="fas fa-edit me-1"></span>
                                                    Edit
                                                </a>
                                            @endcan

                                            @can('roles.delete')
                                                <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-falcon-danger btn-sm px-3"
                                                        onclick="return confirm('Delete this role?')">
                                                        <span class="fas fa-trash me-1"></span>
                                                        Delete
                                                    </button>
                                                </form>
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
                                                        <span class="fas fa-user-shield fs-1"></span>
                                                    </div>
                                                </div>
                                                <h5 class="mb-1 text-700">No roles found</h5>
                                                <p class="text-body-secondary mb-0">
                                                    No roles available at the moment.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- PAGINATION --}}
                @if ($roles->hasPages())
                    <div class="card-footer bg-body-tertiary border-top py-3">
                        <div class="row g-2 align-items-center justify-content-between">
                            <div class="col-auto">
                                <div class="fs--1 text-body-secondary">
                                    Showing
                                    <strong>{{ $roles->firstItem() }}</strong>
                                    to
                                    <strong>{{ $roles->lastItem() }}</strong>
                                    of
                                    <strong>{{ $roles->total() }}</strong>
                                    entries
                                </div>
                            </div>
                            <div class="col-auto">
                                {{ $roles->withQueryString()->links() }}
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
