@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Add Location</h5>
                <small class="text-muted">Create new project/site location</small>
            </div>

            <div class="card-body">
                <form action="{{ route('locations.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Location Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="Example: Main Office">

                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                                value="{{ old('address') }}" placeholder="Example: Quezon City">

                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                    id="is_active" checked>

                                <label class="form-check-label" for="is_active">
                                    Active Location
                                </label>
                            </div>
                        </div>

                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('locations.index') }}" class="btn btn-falcon-default">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <span class="fas fa-save me-1"></span>
                            Save Location
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
