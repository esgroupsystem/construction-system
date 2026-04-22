@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
    <div class="container-fluid px-0" data-layout="container">
        <div class="content">

            <div class="card mb-4">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url({{ asset('assets/img/icons/spot-illustrations/corner-4.png') }});">
                </div>

                <div class="card-body position-relative">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Edit Employee</h3>
                            <p class="text-muted mb-0">Update employee details.</p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <a href="{{ route('employees.index') }}" class="btn btn-falcon-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-body-tertiary border-bottom py-3">
                        <h5 class="mb-0">Employee Information</h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Employee No</label>
                                <input type="text" name="employee_no" class="form-control"
                                    value="{{ old('employee_no', $employee->employee_no) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control"
                                    value="{{ old('full_name', $employee->full_name) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" class="form-control"
                                    value="{{ old('department', $employee->department) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Position</label>
                                <input type="text" name="position" class="form-control"
                                    value="{{ old('position', $employee->position) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Rate / Salary</label>
                                <input type="number" step="0.01" min="0" name="rate_salary" class="form-control"
                                    value="{{ old('rate_salary', $employee->rate_salary) }}" placeholder="e.g. 15000.00">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Payroll Type</label>
                                <select name="payroll_type" class="form-select" required>
                                    <option value="monthly"
                                        {{ old('payroll_type', $employee->payroll_type) === 'monthly' ? 'selected' : '' }}>
                                        Monthly</option>
                                    <option value="weekly"
                                        {{ old('payroll_type', $employee->payroll_type) === 'weekly' ? 'selected' : '' }}>
                                        Weekly</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Schedule Time In</label>
                                <input type="time" name="schedule_time_in" class="form-control"
                                    value="{{ old('schedule_time_in', $employee->schedule_time_in ? \Carbon\Carbon::parse($employee->schedule_time_in)->format('H:i') : '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Schedule Time Out</label>
                                <input type="time" name="schedule_time_out" class="form-control"
                                    value="{{ old('schedule_time_out', $employee->schedule_time_out ? \Carbon\Carbon::parse($employee->schedule_time_out)->format('H:i') : '') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Location / Assignment</label>
                                <input type="text" name="location" class="form-control"
                                    value="{{ old('location', $employee->location) }}"
                                    placeholder="e.g. Main Office / Site A">
                            </div>

                            <div class="col-12">
                                <label class="form-label d-block">Day Offs</label>
                                <div class="row g-2">
                                    @php
                                        $days = [
                                            'Monday',
                                            'Tuesday',
                                            'Wednesday',
                                            'Thursday',
                                            'Friday',
                                            'Saturday',
                                            'Sunday',
                                        ];
                                        $selectedDayOffs = old('day_offs', $employee->day_offs ?? []);
                                    @endphp

                                    @foreach ($days as $day)
                                        <div class="col-md-3 col-sm-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="day_offs[]"
                                                    value="{{ $day }}" id="day_{{ $day }}"
                                                    {{ in_array($day, $selectedDayOffs) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="day_{{ $day }}">
                                                    {{ $day }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Select employee permanent day off(s).</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Photo</label>
                                <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                            </div>

                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label">Active</label>
                                </div>
                            </div>

                            @if ($employee->photo_path)
                                <div class="col-12">
                                    <label class="form-label d-block">Current Photo</label>
                                    <img src="{{ asset('storage/' . $employee->photo_path) }}" width="90"
                                        class="rounded border">
                                </div>
                            @endif

                        </div>
                    </div>

                    <div class="card-footer bg-body-tertiary text-end">
                        <a href="{{ route('employees.index') }}" class="btn btn-falcon-secondary">
                            Cancel
                        </a>
                        <button class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Employee
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
