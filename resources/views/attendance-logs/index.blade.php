@extends('layouts.app')

@section('title', 'Attendance Logs')

@section('content')
    <div class="container-fluid px-0" data-layout="container">
        <div class="content">

            {{-- HEADER --}}
            <div class="card mb-4">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url({{ asset('assets/img/icons/spot-illustrations/corner-4.png') }});">
                </div>

                <div class="card-body position-relative">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Attendance Logs</h3>
                            <p class="text-muted mb-0">View employee daily time in and time out records.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FILTER CARD --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <h5 class="mb-0">Filter Attendance</h5>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('attendance-logs.index') }}">
                        <div class="row g-3">

                            <div class="col-md-4">
                                <label class="form-label">Search Employee</label>
                                <input type="text" name="search" class="form-control"
                                    placeholder="Employee no or full name" value="{{ request('search') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control"
                                    value="{{ request('date_from') }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <div class="w-100 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('attendance-logs.index') }}" class="btn btn-falcon-secondary">
                                        Reset
                                    </a>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            {{-- TABLE CARD --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">Attendance Records</h5>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-hover align-middle mb-0 fs--1">
                            <thead class="bg-200 text-800">
                                <tr>
                                    <th class="ps-3">EMPLOYEE</th>
                                    <th>DATE</th>
                                    <th>SCHEDULE</th>
                                    <th>TIME IN</th>
                                    <th>TIME OUT</th>
                                    <th>SUMMARY</th>
                                    <th>WORKED</th>
                                    <th>IN METHOD</th>
                                    <th>OUT METHOD</th>
                                    <th>IN CONF.</th>
                                    <th>OUT CONF.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendanceLogs as $log)
                                    <tr>
                                        <td class="ps-3">
                                            <div>
                                                <h6 class="mb-0">{{ $log->employee->full_name ?? 'Unknown Employee' }}
                                                </h6>
                                                <div class="fs--2 text-600">
                                                    {{ $log->employee->employee_no ?? '-' }}
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            {{ $log->attendance_date ? $log->attendance_date->format('M d, Y') : '-' }}
                                        </td>

                                        <td>
                                            @if ($log->employee && $log->employee->schedule_time_in && $log->employee->schedule_time_out)
                                                <div class="fw-semibold">
                                                    {{ \Carbon\Carbon::parse($log->employee->schedule_time_in)->format('h:i A') }}
                                                    -
                                                    {{ \Carbon\Carbon::parse($log->employee->schedule_time_out)->format('h:i A') }}
                                                </div>
                                            @else
                                                <span class="text-muted">No schedule</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($log->time_in)
                                                <span class="badge bg-soft-success text-success">
                                                    {{ \Carbon\Carbon::parse($log->time_in)->format('h:i A') }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($log->time_out)
                                                <span class="badge bg-soft-primary text-primary">
                                                    {{ \Carbon\Carbon::parse($log->time_out)->format('h:i A') }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge bg-{{ $log->attendance_summary_class }}">
                                                {{ $log->attendance_summary_text }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="fw-semibold">
                                                {{ $log->worked_text }}
                                            </span>
                                        </td>

                                        <td>
                                            @if ($log->time_in_method)
                                                <span class="badge bg-info-subtle text-info">
                                                    {{ ucfirst(str_replace('_', ' ', $log->time_in_method)) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($log->time_out_method)
                                                <span class="badge bg-info-subtle text-info">
                                                    {{ ucfirst(str_replace('_', ' ', $log->time_out_method)) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if (!is_null($log->time_in_confidence))
                                                {{ number_format((float) $log->time_in_confidence * 100, 2) }}%
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if (!is_null($log->time_out_confidence))
                                                {{ number_format((float) $log->time_out_confidence * 100, 2) }}%
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center py-5">
                                            <h6 class="text-600 mb-1">No attendance records found</h6>
                                            <p class="text-muted mb-0">Try adjusting your filters or wait for attendance
                                                entries.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-body-tertiary border-top py-3">
                    {{ $attendanceLogs->links() }}
                </div>
            </div>

        </div>
    </div>
@endsection
