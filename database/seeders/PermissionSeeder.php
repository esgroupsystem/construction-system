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

            /*----------------------
             -------- EMPLOYEES --------
            ------------------------*/
            'employees.view',
            'employees.create',
            'employees.update',
            'employees.delete',

            /*----------------------
             -------- FACE REGISTRATION --------
            ------------------------*/
            'face-registration.view',
            'face-registration.update', // you only used update in routes

            /*----------------------
             -------- ATTENDANCE (EMPLOYEE SIDE) --------
            ------------------------*/
            'employee-dashboard.view',
            'attendance.time-in',
            'attendance.time-out',

            /*----------------------
             -------- ATTENDANCE LOGS (ADMIN) --------
            ------------------------*/
            'attendance-logs.view',

            /*----------------------
             -------- USERS --------
            ------------------------*/
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            /*----------------------
             -------- ROLES --------
            ------------------------*/
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',

            /*----------------------
             -------- PAYROLL --------
            ------------------------*/
            'payroll.view',
            'payroll.create',
            'payroll.update',
            'payroll.delete',

            /*----------------------
             -------- ATTENDANCE SUMMARY --------
            ------------------------*/
            'attendance-summary.view',
            'attendance-summary.create',
            'attendance-summary.update',
            'attendance-summary.delete',

            /*----------------------
             -------- ADJUSTMENTS --------
            ------------------------*/
            'adjustments.view',
            'adjustments.create',
            'adjustments.update',
            'adjustments.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
