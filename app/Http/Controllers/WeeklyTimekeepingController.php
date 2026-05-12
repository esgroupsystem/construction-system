<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\WeeklyCutoff;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WeeklyTimekeepingController extends Controller
{
    public function index()
    {
        $cutoffs = WeeklyCutoff::query()
            ->latest()
            ->paginate(10);

        return view('weekly-timekeeping.index', compact('cutoffs'));
    }

    public function storeCutoff(Request $request)
    {
        $request->validate([
            'cutoff_name' => ['nullable', 'string', 'max:255'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = Carbon::parse($request->date_from)->format('Y-m-d');
        $dateTo = Carbon::parse($request->date_to)->format('Y-m-d');

        $existingCutoff = WeeklyCutoff::query()
            ->where(function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('date_from', [$dateFrom, $dateTo])
                    ->orWhereBetween('date_to', [$dateFrom, $dateTo])
                    ->orWhere(function ($query) use ($dateFrom, $dateTo) {
                        $query->where('date_from', '<=', $dateFrom)
                            ->where('date_to', '>=', $dateTo);
                    });
            })
            ->first();

        if ($existingCutoff) {
            return back()
                ->withInput()
                ->with('error', 'This date range is already used by another cutoff. Please choose a different date.');
        }

        WeeklyCutoff::create([
            'cutoff_name' => $request->cutoff_name,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'status' => 'open',
        ]);

        return redirect()
            ->route('weekly-timekeeping.index')
            ->with('success', 'Weekly cutoff created successfully.');
    }

    public function showCutoff(WeeklyCutoff $cutoff)
    {
        $employees = Employee::query()
            ->where('payroll_type', 'weekly')
            ->where('is_active', 1)
            ->orderBy('full_name')
            ->paginate(10)
            ->withQueryString();

        return view('weekly-timekeeping.cutoff', compact('cutoff', 'employees'));
    }

    public function showEmployee(WeeklyCutoff $cutoff, Employee $employee)
    {
        $dateFrom = $cutoff->date_from;
        $dateTo = $cutoff->date_to;

        $attendanceRecords = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->attendance_date)->format('Y-m-d');
            });

        $dayOffs = $employee->day_offs;

        if (is_string($dayOffs)) {
            $decoded = json_decode($dayOffs, true);

            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }

            $dayOffs = $decoded;
        }

        if (! is_array($dayOffs)) {
            $dayOffs = [];
        }

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

            $isRestDay = in_array($dayName, $dayOffs);

            $scheduleIn = Carbon::parse($dateKey.' '.$employee->schedule_time_in);
            $scheduleOut = Carbon::parse($dateKey.' '.$employee->schedule_time_out);

            $timeIn = $attendance->time_in ? Carbon::parse($attendance->time_in) : null;
            $timeOut = $attendance->time_out ? Carbon::parse($attendance->time_out) : null;

            $workingMinutes = ($timeIn && $timeOut) ? $timeIn->diffInMinutes($timeOut) : 0;
            $dutyMinutes = $scheduleIn->diffInMinutes($scheduleOut);

            $lateMinutes = (! $isRestDay && $timeIn && $timeIn->gt($scheduleIn))
                ? $scheduleIn->diffInMinutes($timeIn)
                : 0;

            $earlyMinutes = (! $isRestDay && $timeOut && $timeOut->lt($scheduleOut))
                ? $timeOut->diffInMinutes($scheduleOut)
                : 0;

            $otMinutes = ($timeOut && $timeOut->gt($scheduleOut))
                ? $scheduleOut->diffInMinutes($timeOut)
                : 0;

            $attendance->is_rest_day = $isRestDay;
            $attendance->display_date = $currentDate->format('M j');
            $attendance->display_day = $currentDate->format('D');
            $attendance->working_time = $this->formatMinutes($workingMinutes);
            $attendance->late_time = $this->formatMinutes($lateMinutes);
            $attendance->early_time = $this->formatMinutes($earlyMinutes);
            $attendance->ot_time = $this->formatMinutes($otMinutes);
            $attendance->absent_time = (! $isRestDay && $workingMinutes == 0)
                ? $this->formatMinutes($dutyMinutes)
                : '00:00';
            $attendance->actual_working_time = $this->formatMinutes($workingMinutes);

            if ($isRestDay) {
                $attendance->exception = 'Rest Day';
            } elseif ($workingMinutes == 0) {
                $attendance->exception = 'Absent';
            } elseif ($lateMinutes > 0) {
                $attendance->exception = 'Late';
            } elseif ($earlyMinutes > 0) {
                $attendance->exception = 'Early Out';
            } elseif ($otMinutes > 0) {
                $attendance->exception = 'With OT';
            } else {
                $attendance->exception = 'Complete';
            }

            $attendances->push($attendance);

            $currentDate->addDay();
        }

        $attendances = $attendances->sortBy('attendance_date')->values();

        return view('weekly-timekeeping.show', compact(
            'cutoff',
            'employee',
            'attendances',
            'dateFrom',
            'dateTo'
        ));
    }

    public function finalize(WeeklyCutoff $cutoff)
    {
        $cutoff->update([
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);

        return back()->with('success', 'Cutoff finalized successfully.');
    }

    private function formatMinutes($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }

    public function destroy($id)
    {
        $cutoff = WeeklyCutoff::findOrFail($id);

        if ($cutoff->status === 'finalized') {
            return back()->with('error', 'Finalized cutoff cannot be deleted.');
        }

        $cutoff->delete();

        return redirect()
            ->route('weekly-timekeeping.index')
            ->with('success', 'Weekly cutoff deleted successfully.');
    }
}
