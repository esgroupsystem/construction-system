@extends('layouts.app')

@section('title', 'Employees')

@section('content')
    <div class="container-fluid">

        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Employees</h4>
                    <small class="text-muted">Manage employee records</small>
                </div>

                <a href="{{ route('employees.create') }}" class="btn btn-primary">
                    + Add Employee
                </a>
            </div>

            <div class="card-body">

                <form class="mb-3">
                    <input type="text" name="search" class="form-control" placeholder="Search employee no or name"
                        value="{{ request('search') }}">
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Employee No</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Status</th>
                                <th width="150">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($employees as $emp)
                                <tr>
                                    <td>
                                        @if ($emp->photo_path)
                                            <img src="{{ asset('storage/' . $emp->photo_path) }}" width="40"
                                                class="rounded-circle">
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $emp->employee_no }}</td>
                                    <td>{{ $emp->full_name }}</td>
                                    <td>{{ $emp->department }}</td>
                                    <td>{{ $emp->position }}</td>
                                    <td>
                                        <span class="badge bg-{{ $emp->is_active ? 'success' : 'secondary' }}">
                                            {{ $emp->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('employees.show', $emp) }}" class="btn btn-sm btn-info">View</a>
                                        <a href="{{ route('employees.edit', $emp) }}"
                                            class="btn btn-sm btn-warning">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No employees found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $employees->links() }}

            </div>
        </div>

    </div>
@endsection
