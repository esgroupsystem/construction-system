@extends('layouts.app')

@section('title', 'Employee Details')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Employee Details</h4>
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning btn-sm">Edit</a>
            </div>

            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-3">
                        @if ($employee->photo_path)
                            <img src="{{ asset('storage/' . $employee->photo_path) }}" class="img-fluid rounded border">
                        @else
                            <div class="border rounded p-4 text-center text-muted">No Photo</div>
                        @endif
                    </div>

                    <div class="col-md-9">
                        <table class="table table-bordered">
                            <tr>
                                <th width="200">Employee No</th>
                                <td>{{ $employee->employee_no }}</td>
                            </tr>
                            <tr>
                                <th>Full Name</th>
                                <td>{{ $employee->full_name }}</td>
                            </tr>
                            <tr>
                                <th>Department</th>
                                <td>{{ $employee->department ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Position</th>
                                <td>{{ $employee->position ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{{ $employee->is_active ? 'Active' : 'Inactive' }}</td>
                            </tr>
                            <tr>
                                <th>Face Registered At</th>
                                <td>{{ $employee->face_registered_at ? $employee->face_registered_at->format('M d, Y h:i A') : 'Not yet registered' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
