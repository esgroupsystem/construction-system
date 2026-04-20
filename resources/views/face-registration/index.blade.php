@extends('layouts.app')

@section('title', 'Face Registration')

@section('content')
    <div class="container-fluid">

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">Face Registration</h3>
                    <p class="text-muted mb-0">
                        Manage employee face registration for recognition system.
                    </p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            @forelse($employees as $employee)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 h-100">

                        <div class="text-center pt-4">
                            <img src="{{ $employee->photo_path ? asset('storage/' . $employee->photo_path) : asset('assets/img/team/avatar.png') }}"
                                class="rounded-circle border" width="80" height="80" style="object-fit: cover;">
                        </div>

                        <div class="card-body text-center">
                            <h6 class="mb-1 fw-semibold">{{ $employee->full_name }}</h6>

                            <div class="text-muted small mb-1">
                                {{ $employee->employee_no ?: '-' }}
                            </div>

                            <div class="text-muted small mb-2">
                                {{ $employee->department ?: 'No Department' }}
                            </div>

                            <div class="small mb-3">
                                Samples: <strong>{{ $employee->face_samples_count ?? 0 }}</strong>
                            </div>

                            @if ($employee->face_registered_at && ($employee->face_samples_count ?? 0) > 0)
                                <span class="badge bg-success px-3 py-2">
                                    Registered
                                </span>
                            @else
                                <span class="badge bg-warning text-dark px-3 py-2">
                                    Not Registered
                                </span>
                            @endif
                        </div>

                        <div class="card-footer bg-transparent border-0 text-center pb-4">
                            <a href="{{ route('face-registration.show', $employee) }}" class="btn btn-primary btn-sm w-100">
                                {{ ($employee->face_samples_count ?? 0) > 0 ? 'Update Face' : 'Register Face' }}
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <h5 class="mb-1">No Employees Found</h5>
                            <p class="text-muted mb-0">
                                There are no employees available for face registration.
                            </p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $employees->links() }}
        </div>
    </div>
@endsection
