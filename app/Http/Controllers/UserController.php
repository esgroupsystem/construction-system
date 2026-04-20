<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with(['roles', 'employee'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        $employees = Employee::query()
            ->whereDoesntHave('user')
            ->orderBy('full_name')
            ->get();

        return view('users.create', compact('roles', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['nullable', 'exists:roles,name'],
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        $user = User::create([
            'employee_id' => $employee->id,
            'name' => $employee->full_name,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (! empty($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully and linked to employee record.');
    }

    public function edit(User $user)
    {
        $user->load(['roles', 'employee']);

        $roles = Role::orderBy('name')->get();
        $selectedRole = $user->roles->first()?->name;

        return view('users.edit', compact('user', 'roles', 'selectedRole'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['nullable', 'exists:roles,name'],
        ]);

        $user->syncRoles(
            ! empty($validated['role']) ? [$validated['role']] : []
        );

        return redirect()
            ->route('users.index')
            ->with('success', 'User role updated successfully.');
    }
}
