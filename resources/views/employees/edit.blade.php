@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-header">
                <h4 class="mb-0">Edit Employee</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

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
                            <label class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        </div>

                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                    value="1" {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>

                        @if ($employee->photo_path)
                            <div class="col-12">
                                <label class="form-label d-block">Current Photo</label>
                                <img src="{{ asset('storage/' . $employee->photo_path) }}" width="80" class="rounded">
                            </div>
                        @endif
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('employees.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
