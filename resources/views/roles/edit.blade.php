@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
    <div class="container">
        <h1 class="mb-4">Edit Role</h1>

        <form action="{{ route('roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Role Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
            </div>

            <div class="card">
                <div class="card-header">Permissions</div>
                <div class="card-body">
                    @foreach ($permissions as $module => $modulePermissions)
                        <div class="mb-4">
                            <h5 class="text-capitalize">{{ str_replace('-', ' ', $module) }}</h5>
                            <div class="row">
                                @foreach ($modulePermissions as $permission)
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]"
                                                value="{{ $permission->name }}" id="perm_{{ $permission->id }}"
                                                {{ in_array($permission->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                {{ $permission->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">Update Role</button>
                <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
