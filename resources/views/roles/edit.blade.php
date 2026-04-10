@extends('layouts.app')

@section('title', 'Edit Role')

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
                            <h3 class="mb-2">Edit Role</h3>
                            <p class="text-muted mb-0">
                                Update role details and modify permissions.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <a href="{{ route('roles.index') }}" class="btn btn-falcon-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Roles
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MAIN CARD --}}
            <div class="card border-0 shadow-sm">

                {{-- HEADER --}}
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="row g-3 align-items-center justify-content-between">
                        <div class="col-lg-auto">
                            <h5 class="mb-1 text-900">Role Information</h5>
                            <p class="mb-0 fs--1 text-600">
                                Modify role name and assigned permissions.
                            </p>
                        </div>

                        <div class="col-lg-auto">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">
                                <i class="fas fa-check-double me-1"></i> Select All
                            </button>
                        </div>
                    </div>
                </div>

                {{-- FORM --}}
                <form action="{{ route('roles.update', $role) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">

                        {{-- ROLE NAME --}}
                        <div class="row mb-4">
                            <div class="col-lg-6">
                                <label class="form-label fw-semibold">Role Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="{{ old('name', $role->name) }}" required>
                            </div>
                        </div>

                        {{-- PERMISSIONS --}}
                        @foreach ($permissions as $module => $modulePermissions)
                            @php
                                $grouped = collect($modulePermissions)->keyBy(function ($perm) {
                                    return explode('.', $perm->name)[1] ?? '';
                                });
                            @endphp

                            <div class="mb-4">

                                {{-- MODULE HEADER --}}
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-uppercase text-primary fw-bold mb-0">
                                        {{ str_replace('-', ' ', $module) }}
                                    </h6>

                                    <button type="button" class="btn btn-sm btn-outline-secondary select-module"
                                        data-module="{{ $module }}">
                                        Select All
                                    </button>
                                </div>

                                {{-- CRUD ROW --}}
                                <div class="row g-2">
                                    @foreach (['create', 'delete', 'update', 'view'] as $action)
                                        @php $perm = $grouped[$action] ?? null; @endphp

                                        @if ($perm)
                                            <div class="col-lg-3 col-md-6">
                                                <div class="border rounded px-3 py-2 bg-white hover-shadow-sm">
                                                    <div class="form-check d-flex align-items-center m-0">

                                                        <input
                                                            class="form-check-input perm-checkbox module-{{ $module }} me-2"
                                                            type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                                            id="perm_{{ $perm->id }}"
                                                            {{ in_array($perm->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}>

                                                        <label class="form-check-label small mb-0"
                                                            for="perm_{{ $perm->id }}">
                                                            {{ ucfirst($action) }}
                                                        </label>

                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                            </div>
                        @endforeach

                    </div>

                    {{-- FOOTER --}}
                    <div class="card-footer bg-body-tertiary border-top py-3">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('roles.index') }}" class="btn btn-falcon-secondary">
                                Cancel
                            </a>
                            <button class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Update Role
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
        document.getElementById('selectAll').addEventListener('click', function() {
            document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = true);
        });

        document.querySelectorAll('.select-module').forEach(button => {
            button.addEventListener('click', function() {
                let module = this.dataset.module;
                document.querySelectorAll('.module-' + module).forEach(cb => cb.checked = true);
            });
        });
    </script>

    <style>
        .hover-shadow-sm:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: 0.2s;
        }
    </style>
@endsection
