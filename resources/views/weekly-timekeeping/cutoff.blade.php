@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        {{-- Cutoff Header --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col">
                        <h4 class="mb-1">
                            {{ $cutoff->cutoff_name ?? 'Weekly Cutoff #' . $cutoff->id }}
                        </h4>

                        <div class="text-muted">
                            <span class="fas fa-calendar-alt me-1"></span>
                            {{ \Carbon\Carbon::parse($cutoff->date_from)->format('M d, Y') }}
                            -
                            {{ \Carbon\Carbon::parse($cutoff->date_to)->format('M d, Y') }}
                        </div>
                    </div>

                    <div class="col-auto d-flex gap-2">
                        @if ($cutoff->status !== 'finalized')
                            <form method="POST" action="{{ route('weekly-timekeeping.cutoffs.finalize', $cutoff->id) }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="btn btn-falcon-success btn-sm">
                                    <span class="fas fa-check-circle me-1"></span>
                                    Finalize Cutoff
                                </button>
                            </form>
                        @else
                            <span class="badge rounded-pill badge-subtle-success">
                                Finalized
                            </span>
                        @endif

                        <a href="{{ route('weekly-timekeeping.index') }}" class="btn btn-falcon-default btn-sm">
                            <span class="fas fa-arrow-left me-1"></span>
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cutoff Summary --}}
        <div class="row g-3 mb-3">

            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-2xl">
                                <div class="avatar-name rounded-circle bg-soft-primary text-primary">
                                    <span class="fas fa-users"></span>
                                </div>
                            </div>

                            <div class="ms-3">
                                <h6 class="mb-1 text-muted">Total Employees</h6>
                                <h4 class="mb-0">{{ $employees->total() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-2xl">
                                <div class="avatar-name rounded-circle bg-soft-success text-success">
                                    <span class="fas fa-user-check"></span>
                                </div>
                            </div>

                            <div class="ms-3">
                                <h6 class="mb-1 text-muted">Active Employees</h6>
                                <h4 class="mb-0">{{ $employees->total() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-2xl">
                                <div class="avatar-name rounded-circle bg-soft-info text-info">
                                    <span class="fas fa-calendar-day"></span>
                                </div>
                            </div>

                            <div class="ms-3">
                                <h6 class="mb-1 text-muted">Cutoff Days</h6>
                                <h4 class="mb-0">
                                    {{ \Carbon\Carbon::parse($cutoff->date_from)->diffInDays(\Carbon\Carbon::parse($cutoff->date_to)) + 1 }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-2xl">
                                <div class="avatar-name rounded-circle bg-soft-warning text-warning">
                                    <span class="fas fa-clipboard-check"></span>
                                </div>
                            </div>

                            <div class="ms-3">
                                <h6 class="mb-1 text-muted">Cutoff Status</h6>

                                @if ($cutoff->status === 'finalized')
                                    <span class="badge rounded-pill px-3 py-2"
                                        style="background-color: #d1f7e5; color: #198754; font-weight: 600;">
                                        Finalized
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-3 py-2"
                                        style="background-color: #fff3cd; color: #b76e00; font-weight: 600;">
                                        {{ ucfirst($cutoff->status) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Cutoff Information --}}
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">Cutoff Information</h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted fs--1 mb-1">Date From</div>
                            <div class="fw-semibold">
                                {{ \Carbon\Carbon::parse($cutoff->date_from)->format('F d, Y') }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted fs--1 mb-1">Date To</div>
                            <div class="fw-semibold">
                                {{ \Carbon\Carbon::parse($cutoff->date_to)->format('F d, Y') }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="text-muted fs--1 mb-1">Included Employees</div>
                            <div class="fw-semibold">
                                {{ $employees->total() }} employee(s)
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Employees Table --}}
        <div class="card">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="mb-0">Active Weekly Employees</h6>
                        <small class="text-muted">Employees included in this cutoff period</small>
                    </div>

                    <div class="col-auto">
                        <span class="badge rounded-pill badge-subtle-primary">
                            {{ $employees->total() }} employee(s)
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive scrollbar">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="bg-200 text-900">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Employee No.</th>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pe-3">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($employees as $employee)
                                <tr>
                                    <td class="ps-3 text-muted">
                                        {{ $loop->iteration + ($employees->currentPage() - 1) * $employees->perPage() }}
                                    </td>

                                    <td class="fw-semibold">
                                        {{ $employee->employee_no }}
                                    </td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $employee->full_name }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $employee->location ?? 'No location' }}
                                        </small>
                                    </td>

                                    <td>{{ $employee->department ?? 'N/A' }}</td>

                                    <td>{{ $employee->position ?? 'N/A' }}</td>

                                    <td class="text-center">
                                        <span class="badge rounded-pill px-3 py-2"
                                            style="background-color: #d1f7e5; color: #198754; font-weight: 600;">

                                            <span class="fas fa-circle me-1"
                                                style="font-size: 7px; color: #198754;"></span>

                                            Active
                                        </span>
                                    </td>

                                    <td class="text-center pe-3">
                                        <a href="{{ route('weekly-timekeeping.employees.show', [
                                            'cutoff' => $cutoff->id,
                                            'employee' => $employee->id,
                                        ]) }}"
                                            class="btn btn-falcon-primary btn-sm">
                                            <span class="fas fa-folder-open me-1"></span>
                                            Open
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <span class="fas fa-users-slash fs-3 mb-2 d-block"></span>
                                            <h6 class="mb-1">No weekly employees found</h6>
                                            <small>No employees are included in this cutoff.</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($employees->hasPages())
                <div class="card-footer bg-light">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
