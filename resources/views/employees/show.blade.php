@extends('layouts.app')

@section('title', 'Employee Details')

@section('content')
    <div class="container-fluid px-0" data-layout="container">
        <div class="content">

            <div class="card mb-4">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url({{ asset('assets/img/icons/spot-illustrations/corner-4.png') }});">
                </div>

                <div class="card-body position-relative">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Employee Details</h3>
                            <p class="text-muted mb-0">
                                View complete employee profile and information.
                            </p>
                        </div>

                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <a href="{{ route('employees.index') }}" class="btn btn-falcon-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>

                            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-falcon-warning">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-4">

                        <div class="col-lg-4 text-center">
                            <div class="avatar avatar-4xl mb-3">
                                @if ($employee->photo_path)
                                    <img src="{{ asset('storage/' . $employee->photo_path) }}"
                                        class="rounded-circle border shadow-sm">
                                @else
                                    <div class="avatar-name rounded-circle bg-soft-primary text-primary">
                                        <span class="fs-1 fw-bold">
                                            {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <h4 class="mb-1">{{ $employee->full_name }}</h4>
                            <div class="text-600 mb-2">
                                Employee No: <strong>{{ $employee->employee_no }}</strong>
                            </div>

                            <span class="badge bg-{{ $employee->is_active ? 'success' : 'secondary' }} px-3 py-2">
                                {{ $employee->is_active ? 'Active' : 'Inactive' }}
                            </span>

                            <div class="mt-3">
                                @if ($employee->face_registered_at)
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i> Face Registered
                                    </span>
                                @else
                                    <span class="badge bg-soft-warning px-3 py-2">
                                        <i class="fas fa-exclamation-circle me-1"></i> No Face Data
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="text-600 fs--1">Department</label>
                                    <div class="fw-semibold">{{ $employee->department ?? '-' }}</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="text-600 fs--1">Position</label>
                                    <div class="fw-semibold">{{ $employee->position ?? '-' }}</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="text-600 fs--1">Rate / Salary</label>
                                    <div class="fw-semibold">
                                        {{ $employee->rate_salary !== null ? number_format($employee->rate_salary, 2) : '-' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="text-600 fs--1">Payroll Type</label>
                                    <div class="fw-semibold">
                                        {{ ucfirst($employee->payroll_type ?? '-') }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="text-600 fs--1">Location</label>
                                    <div class="fw-semibold">{{ $employee->location ?? '-' }}</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="text-600 fs--1">Schedule</label>
                                    <div class="fw-semibold">
                                        @if ($employee->schedule_time_in && $employee->schedule_time_out)
                                            {{ \Carbon\Carbon::parse($employee->schedule_time_in)->format('h:i A') }}
                                            -
                                            {{ \Carbon\Carbon::parse($employee->schedule_time_out)->format('h:i A') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="text-600 fs--1">Day Offs</label>
                                    <div class="fw-semibold">
                                        @if (!empty($employee->day_offs))
                                            {{ implode(', ', $employee->day_offs) }}
                                        @else
                                            None
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="text-600 fs--1">Face Registered At</label>
                                    <div class="fw-semibold">
                                        {{ $employee->face_registered_at ? $employee->face_registered_at->format('M d, Y h:i A') : 'Not yet registered' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="text-600 fs--1">Account Status</label>
                                    <div class="fw-semibold">
                                        {{ $employee->is_active ? 'Active Employee' : 'Inactive Employee' }}
                                    </div>
                                </div>

                            </div>

                            <div class="mt-4 d-flex gap-2 flex-wrap">
                                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-falcon-warning">
                                    <i class="fas fa-edit me-1"></i> Edit Profile
                                </a>

                                <button class="btn btn-outline-primary">
                                    <i class="fas fa-camera me-1"></i> Register Face
                                </button>

                                <button class="btn btn-outline-secondary">
                                    <i class="fas fa-calendar-alt me-1"></i> View Attendance
                                </button>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
