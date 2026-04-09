@extends('layouts.app')

@section('title', 'Face Registration')

@section('content')
    <div class="container-fluid">

        <div class="card shadow-sm p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-1">Face Registration</h4>
                    <p class="text-muted mb-0">Select employee to register face</p>
                </div>
            </div>

            <div class="row">
                @forelse($employees as $employee)
                    <div class="col-md-3 mb-3">
                        <div class="card border shadow-sm p-3 h-100">

                            <div class="mb-2">
                                <strong>{{ $employee->full_name }}</strong>
                            </div>

                            <div class="text-muted small mb-2">
                                {{ $employee->employee_no }}
                            </div>

                            {{-- STATUS --}}
                            <div class="mb-3">
                                @if ($employee->face_registered_at)
                                    <span class="badge bg-success">Registered</span>
                                @else
                                    <span class="badge bg-warning text-dark">Not Registered</span>
                                @endif
                            </div>

                            <a href="{{ route('face-registration.show', $employee) }}"
                                class="btn btn-primary btn-sm mt-auto">
                                Open Registration
                            </a>

                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-light border">
                            No employees found.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
@endsection
