<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\MonthlyCutoff;
use App\Models\MonthlyTimekeepingRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonthlyTimekeepingController extends Controller
{
    public function index()
    {
        $cutoffs = MonthlyCutoff::latest()->paginate(10);

        return view('monthly-timekeeping.index', compact('cutoffs'));
    }

    public function storeCutoff(Request $request)
    {
        $request->validate([
            'cutoff_name' => ['nullable', 'string', 'max:255'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
        ]);

        $dateFrom = Carbon::createFromDate($request->year, $request->month, 1)->startOfMonth();
        $dateTo = Carbon::createFromDate($request->year, $request->month, 1)->endOfMonth();

        $existingCutoff = MonthlyCutoff::whereDate('date_from', $dateFrom->format('Y-m-d'))
            ->whereDate('date_to', $dateTo->format('Y-m-d'))
            ->first();

        if ($existingCutoff) {
            return back()
                ->withInput()
                ->with('error', 'This monthly cutoff already exists.');
        }

        MonthlyCutoff::create([
            'cutoff_name' => $request->cutoff_name ?: $dateFrom->format('F Y').' Payroll',
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'status' => 'open',
        ]);

        return redirect()
            ->route('monthly-timekeeping.index')
            ->with('success', 'Monthly cutoff created successfully.');
    }

    public function showCutoff(MonthlyCutoff $cutoff)
    {
        $employees = Employee::query()
            ->where('payroll_type', 'monthly')
            ->where('is_active', 1)
            ->orderBy('full_name')
            ->paginate(10)
            ->withQueryString();

        $monthlyEmployeeIds = Employee::query()
            ->where('payroll_type', 'monthly')
            ->where('is_active', 1)
            ->pluck('id')
            ->toArray();

        $finalizedEmployeeIds = MonthlyTimekeepingRecord::query()
            ->where('monthly_cutoff_id', $cutoff->id)
            ->where('is_finalized', true)
            ->whereIn('employee_id', $monthlyEmployeeIds)
            ->pluck('employee_id')
            ->unique()
            ->toArray();

        return view('monthly-timekeeping.show', compact(
            'cutoff',
            'employees',
            'finalizedEmployeeIds'
        ));
    }

    public function finalizeCutoff(MonthlyCutoff $cutoff)
    {
        if ($cutoff->status === 'finalized') {
            return back()->with('error', 'This monthly cutoff is already finalized.');
        }

        $monthlyEmployeeIds = Employee::query()
            ->where('payroll_type', 'monthly')
            ->where('is_active', 1)
            ->pluck('id')
            ->toArray();

        $finalizedEmployeeIds = MonthlyTimekeepingRecord::query()
            ->where('monthly_cutoff_id', $cutoff->id)
            ->where('is_finalized', true)
            ->whereIn('employee_id', $monthlyEmployeeIds)
            ->pluck('employee_id')
            ->unique()
            ->toArray();

        $pendingEmployeeCount = count(array_diff($monthlyEmployeeIds, $finalizedEmployeeIds));

        if ($pendingEmployeeCount > 0) {
            return back()->with(
                'error',
                'Cannot finalize monthly cutoff. There are still '.$pendingEmployeeCount.' employee(s) pending review.'
            );
        }

        $cutoff->update([
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);

        return back()->with('success', 'Monthly cutoff finalized successfully.');
    }

    public function destroyCutoff(MonthlyCutoff $cutoff)
    {
        if ($cutoff->status === 'finalized') {
            return back()->with('error', 'Finalized monthly cutoff cannot be deleted.');
        }

        $cutoff->delete();

        return redirect()
            ->route('monthly-timekeeping.index')
            ->with('success', 'Monthly cutoff deleted successfully.');
    }

    public function showEmployee(MonthlyCutoff $cutoff, Employee $employee)
    {
        if ($employee->payroll_type !== 'monthly') {
            abort(404);
        }

        $dateFrom = Carbon::parse($cutoff->date_from)->format('Y-m-d');
        $dateTo = Carbon::parse($cutoff->date_to)->format('Y-m-d');

        $attendanceRecords = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->get()
            ->keyBy(fn ($attendance) => Carbon::parse($attendance->attendance_date)->format('Y-m-d'));

        $holidays = Holiday::query()
            ->where('is_active', 1)
            ->whereBetween('holiday_date', [$dateFrom, $dateTo])
            ->get()
            ->keyBy(fn ($holiday) => Carbon::parse($holiday->holiday_date)->format('Y-m-d'));

        $timekeepingRecords = MonthlyTimekeepingRecord::query()
            ->where('monthly_cutoff_id', $cutoff->id)
            ->where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->get()
            ->keyBy(fn ($record) => Carbon::parse($record->attendance_date)->format('Y-m-d'));

        $employeeFinalized = MonthlyTimekeepingRecord::query()
            ->where('monthly_cutoff_id', $cutoff->id)
            ->where('employee_id', $employee->id)
            ->where('is_finalized', true)
            ->exists();

        $dayOffs = $this->normalizeDayOffs($employee->day_offs);

        $attendances = collect();

        $currentDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate = Carbon::parse($dateTo)->startOfDay();

        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayName = $currentDate->format('l');

            $attendance = $attendanceRecords->get($dateKey) ?? new Attendance([
                'attendance_date' => $dateKey,
                'time_in' => null,
                'time_out' => null,
            ]);

            $holiday = $holidays->get($dateKey);

            $isRestDay = in_array($dayName, $dayOffs);
            $isHoliday = ! is_null($holiday);

            $scheduleIn = Carbon::parse($dateKey.' '.$employee->schedule_time_in);
            $scheduleOut = Carbon::parse($dateKey.' '.$employee->schedule_time_out);

            $timeIn = $attendance->time_in ? Carbon::parse($attendance->time_in) : null;
            $timeOut = $attendance->time_out ? Carbon::parse($attendance->time_out) : null;

            $workingMinutes = ($timeIn && $timeOut) ? $timeIn->diffInMinutes($timeOut) : 0;
            $dutyMinutes = $scheduleIn->diffInMinutes($scheduleOut);

            $lateMinutes = (! $isRestDay && ! $isHoliday && $timeIn && $timeIn->gt($scheduleIn))
                ? $scheduleIn->diffInMinutes($timeIn)
                : 0;

            $earlyMinutes = (! $isRestDay && ! $isHoliday && $timeOut && $timeOut->lt($scheduleOut))
                ? $timeOut->diffInMinutes($scheduleOut)
                : 0;

            $computedOtMinutes = ($timeOut && $timeOut->gt($scheduleOut))
                ? $scheduleOut->diffInMinutes($timeOut)
                : 0;

            $timekeepingRecord = $timekeepingRecords->get($dateKey);

            $approvedOtMinutes = $timekeepingRecord?->approved_ot_minutes ?? 0;
            $otStatus = $timekeepingRecord?->ot_status ?? ($computedOtMinutes > 0 ? 'pending' : 'approved');
            $remarks = $timekeepingRecord?->remarks;

            $absentMinutes = (! $isRestDay && ! $isHoliday && $workingMinutes == 0)
                ? $dutyMinutes
                : 0;

            $attendance->is_rest_day = $isRestDay;
            $attendance->is_holiday = $isHoliday;
            $attendance->holiday_name = $holiday?->name;
            $attendance->holiday_type = $holiday?->type;

            $attendance->display_date = $currentDate->format('M j');
            $attendance->display_day = $currentDate->format('D');

            $attendance->working_time = $this->formatMinutes($workingMinutes);
            $attendance->late_time = $this->formatMinutes($lateMinutes);
            $attendance->early_time = $this->formatMinutes($earlyMinutes);
            $attendance->computed_ot_time = $this->formatMinutes($computedOtMinutes);
            $attendance->approved_ot_time = $this->formatMinutes($approvedOtMinutes);
            $attendance->ot_time = $this->formatMinutes($approvedOtMinutes);
            $attendance->absent_time = $this->formatMinutes($absentMinutes);
            $attendance->actual_working_time = $this->formatMinutes($workingMinutes);

            $attendance->computed_ot_minutes = $computedOtMinutes;
            $attendance->approved_ot_minutes = $approvedOtMinutes;
            $attendance->ot_status = $otStatus;
            $attendance->remarks = $remarks;

            if ($isRestDay) {
                $attendance->exception = 'Rest Day';
            } elseif ($isHoliday && $workingMinutes > 0) {
                $attendance->exception = $holiday->type === 'regular'
                    ? 'Worked Regular Holiday'
                    : 'Worked Special Holiday';
            } elseif ($isHoliday) {
                $attendance->exception = $holiday->type === 'regular'
                    ? 'Regular Holiday'
                    : 'Special Holiday';
            } elseif ($workingMinutes == 0) {
                $attendance->exception = 'Absent';
            } elseif ($lateMinutes > 0) {
                $attendance->exception = 'Late';
            } elseif ($earlyMinutes > 0) {
                $attendance->exception = 'Early Out';
            } elseif ($computedOtMinutes > 0) {
                $attendance->exception = 'With OT';
            } else {
                $attendance->exception = 'Complete';
            }

            $attendances->push($attendance);

            $currentDate->addDay();
        }

        $attendances = $attendances->sortBy('attendance_date')->values();

        return view('monthly-timekeeping.cutoff', compact(
            'cutoff',
            'employee',
            'attendances',
            'dateFrom',
            'dateTo',
            'employeeFinalized'
        ));
    }

    public function saveEmployeeOtApproval(Request $request, MonthlyCutoff $cutoff, Employee $employee)
    {
        $request->validate([
            'records' => ['nullable', 'array'],
            'records.*.attendance_date' => ['required', 'date'],
            'records.*.computed_ot_minutes' => ['nullable', 'integer', 'min:0'],
            'records.*.approved_ot_minutes' => ['nullable', 'integer', 'min:0'],
            'records.*.ot_status' => ['required', 'in:pending,approved,rejected'],
            'records.*.remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $alreadyFinalized = MonthlyTimekeepingRecord::query()
            ->where('monthly_cutoff_id', $cutoff->id)
            ->where('employee_id', $employee->id)
            ->where('is_finalized', true)
            ->exists();

        if ($alreadyFinalized) {
            return back()->with('error', 'This employee monthly timekeeping is already finalized.');
        }

        foreach ($request->records ?? [] as $record) {
            $computedOtMinutes = (int) ($record['computed_ot_minutes'] ?? 0);
            $otStatus = $record['ot_status'];

            $approvedOtMinutes = 0;

            if ($otStatus === 'approved') {
                $approvedOtMinutes = $computedOtMinutes;
            }

            MonthlyTimekeepingRecord::updateOrCreate(
                [
                    'monthly_cutoff_id' => $cutoff->id,
                    'employee_id' => $employee->id,
                    'attendance_date' => $record['attendance_date'],
                ],
                [
                    'computed_ot_minutes' => $computedOtMinutes,
                    'approved_ot_minutes' => $approvedOtMinutes,
                    'ot_status' => $otStatus,
                    'remarks' => $record['remarks'] ?? null,
                ]
            );
        }

        return back()->with('success', 'Monthly OT approval saved successfully.');
    }

    public function finalizeEmployee(MonthlyCutoff $cutoff, Employee $employee)
    {
        $dateFrom = Carbon::parse($cutoff->date_from)->format('Y-m-d');
        $dateTo = Carbon::parse($cutoff->date_to)->format('Y-m-d');

        $pendingOtExists = MonthlyTimekeepingRecord::query()
            ->where('monthly_cutoff_id', $cutoff->id)
            ->where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->where('computed_ot_minutes', '>', 0)
            ->where('ot_status', 'pending')
            ->exists();

        if ($pendingOtExists) {
            return back()->with('error', 'Cannot finalize. Please approve or reject all pending OT first.');
        }

        $currentDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);

        while ($currentDate <= $endDate) {
            MonthlyTimekeepingRecord::updateOrCreate(
                [
                    'monthly_cutoff_id' => $cutoff->id,
                    'employee_id' => $employee->id,
                    'attendance_date' => $currentDate->format('Y-m-d'),
                ],
                [
                    'is_finalized' => true,
                    'finalized_at' => now(),
                ]
            );

            $currentDate->addDay();
        }

        return redirect()
            ->route('monthly-timekeeping.cutoffs.show', $cutoff->id)
            ->with('success', 'Employee monthly timekeeping finalized successfully.');
    }

    public function unfinalizeEmployee(MonthlyCutoff $cutoff, Employee $employee)
    {
        MonthlyTimekeepingRecord::query()
            ->where('monthly_cutoff_id', $cutoff->id)
            ->where('employee_id', $employee->id)
            ->update([
                'is_finalized' => false,
                'finalized_at' => null,
            ]);

        return back()->with('success', 'Employee monthly timekeeping reopened successfully.');
    }

    private function normalizeDayOffs($dayOffs): array
    {
        if (is_string($dayOffs)) {
            $decoded = json_decode($dayOffs, true);

            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }

            $dayOffs = $decoded;
        }

        return is_array($dayOffs) ? $dayOffs : [];
    }

    private function formatMinutes($minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }
}
