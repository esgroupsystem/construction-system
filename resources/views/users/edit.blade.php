@extends('layouts.app')

@section('title', 'Assign Role')

@section('content')
    <div class="container">
        <h1 class="mb-4">Assign Role to {{ $user->name }}</h1>

        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Select Role</label>
                <select name="role" class="form-select">
                    <option value="">-- No Role --</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}"
                            {{ old('role', $selectedRole) === $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button class="btn btn-primary">Save</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
@endsection
