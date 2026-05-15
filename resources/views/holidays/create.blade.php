@extends('layouts.app')

@section('title', 'Add Holiday')

@section('content')
    <div class="container-fluid px-0" data-layout="container">
        <div class="content">

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Add Holiday</h3>
                            <p class="text-muted mb-0">Create custom Philippine holiday.</p>
                        </div>

                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <a href="{{ route('holidays.index') }}" class="btn btn-falcon-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('holidays.store') }}" method="POST">
                @csrf

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
                                    value="{{ old('holiday_date') }}" required>

                                @error('holiday_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Holiday Name</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                    placeholder="Example: New Year's Day" required>

                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Holiday Type</label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="regular" {{ old('type') === 'regular' ? 'selected' : '' }}>
                                        Regular Holiday
                                    </option>
                                    <option value="special_non_working"
                                        {{ old('type') === 'special_non_working' ? 'selected' : '' }}>
                                        Special Non-Working Day
                                    </option>
                                </select>

                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 d-flex align-items-center">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                    <label class="form-check-label">Active</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Optional remarks">{{ old('notes') }}</textarea>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer bg-body-tertiary text-end">
                        <a href="{{ route('holidays.index') }}" class="btn btn-falcon-secondary">
                            Cancel
                        </a>

                        <button class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Holiday
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection
