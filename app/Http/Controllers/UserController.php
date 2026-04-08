<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->with('roles')
            ->latest()
            ->paginate(10);

        return view('users.index', compact('users'));
    }

    public function edit(User $user)
    {
        $user->load('roles');

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
