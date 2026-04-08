<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $payrollStaff = Role::firstOrCreate(['name' => 'Payroll Staff']);
        $hrStaff = Role::firstOrCreate(['name' => 'HR Staff']);
        $userAdmin = Role::firstOrCreate(['name' => 'User Admin']);

        // Super Admin = all permissions
        $superAdmin->syncPermissions(Permission::pluck('name')->toArray());

        // Payroll Staff = view only
        $payrollStaff->syncPermissions([
            'payroll.view',
        ]);

        // Example HR Staff
        $hrStaff->syncPermissions([
            'attendance-summary.view',
            'attendance-summary.update',
            'adjustments.view',
            'adjustments.create',
            'adjustments.update',
        ]);

        // Example User Admin
        $userAdmin->syncPermissions([
            'users.view',
            'users.create',
            'users.update',
            'roles.view',
            'roles.create',
            'roles.update',
        ]);
    }
}
