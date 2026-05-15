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
                                <h3 class="mb-1 fw-bold text-900">Weekly Timekeeping Details</h3>
                                <div class="text-600 fs-0">
                                    {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }}
                                    -
                                    {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-auto">
                        <a href="{{ route('weekly-timekeeping.cutoffs.show', $cutoff->id) }}"
                            class="btn btn-falcon-default btn-sm px-3">
                            <span class="fas fa-arrow-left me-2"></span>
                            Back
                        </a>
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
                                <h5 class="mb-1 fw-bold text-900">Attendance Report</h5>
                                <small class="text-600">
                                    Daily attendance summary with worked hours, late, early out, OT, and absences
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

            <div class="card-body p-0">
                <div class="table-responsive scrollbar">
                    <table class="table table-hover align-middle mb-0 weekly-attendance-table">
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
                                <th class="text-center">OT</th>
                                <th class="text-center">Absent</th>
                                <th class="text-center">Actual</th>
                                <th class="text-center pe-3">Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
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

                                $totalWorkedMinutes = 0;
                                $totalLateMinutes = 0;
                                $totalEarlyMinutes = 0;
                                $totalOtMinutes = 0;
                                $totalAbsentMinutes = 0;
                            @endphp

                            @forelse($attendances as $attendance)
                                @php
                                    [$workedHour, $workedMin] = explode(':', $attendance->working_time);
                                    [$lateHour, $lateMin] = explode(':', $attendance->late_time);
                                    [$earlyHour, $earlyMin] = explode(':', $attendance->early_time);
                                    [$otHour, $otMin] = explode(':', $attendance->ot_time);
                                    [$absentHour, $absentMin] = explode(':', $attendance->absent_time);

                                    $workedTotal = (int) $workedHour * 60 + (int) $workedMin;
                                    $lateTotal = (int) $lateHour * 60 + (int) $lateMin;
                                    $earlyTotal = (int) $earlyHour * 60 + (int) $earlyMin;
                                    $otTotal = (int) $otHour * 60 + (int) $otMin;
                                    $absentTotal = (int) $absentHour * 60 + (int) $absentMin;

                                    $totalWorkedMinutes += $workedTotal;
                                    $totalLateMinutes += $lateTotal;
                                    $totalEarlyMinutes += $earlyTotal;
                                    $totalOtMinutes += $otTotal;
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
                                        @if ($attendance->ot_time !== '00:00')
                                            <span class="fw-bold text-success">
                                                {{ readableTime($attendance->ot_time) }}
                                            </span>
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
                                    <td colspan="12" class="text-center py-5">
                                        <h6 class="mb-1">No attendance records found</h6>
                                        <p class="text-muted mb-0">No attendance record for selected date range.</p>
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
                                        {{ readableTime(sprintf('%02d:%02d', floor($totalOtMinutes / 60), $totalOtMinutes % 60)) }}
                                    </th>

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
        </div>

        {{-- Legend --}}
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <div class="row g-3 align-items-center">

                    <div class="col-md-3 border-end">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-calendar-alt text-danger fs-2 me-3"></span>
                            <div>
                                <h6 class="mb-0 fw-bold">Regular Holiday</h6>
                                <small class="text-600">Paid holiday work and OT</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 border-end">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-calendar-day text-warning fs-2 me-3"></span>
                            <div>
                                <h6 class="mb-0 fw-bold">Special Holiday</h6>
                                <small class="text-600">Special non-working day</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 border-end">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-user-clock text-info fs-2 me-3"></span>
                            <div>
                                <h6 class="mb-0 fw-bold">Rest Day</h6>
                                <small class="text-600">Scheduled day off</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-info-circle text-primary fs-2 me-3"></span>
                            <div>
                                <h6 class="mb-0 fw-bold">Note</h6>
                                <small class="text-600">Holidays without logs are not marked absent.</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <style>
        .weekly-attendance-table {
            font-size: 0.92rem;
        }

        .weekly-attendance-table thead th {
            background-color: #f8fafd;
            color: #1f2937;
            font-weight: 700;
            border-bottom: 1px solid #d8e2ef;
            padding-top: 0.8rem;
            padding-bottom: 0.8rem;
            white-space: nowrap;
        }

        .weekly-attendance-table tbody td {
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
            border-bottom: 1px solid #edf2f9;
            vertical-align: middle;
            white-space: nowrap;
        }

        .weekly-attendance-table tbody tr:hover {
            background-color: #f9fbfd;
        }

        .weekly-attendance-table tfoot th {
            background-color: #f8fafd;
            border-top: 1px solid #d8e2ef;
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .weekly-attendance-table .badge {
            font-weight: 700;
        }

        .card {
            border-radius: 0.45rem;
        }
    </style>
@endsection
