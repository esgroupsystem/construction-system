@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col">
                        <h4 class="mb-1">Weekly Timekeeping Details</h4>
                        <div class="text-muted">
                            {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }}
                            -
                            {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                        </div>
                    </div>

                    <div class="col-auto">
                        <a href="{{ route('weekly-timekeeping.cutoffs.show', $cutoff->id) }}"
                            class="btn btn-falcon-default btn-sm">
                            Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Employee</small>
                        <h6 class="mb-1 mt-1">{{ $employee->full_name }}</h6>
                        <span class="badge bg-primary">{{ $employee->employee_no }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Department</small>
                        <h6 class="mb-1 mt-1">{{ $employee->department }}</h6>
                        <span class="text-muted">{{ $employee->position }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Location</small>
                        <h6 class="mb-1 mt-1">{{ $employee->location }}</h6>
                        <span class="badge bg-info">{{ ucfirst($employee->payroll_type) }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Schedule</small>
                        <h6 class="mb-1 mt-1">
                            {{ \Carbon\Carbon::parse($employee->schedule_time_in)->format('h:i A') }}
                            -
                            {{ \Carbon\Carbon::parse($employee->schedule_time_out)->format('h:i A') }}
                        </h6>
                        <span class="text-muted">{{ $attendances->count() }} day(s)</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance Report --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-bold">Attendance Report</h6>
                        <small class="text-muted">
                            Daily attendance summary with worked hours, late, early out, OT, and absences
                        </small>
                    </div>

                    <span class="badge rounded-pill badge-subtle-primary px-3 py-2">
                        {{ $attendances->count() }} day(s)
                    </span>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive scrollbar">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="bg-200 text-900">
                            <tr>
                                <th class="ps-3">Date</th>
                                <th>Schedule</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th class="text-center">Worked</th>
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

                                <tr class="{{ $attendance->is_rest_day ? 'bg-light' : '' }}">
                                    <td class="ps-3">
                                        <div class="fw-semibold text-primary">
                                            {{ $attendance->display_date }}
                                        </div>
                                        <small class="text-muted">{{ $attendance->display_day }}</small>
                                    </td>

                                    <td>
                                        @if ($attendance->is_rest_day)
                                            <span class="badge rounded-pill badge-subtle-secondary text-secondary">
                                                Rest Day
                                            </span>
                                        @else
                                            <span class="text-muted">
                                                {{ \Carbon\Carbon::parse($employee->schedule_time_in)->format('h:i A') }}
                                                -
                                                {{ \Carbon\Carbon::parse($employee->schedule_time_out)->format('h:i A') }}
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($attendance->time_in)
                                            <span class="badge rounded-pill badge-subtle-success text-success">
                                                {{ \Carbon\Carbon::parse($attendance->time_in)->format('h:i A') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($attendance->time_out)
                                            <span class="badge rounded-pill badge-subtle-primary text-primary">
                                                {{ \Carbon\Carbon::parse($attendance->time_out)->format('h:i A') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center fw-semibold">
                                        {{ readableTime($attendance->working_time) }}
                                    </td>

                                    <td class="text-center">
                                        @if ($attendance->late_time !== '00:00')
                                            <span class="fw-semibold text-danger">
                                                {{ readableTime($attendance->late_time) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if ($attendance->early_time !== '00:00')
                                            <span class="fw-semibold text-warning">
                                                {{ readableTime($attendance->early_time) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if ($attendance->ot_time !== '00:00')
                                            <span class="fw-semibold text-success">
                                                {{ readableTime($attendance->ot_time) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if ($attendance->absent_time !== '00:00')
                                            <span class="fw-semibold text-danger">
                                                {{ readableTime($attendance->absent_time) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <td class="text-center fw-semibold">
                                        {{ readableTime($attendance->actual_working_time) }}
                                    </td>

                                    <td class="text-center pe-3">
                                        @if ($attendance->is_rest_day)
                                            <span class="badge rounded-pill badge-subtle-secondary text-secondary px-3">
                                                Rest Day
                                            </span>
                                        @elseif($attendance->exception === 'Absent')
                                            <span class="badge rounded-pill badge-subtle-danger text-danger px-3">
                                                Absent
                                            </span>
                                        @elseif($attendance->exception === 'Late')
                                            <span class="badge rounded-pill badge-subtle-warning text-warning px-3">
                                                Late
                                            </span>
                                        @elseif($attendance->exception === 'Early Out')
                                            <span class="badge rounded-pill badge-subtle-warning text-warning px-3">
                                                Early Out
                                            </span>
                                        @elseif($attendance->exception === 'With OT')
                                            <span class="badge rounded-pill badge-subtle-success text-success px-3">
                                                With OT
                                            </span>
                                        @else
                                            <span class="badge rounded-pill badge-subtle-success text-success px-3">
                                                Complete
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5">
                                        <h6 class="mb-1">No attendance records found</h6>
                                        <p class="text-muted mb-0">No attendance record for selected date range.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        @if ($attendances->count() > 0)
                            <tfoot class="bg-light border-top">
                                <tr>
                                    <th colspan="4" class="text-end pe-3">Total Summary</th>

                                    <th class="text-center text-primary">
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

    </div>
@endsection
