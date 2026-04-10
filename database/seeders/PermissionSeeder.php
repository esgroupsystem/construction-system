<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'payroll.view',
            'payroll.create',
            'payroll.update',
            'payroll.delete',

            'attendance-summary.view',
            'attendance-summary.create',
            'attendance-summary.update',
            'attendance-summary.delete',

            'adjustments.view',
            'adjustments.create',
            'adjustments.update',
            'adjustments.delete',

            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',

            'face-registration.view',
            'face-registration.create',
            'face-registration.update',
            'face-registration.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
