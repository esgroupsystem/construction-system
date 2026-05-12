<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\MonthlyCutoff;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
            ->orderBy('full_name')
            ->paginate(10);

        return view('monthly-timekeeping.show', compact('cutoff', 'employees'));
    }

    public function finalizeCutoff(MonthlyCutoff $cutoff)
    {
        if ($cutoff->status === 'finalized') {
            return back()->with('error', 'This monthly cutoff is already finalized.');
        }

        $cutoff->update([
            'status' => 'finalized',
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

        $dateFrom = Carbon::parse($cutoff->date_from);
        $dateTo = Carbon::parse($cutoff->date_to);

        $period = CarbonPeriod::create($dateFrom, $dateTo);

        $attendances = collect($period)->map(function ($date) use ($employee) {
            $attendance = $employee->attendances()
                ->whereDate('date', $date->format('Y-m-d'))
                ->first();

            $isRestDay = in_array($date->format('D'), ['Sat', 'Sun']);

            return (object) [
                'display_date' => $date->format('M d'),
                'display_day' => $date->format('D'),
                'is_rest_day' => $isRestDay,
                'time_in' => $attendance?->time_in,
                'time_out' => $attendance?->time_out,
                'working_time' => $attendance?->working_time ?? '00:00',
                'late_time' => $attendance?->late_time ?? '00:00',
                'early_time' => $attendance?->early_time ?? '00:00',
                'ot_time' => $attendance?->ot_time ?? '00:00',
                'absent_time' => $attendance?->absent_time ?? ($isRestDay ? '00:00' : '09:00'),
                'actual_working_time' => $attendance?->actual_working_time ?? '00:00',
                'exception' => $attendance?->exception ?? ($isRestDay ? 'Rest Day' : 'Absent'),
            ];
        });

        return view('monthly-timekeeping.cutoff', compact(
            'cutoff',
            'employee',
            'attendances',
            'dateFrom',
            'dateTo'
        ));
    }
}
