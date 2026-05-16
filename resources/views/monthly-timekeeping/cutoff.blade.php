@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        {{-- Header --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-3xl me-3">
                                <div class="avatar-name rounded bg-soft-secondary text-700">
                                    <span class="far fa-calendar-alt fs-3"></span>
                                </div>
                            </div>

                            <div>
                                <h3 class="mb-1 fw-bold text-900">Monthly Timekeeping Details</h3>
                                <div class="text-600 fs-0">
                                    {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }}
                                    -
                                    {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-auto">
                        <a href="{{ route('monthly-timekeeping.cutoffs.show', $cutoff->id) }}"
                            class="btn btn-falcon-default btn-sm px-3">
                            <span class="fas fa-arrow-left me-2"></span>
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Review Status --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col">
                        @if ($employeeFinalized)
                            <span class="badge rounded-pill badge-subtle-success text-success px-3 py-2">
                                <span class="fas fa-check-circle me-1"></span>
                                Employee Timekeeping Finalized
                            </span>
                        @else
                            <span class="badge rounded-pill badge-subtle-warning text-warning px-3 py-2">
                                <span class="fas fa-clock me-1"></span>
                                Pending OT Review
                            </span>
                        @endif
                    </div>

                    <div class="col-auto d-flex gap-2">
                        @if (!$employeeFinalized)
                            <form method="POST"
                                action="{{ route('monthly-timekeeping.employees.finalize', ['cutoff' => $cutoff->id, 'employee' => $employee->id]) }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="btn btn-falcon-success btn-sm confirm-action"
                                    data-title="Finalize Employee Timekeeping"
                                    data-message="Make sure all computed OT is approved or rejected before finalizing."
                                    data-confirm-text="Finalize">
                                    <span class="fas fa-check-circle me-1"></span>
                                    Finalize Employee
                                </button>
                            </form>
                        @else
                            <form method="POST"
                                action="{{ route('monthly-timekeeping.employees.unfinalize', ['cutoff' => $cutoff->id, 'employee' => $employee->id]) }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="btn btn-falcon-warning btn-sm confirm-action"
                                    data-title="Reopen Employee Timekeeping"
                                    data-message="Are you sure you want to reopen this employee timekeeping?"
                                    data-confirm-text="Reopen">
                                    <span class="fas fa-undo me-1"></span>
                                    Reopen
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Cards --}}
        <div class="row g-3 mb-3">

            <div class="col-md-6 col-xl-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-3xl">
                                <div class="avatar-name rounded-circle bg-soft-primary text-primary">
                                    <span class="far fa-user fs-3"></span>
                                </div>
                            </div>

                            <div class="ms-3">
                                <div class="text-600 fs--1">Employee</div>
                                <h6 class="mb-1 fw-bold text-900">{{ $employee->full_name }}</h6>
                                <span class="badge bg-primary">{{ $employee->employee_no }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-3xl">
                                <div class="avatar-name rounded-circle bg-soft-success text-success">
                                    <span class="fas fa-briefcase fs-3"></span>
                                </div>
                            </div>

                            <div class="ms-3">
                                <div class="text-600 fs--1">Department</div>
                                <h6 class="mb-1 fw-bold text-900">{{ $employee->department }}</h6>
                                <div class="text-600">{{ $employee->position }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-3xl">
                                <div class="avatar-name rounded-circle bg-soft-info text-info">
                                    <span class="fas fa-map-marker-alt fs-3"></span>
                                </div>
                            </div>

                            <div class="ms-3">
                                <div class="text-600 fs--1">Location</div>
                                <h6 class="mb-1 fw-bold text-900">{{ $employee->location }}</h6>
                                <span class="badge bg-primary">{{ ucfirst($employee->payroll_type) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-3xl">
                                <div class="avatar-name rounded-circle bg-soft-warning text-warning">
                                    <span class="far fa-clock fs-3"></span>
                                </div>
                            </div>

                            <div class="ms-3">
                                <div class="text-600 fs--1">Schedule</div>
                                <h6 class="mb-1 fw-bold text-900">
                                    {{ \Carbon\Carbon::parse($employee->schedule_time_in)->format('h:i A') }}
                                    -
                                    {{ \Carbon\Carbon::parse($employee->schedule_time_out)->format('h:i A') }}
                                </h6>
                                <div class="text-600">{{ $attendances->count() }} day(s)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Attendance Report --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white border-bottom py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-xl me-2">
                                <div class="avatar-name rounded bg-soft-primary text-primary">
                                    <span class="far fa-clipboard fs-1"></span>
                                </div>
                            </div>

                            <div>
                                <h5 class="mb-1 fw-bold text-900">Monthly Attendance Report</h5>
                                <small class="text-600">
                                    Review computed OT, approve OT, then finalize employee timekeeping.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="col-auto">
                        <span class="badge rounded-pill badge-subtle-primary px-4 py-2 fs--1">
                            {{ $attendances->count() }} day(s)
                        </span>
                    </div>
                </div>
            </div>

            @if (!$employeeFinalized)
                <form method="POST"
                    action="{{ route('monthly-timekeeping.employees.ot-approval', ['cutoff' => $cutoff->id, 'employee' => $employee->id]) }}">
                    @csrf
            @endif

            <div class="card-body p-0">
                <div class="table-responsive scrollbar">
                    <table class="table table-hover align-middle mb-0 monthly-attendance-table">
                        <thead>
                            <tr>
                                <th class="ps-3">Date</th>
                                <th>Schedule</th>
                                <th>Holiday</th>
                                <th class="text-center">Time In</th>
                                <th class="text-center">Time Out</th>
                                <th class="text-center border-start">Worked</th>
                                <th class="text-center">Late</th>
                                <th class="text-center">Early Out</th>
                                <th class="text-center">Computed OT</th>
                                <th class="text-center">Approved OT</th>
                                <th class="text-center">OT Approval</th>
                                <th class="text-center">Absent</th>
                                <th class="text-center">Actual</th>
                                <th class="text-center pe-3">Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                if (!function_exists('readableTime')) {
                                    function readableTime($time)
                                    {
                                        if (!$time || $time === '00:00') {
                                            return '-';
                                        }

                                        [$hours, $minutes] = explode(':', $time);
                                        $hours = (int) $hours;
                                        $minutes = (int) $minutes;

                                        if ($hours > 0 && $minutes > 0) {
                                            return $hours . ' hr ' . $minutes . ' mins';
                                        }

                                        if ($hours > 0) {
                                            return $hours . ' hr';
                                        }

                                        return $minutes . ' mins';
                                    }
                                }

                                $totalWorkedMinutes = 0;
                                $totalLateMinutes = 0;
                                $totalEarlyMinutes = 0;
                                $totalComputedOtMinutes = 0;
                                $totalApprovedOtMinutes = 0;
                                $totalAbsentMinutes = 0;
                            @endphp

                            @forelse($attendances as $attendance)
                                @php
                                    [$workedHour, $workedMin] = explode(':', $attendance->working_time);
                                    [$lateHour, $lateMin] = explode(':', $attendance->late_time);
                                    [$earlyHour, $earlyMin] = explode(':', $attendance->early_time);
                                    [$computedOtHour, $computedOtMin] = explode(
                                        ':',
                                        $attendance->computed_ot_time ?? '00:00',
                                    );
                                    [$approvedOtHour, $approvedOtMin] = explode(
                                        ':',
                                        $attendance->approved_ot_time ?? '00:00',
                                    );
                                    [$absentHour, $absentMin] = explode(':', $attendance->absent_time);

                                    $workedTotal = (int) $workedHour * 60 + (int) $workedMin;
                                    $lateTotal = (int) $lateHour * 60 + (int) $lateMin;
                                    $earlyTotal = (int) $earlyHour * 60 + (int) $earlyMin;
                                    $computedOtTotal = (int) $computedOtHour * 60 + (int) $computedOtMin;
                                    $approvedOtTotal = (int) $approvedOtHour * 60 + (int) $approvedOtMin;
                                    $absentTotal = (int) $absentHour * 60 + (int) $absentMin;

                                    $totalWorkedMinutes += $workedTotal;
                                    $totalLateMinutes += $lateTotal;
                                    $totalEarlyMinutes += $earlyTotal;
                                    $totalComputedOtMinutes += $computedOtTotal;
                                    $totalApprovedOtMinutes += $approvedOtTotal;
                                    $totalAbsentMinutes += $absentTotal;
                                @endphp

                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-primary fs-0">
                                            {{ $attendance->display_date }}
                                        </div>
                                        <small class="text-600">{{ $attendance->display_day }}</small>
                                    </td>

                                    <td class="text-nowrap">
                                        @if ($attendance->is_rest_day)
                                            <span class="badge rounded-pill badge-subtle-info text-info px-3">
                                                Rest Day
                                            </span>
                                        @else
                                            <span class="text-700">
                                                {{ \Carbon\Carbon::parse($employee->schedule_time_in)->format('h:i A') }}
                                                -
                                                {{ \Carbon\Carbon::parse($employee->schedule_time_out)->format('h:i A') }}
                                            </span>
                                        @endif
                                    </td>

                                    <td style="min-width: 180px;">
                                        @if ($attendance->is_holiday)
                                            @if ($attendance->holiday_type === 'regular')
                                                <div class="fw-bold text-danger">
                                                    <span class="far fa-calendar-alt me-1"></span>
                                                    Regular Holiday
                                                </div>
                                            @else
                                                <div class="fw-bold text-warning">
                                                    <span class="far fa-calendar-alt me-1"></span>
                                                    Special Holiday
                                                </div>
                                            @endif

                                            <small class="text-600">{{ $attendance->holiday_name }}</small>
                                        @else
                                            <span class="text-600">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if ($attendance->time_in)
                                            <span class="fw-bold text-success">
                                                {{ \Carbon\Carbon::parse($attendance->time_in)->format('h:i A') }}
                                            </span>
                                        @else
                                            <span class="text-600">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if ($attendance->time_out)
                                            <span class="fw-bold text-primary">
                                                {{ \Carbon\Carbon::parse($attendance->time_out)->format('h:i A') }}
                                            </span>
                                        @else
                                            <span class="text-600">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center border-start text-700">
                                        {{ readableTime($attendance->working_time) }}
                                    </td>

                                    <td class="text-center">
                                        @if ($attendance->late_time !== '00:00')
                                            <span class="fw-bold text-danger">
                                                {{ readableTime($attendance->late_time) }}
                                            </span>
                                        @else
                                            <span class="text-600">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if ($attendance->early_time !== '00:00')
                                            <span class="fw-bold text-warning">
                                                {{ readableTime($attendance->early_time) }}
                                            </span>
                                        @else
                                            <span class="text-600">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if (($attendance->computed_ot_time ?? '00:00') !== '00:00')
                                            <span class="fw-bold text-success">
                                                {{ readableTime($attendance->computed_ot_time) }}
                                            </span>
                                        @else
                                            <span class="text-600">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if (($attendance->approved_ot_time ?? '00:00') !== '00:00')
                                            <span class="fw-bold text-primary">
                                                {{ readableTime($attendance->approved_ot_time) }}
                                            </span>
                                        @else
                                            <span class="text-600">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center" style="min-width: 180px;">
                                        @if (($attendance->computed_ot_minutes ?? 0) > 0)
                                            @if ($employeeFinalized)
                                                @if ($attendance->ot_status === 'approved')
                                                    <span
                                                        class="badge rounded-pill badge-subtle-success text-success px-3 py-2">
                                                        Approved
                                                    </span>
                                                @elseif ($attendance->ot_status === 'rejected')
                                                    <span
                                                        class="badge rounded-pill badge-subtle-danger text-danger px-3 py-2">
                                                        Rejected
                                                    </span>
                                                @else
                                                    <span
                                                        class="badge rounded-pill badge-subtle-warning text-warning px-3 py-2">
                                                        Pending
                                                    </span>
                                                @endif
                                            @else
                                                <input type="hidden"
                                                    name="records[{{ $loop->index }}][attendance_date]"
                                                    value="{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d') }}">

                                                <input type="hidden"
                                                    name="records[{{ $loop->index }}][computed_ot_minutes]"
                                                    value="{{ $attendance->computed_ot_minutes }}">

                                                <select name="records[{{ $loop->index }}][ot_status]"
                                                    class="form-select form-select-sm ot-status-select"
                                                    data-computed-ot="{{ $attendance->computed_ot_minutes }}">
                                                    <option value="pending" @selected($attendance->ot_status === 'pending')>
                                                        Pending
                                                    </option>
                                                    <option value="approved" @selected($attendance->ot_status === 'approved')>
                                                        Approved
                                                    </option>
                                                    <option value="rejected" @selected($attendance->ot_status === 'rejected')>
                                                        Rejected
                                                    </option>
                                                </select>

                                                <input type="hidden" class="approved-ot-hidden"
                                                    name="records[{{ $loop->index }}][approved_ot_minutes]"
                                                    value="{{ $attendance->ot_status === 'approved' ? $attendance->computed_ot_minutes : 0 }}">
                                            @endif
                                        @else
                                            <span class="text-600">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if ($attendance->absent_time !== '00:00')
                                            <span class="fw-bold text-danger">
                                                {{ readableTime($attendance->absent_time) }}
                                            </span>
                                        @else
                                            <span class="text-600">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center text-700">
                                        {{ readableTime($attendance->actual_working_time) }}
                                    </td>

                                    <td class="text-center pe-3">
                                        @if ($attendance->is_rest_day)
                                            <span
                                                class="badge rounded-pill badge-subtle-secondary text-secondary px-3 py-2">
                                                Rest Day
                                            </span>
                                        @elseif($attendance->is_holiday && $attendance->holiday_type === 'regular')
                                            <span class="badge rounded-pill badge-subtle-danger text-danger px-3 py-2">
                                                {{ $attendance->exception }}
                                            </span>
                                        @elseif($attendance->is_holiday && $attendance->holiday_type === 'special_non_working')
                                            <span class="badge rounded-pill badge-subtle-warning text-warning px-3 py-2">
                                                {{ $attendance->exception }}
                                            </span>
                                        @elseif($attendance->exception === 'Absent')
                                            <span class="badge rounded-pill badge-subtle-danger text-danger px-3 py-2">
                                                Absent
                                            </span>
                                        @elseif($attendance->exception === 'Late')
                                            <span class="badge rounded-pill badge-subtle-warning text-warning px-3 py-2">
                                                Late
                                            </span>
                                        @elseif($attendance->exception === 'Early Out')
                                            <span class="badge rounded-pill badge-subtle-warning text-warning px-3 py-2">
                                                Early Out
                                            </span>
                                        @elseif($attendance->exception === 'With OT')
                                            <span class="badge rounded-pill badge-subtle-success text-success px-3 py-2">
                                                With OT
                                            </span>
                                        @else
                                            <span class="badge rounded-pill badge-subtle-success text-success px-3 py-2">
                                                Complete
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center py-5">
                                        <h6 class="mb-1">No attendance records found</h6>
                                        <p class="text-muted mb-0">No attendance record for selected month.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        @if ($attendances->count() > 0)
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end pe-3">Total Summary</th>

                                    <th class="text-center text-primary border-start">
                                        {{ readableTime(sprintf('%02d:%02d', floor($totalWorkedMinutes / 60), $totalWorkedMinutes % 60)) }}
                                    </th>

                                    <th class="text-center text-danger">
                                        {{ readableTime(sprintf('%02d:%02d', floor($totalLateMinutes / 60), $totalLateMinutes % 60)) }}
                                    </th>

                                    <th class="text-center text-warning">
                                        {{ readableTime(sprintf('%02d:%02d', floor($totalEarlyMinutes / 60), $totalEarlyMinutes % 60)) }}
                                    </th>

                                    <th class="text-center text-success">
                                        {{ readableTime(sprintf('%02d:%02d', floor($totalComputedOtMinutes / 60), $totalComputedOtMinutes % 60)) }}
                                    </th>

                                    <th class="text-center text-primary">
                                        {{ readableTime(sprintf('%02d:%02d', floor($totalApprovedOtMinutes / 60), $totalApprovedOtMinutes % 60)) }}
                                    </th>

                                    <th></th>

                                    <th class="text-center text-danger">
                                        {{ readableTime(sprintf('%02d:%02d', floor($totalAbsentMinutes / 60), $totalAbsentMinutes % 60)) }}
                                    </th>

                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            @if (!$employeeFinalized)
                <div class="card-footer bg-light text-end">
                    <button type="submit" class="btn btn-falcon-primary">
                        <span class="fas fa-save me-1"></span>
                        Save OT Approval
                    </button>
                </div>
                </form>
            @endif
        </div>

    </div>

    <style>
        .monthly-attendance-table {
            font-size: 0.92rem;
        }

        .monthly-attendance-table thead th {
            background-color: #f8fafd;
            color: #1f2937;
            font-weight: 700;
            border-bottom: 1px solid #d8e2ef;
            padding-top: 0.8rem;
            padding-bottom: 0.8rem;
            white-space: nowrap;
        }

        .monthly-attendance-table tbody td {
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
            border-bottom: 1px solid #edf2f9;
            vertical-align: middle;
            white-space: nowrap;
        }

        .monthly-attendance-table tbody tr:hover {
            background-color: #f9fbfd;
        }

        .monthly-attendance-table tfoot th {
            background-color: #f8fafd;
            border-top: 1px solid #d8e2ef;
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .monthly-attendance-table .badge {
            font-weight: 700;
        }

        .card {
            border-radius: 0.45rem;
        }

        .avatar-name {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .avatar-name span {
            line-height: 1 !important;
        }

        .ot-status-select {
            min-width: 140px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.ot-status-select').forEach(function(select) {
                select.addEventListener('change', function() {
                    const computedOt = this.dataset.computedOt || 0;
                    const hiddenInput = this.closest('td').querySelector('.approved-ot-hidden');

                    if (this.value === 'approved') {
                        hiddenInput.value = computedOt;
                    } else {
                        hiddenInput.value = 0;
                    }
                });
            });
        });
    </script>
@endsection
