<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $employees = Employee::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_no', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('employees.index', compact('employees', 'search'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_no' => ['required', 'string', 'max:50', 'unique:employees,employee_no'],
            'full_name' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'rate_salary' => ['nullable', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'schedule_time_in' => ['nullable', 'date_format:H:i'],
            'schedule_time_out' => ['nullable', 'date_format:H:i', 'after:schedule_time_in'],
            'day_offs' => ['nullable', 'array'],
            'day_offs.*' => ['in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        try {
            $photoPath = null;

            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('employees', 'public');
            }

            Employee::create([
                'employee_no' => $validated['employee_no'],
                'full_name' => $validated['full_name'],
                'department' => $validated['department'] ?? null,
                'position' => $validated['position'] ?? null,
                'rate_salary' => $validated['rate_salary'] ?? null,
                'location' => $validated['location'] ?? null,
                'schedule_time_in' => $validated['schedule_time_in'] ?? null,
                'schedule_time_out' => $validated['schedule_time_out'] ?? null,
                'day_offs' => $validated['day_offs'] ?? [],
                'photo_path' => $photoPath,
                'is_active' => $request->boolean('is_active'),
                'face_registered_at' => null,
            ]);

            return redirect()
                ->route('employees.index')
                ->with('success', 'Employee added successfully.');
        } catch (\Throwable $e) {
            Log::error('Employee store failed', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to add employee.');
        }
    }

    public function show(Employee $employee)
    {
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_no' => ['required', 'string', 'max:50', 'unique:employees,employee_no,'.$employee->id],
            'full_name' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'rate_salary' => ['nullable', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'schedule_time_in' => ['nullable', 'date_format:H:i'],
            'schedule_time_out' => ['nullable', 'date_format:H:i', 'after:schedule_time_in'],
            'day_offs' => ['nullable', 'array'],
            'day_offs.*' => ['in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        try {
            $photoPath = $employee->photo_path;

            if ($request->hasFile('photo')) {
                if ($employee->photo_path && Storage::disk('public')->exists($employee->photo_path)) {
                    Storage::disk('public')->delete($employee->photo_path);
                }

                $photoPath = $request->file('photo')->store('employees', 'public');
            }

            $employee->update([
                'employee_no' => $validated['employee_no'],
                'full_name' => $validated['full_name'],
                'department' => $validated['department'] ?? null,
                'position' => $validated['position'] ?? null,
                'rate_salary' => $validated['rate_salary'] ?? null,
                'location' => $validated['location'] ?? null,
                'schedule_time_in' => $validated['schedule_time_in'] ?? null,
                'schedule_time_out' => $validated['schedule_time_out'] ?? null,
                'day_offs' => $validated['day_offs'] ?? [],
                'photo_path' => $photoPath,
                'is_active' => $request->boolean('is_active'),
            ]);

            return redirect()
                ->route('employees.index')
                ->with('success', 'Employee updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Employee update failed', [
                'employee_id' => $employee->id,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update employee.');
        }
    }

    public function destroy(Employee $employee)
    {
        try {
            if ($employee->photo_path && Storage::disk('public')->exists($employee->photo_path)) {
                Storage::disk('public')->delete($employee->photo_path);
            }

            $employee->delete();

            return redirect()
                ->route('employees.index')
                ->with('success', 'Employee deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Employee delete failed', [
                'employee_id' => $employee->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete employee.');
        }
    }
}
