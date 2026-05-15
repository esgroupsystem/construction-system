@extends('layouts.app')

@section('title', 'Edit Holiday')

@section('content')
    <div class="container-fluid px-0" data-layout="container">
        <div class="content">

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Edit Holiday</h3>
                            <p class="text-muted mb-0">Update holiday date, type, or status.</p>
                        </div>

                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <a href="{{ route('holidays.index', ['year' => $holiday->holiday_date->format('Y')]) }}"
                                class="btn btn-falcon-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('holidays.update', $holiday) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-body-tertiary border-bottom py-3">
                        <h5 class="mb-0">Holiday Information</h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Holiday Date</label>
                                <input type="date" name="holiday_date"
                                    class="form-control @error('holiday_date') is-invalid @enderror"
                                    value="{{ old('holiday_date', $holiday->holiday_date->format('Y-m-d')) }}" required>

                                @error('holiday_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Holiday Name</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $holiday->name) }}" required>

                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Holiday Type</label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="regular"
                                        {{ old('type', $holiday->type) === 'regular' ? 'selected' : '' }}>
                                        Regular Holiday
                                    </option>
                                    <option value="special_non_working"
                                        {{ old('type', $holiday->type) === 'special_non_working' ? 'selected' : '' }}>
                                        Special Non-Working Day
                                    </option>
                                </select>

                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        {{ old('is_active', $holiday->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label">Active</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $holiday->notes) }}</textarea>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer bg-body-tertiary text-end">
                        <a href="{{ route('holidays.index', ['year' => $holiday->holiday_date->format('Y')]) }}"
                            class="btn btn-falcon-secondary">
                            Cancel
                        </a>

                        <button class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Holiday
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection
