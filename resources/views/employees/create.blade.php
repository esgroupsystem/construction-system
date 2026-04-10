@extends('layouts.app')

@section('title', 'Add Employee')

@section('content')
    <div class="container-fluid px-0" data-layout="container">
        <div class="content">

            {{-- HEADER --}}
            <div class="card mb-4">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url({{ asset('assets/img/icons/spot-illustrations/corner-4.png') }});">
                </div>

                <div class="card-body position-relative">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Add Employee</h3>
                            <p class="text-muted mb-0">Create a new employee profile.</p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <a href="{{ route('employees.index') }}" class="btn btn-falcon-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FORM CARD --}}
            <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-body-tertiary border-bottom py-3">
                        <h5 class="mb-0">Employee Information</h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Employee No</label>
                                <input type="text" name="employee_no" class="form-control"
                                    value="{{ old('employee_no') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" class="form-control"
                                    value="{{ old('department') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Position</label>
                                <input type="text" name="position" class="form-control" value="{{ old('position') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Photo</label>
                                <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                            </div>

                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                    <label class="form-check-label">Active</label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer bg-body-tertiary text-end">
                        <a href="{{ route('employees.index') }}" class="btn btn-falcon-secondary">
                            Cancel
                        </a>
                        <button class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Employee
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
@endsection
