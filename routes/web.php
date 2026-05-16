<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FaceRegistrationController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MonthlyTimekeepingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WeeklyTimekeepingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    /*----------------------
     -------- ATTENDANCE --------
    ------------------------*/
    Route::get('/employee/dashboard', [AttendanceController::class, 'index'])
        ->middleware('permission:employee-dashboard.view')
        ->name('employee.dashboard');

    Route::post('/attendance/time-in', [AttendanceController::class, 'timeIn'])
        ->middleware('permission:attendance.time-in')
        ->name('attendance.time-in');

    Route::post('/attendance/time-out', [AttendanceController::class, 'timeOut'])
        ->middleware('permission:attendance.time-out')
        ->name('attendance.time-out');

    /*----------------------
     -------- ATTENDANCE LOGS --------
    ------------------------*/
    Route::get('/attendance-logs', [AttendanceController::class, 'logs'])
        ->middleware('permission:attendance-logs.view')
        ->name('attendance-logs.index');

    /*----------------------
     -------- USERS --------
    ------------------------*/
    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:users.view')
        ->name('users.index');

    Route::get('/users/create', [UserController::class, 'create'])
        ->middleware('permission:users.create')
        ->name('users.create');

    Route::post('/users', [UserController::class, 'store'])
        ->middleware('permission:users.create')
        ->name('users.store');

    Route::get('/users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('permission:users.update')
        ->name('users.edit');

    Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.update')
        ->name('users.update');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete')
        ->name('users.destroy');

    /*----------------------
     -------- ROLES --------
    ------------------------*/
    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view')
        ->name('roles.index');

    Route::get('/roles/create', [RoleController::class, 'create'])
        ->middleware('permission:roles.create')
        ->name('roles.create');

    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('permission:roles.create')
        ->name('roles.store');

    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware('permission:roles.update')
        ->name('roles.edit');

    Route::match(['put', 'patch'], '/roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.update')
        ->name('roles.update');

    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.delete')
        ->name('roles.destroy');

    /*----------------------
     -------- EMPLOYEES --------
    ------------------------*/
    Route::get('/employees', [EmployeeController::class, 'index'])
        ->middleware('permission:employees.view')
        ->name('employees.index');

    Route::get('/employees/create', [EmployeeController::class, 'create'])
        ->middleware('permission:employees.create')
        ->name('employees.create');

    Route::post('/employees', [EmployeeController::class, 'store'])
        ->middleware('permission:employees.create')
        ->name('employees.store');

    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])
        ->middleware('permission:employees.view')
        ->name('employees.show');

    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])
        ->middleware('permission:employees.update')
        ->name('employees.edit');

    Route::match(['put', 'patch'], '/employees/{employee}', [EmployeeController::class, 'update'])
        ->middleware('permission:employees.update')
        ->name('employees.update');

    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])
        ->middleware('permission:employees.delete')
        ->name('employees.destroy');

    /*----------------------
     -------- FACE REGISTRATION --------
    ------------------------*/
    Route::get('/face-registration', [FaceRegistrationController::class, 'index'])
        ->middleware('permission:face-registration.view')
        ->name('face-registration.index');

    Route::get('/face-registration/{employee}', [FaceRegistrationController::class, 'show'])
        ->middleware('permission:face-registration.update')
        ->name('face-registration.show');

    Route::post('/face-registration/{employee}', [FaceRegistrationController::class, 'store'])
        ->middleware('permission:face-registration.update')
        ->name('face-registration.store');

    Route::put('/face-registration/{employee}/{sample}', [FaceRegistrationController::class, 'update'])
        ->middleware('permission:face-registration.update')
        ->name('face-registration.update');

    Route::delete('/face-registration/{employee}/{sample}', [FaceRegistrationController::class, 'destroy'])
        ->middleware('permission:face-registration.update')
        ->name('face-registration.destroy');

    /*----------------------
     -------- Weekly TimeKeeping --------
    ------------------------*/
    Route::get('/weekly-timekeeping', [WeeklyTimekeepingController::class, 'index'])
        ->middleware('permission:weekly-timekeeping.view')
        ->name('weekly-timekeeping.index');

    Route::post('/weekly-timekeeping/cutoffs', [WeeklyTimekeepingController::class, 'storeCutoff'])
        ->middleware('permission:weekly-timekeeping.create')
        ->name('weekly-timekeeping.cutoffs.store');

    Route::get('/weekly-timekeeping/cutoffs/{cutoff}', [WeeklyTimekeepingController::class, 'showCutoff'])
        ->middleware('permission:weekly-timekeeping.view')
        ->name('weekly-timekeeping.cutoffs.show');

    Route::get('/weekly-timekeeping/cutoffs/{cutoff}/employees/{employee}', [WeeklyTimekeepingController::class, 'showEmployee'])
        ->middleware('permission:weekly-timekeeping.view')
        ->name('weekly-timekeeping.employees.show');

    Route::patch('/weekly-timekeeping/cutoffs/{cutoff}/finalize', [WeeklyTimekeepingController::class, 'finalize'])
        ->middleware('permission:weekly-timekeeping.finalize')
        ->name('weekly-timekeeping.cutoffs.finalize');

    Route::delete('/weekly-timekeeping/cutoffs/{cutoff}', [WeeklyTimekeepingController::class, 'destroy'])
        ->middleware('permission:weekly-timekeeping.delete')
        ->name('weekly-timekeeping.cutoffs.destroy');

    Route::post('/weekly-timekeeping/cutoffs/{cutoff}/employees/{employee}/ot-approval', [WeeklyTimekeepingController::class, 'saveEmployeeOtApproval'])
        ->middleware('permission:weekly-timekeeping.update')
        ->name('weekly-timekeeping.employees.ot-approval');

    Route::patch('/weekly-timekeeping/cutoffs/{cutoff}/employees/{employee}/finalize', [WeeklyTimekeepingController::class, 'finalizeEmployee'])
        ->middleware('permission:weekly-timekeeping.update')
        ->name('weekly-timekeeping.employees.finalize');

    Route::patch('/weekly-timekeeping/cutoffs/{cutoff}/employees/{employee}/unfinalize', [WeeklyTimekeepingController::class, 'unfinalizeEmployee'])
        ->middleware('permission:weekly-timekeeping.update')
        ->name('weekly-timekeeping.employees.unfinalize');

    /*------------------------------------
     -------- Monthly TimeKeeping --------
    --------------------------------------*/
    Route::get('/monthly-timekeeping', [MonthlyTimekeepingController::class, 'index'])
        ->middleware('permission:monthly-timekeeping.view')
        ->name('monthly-timekeeping.index');

    Route::post('/monthly-timekeeping/cutoffs', [MonthlyTimekeepingController::class, 'storeCutoff'])
        ->middleware('permission:monthly-timekeeping.create')
        ->name('monthly-timekeeping.cutoffs.store');

    Route::get('/monthly-timekeeping/cutoffs/{cutoff}', [MonthlyTimekeepingController::class, 'showCutoff'])
        ->middleware('permission:monthly-timekeeping.view')
        ->name('monthly-timekeeping.cutoffs.show');

    Route::patch('/monthly-timekeeping/cutoffs/{cutoff}/finalize', [MonthlyTimekeepingController::class, 'finalizeCutoff'])
        ->middleware('permission:monthly-timekeeping.update')
        ->name('monthly-timekeeping.cutoffs.finalize');

    Route::delete('/monthly-timekeeping/cutoffs/{cutoff}', [MonthlyTimekeepingController::class, 'destroyCutoff'])
        ->middleware('permission:monthly-timekeeping.delete')
        ->name('monthly-timekeeping.cutoffs.destroy');

    Route::get('/monthly-timekeeping/cutoffs/{cutoff}/employees/{employee}', [MonthlyTimekeepingController::class, 'showEmployee'])
        ->middleware('permission:monthly-timekeeping.view')
        ->name('monthly-timekeeping.employees.show');

    Route::post('/monthly-timekeeping/cutoffs/{cutoff}/employees/{employee}/ot-approval', [MonthlyTimekeepingController::class, 'saveEmployeeOtApproval'])
        ->middleware('permission:monthly-timekeeping.update')
        ->name('monthly-timekeeping.employees.ot-approval');

    Route::patch('/monthly-timekeeping/cutoffs/{cutoff}/employees/{employee}/finalize', [MonthlyTimekeepingController::class, 'finalizeEmployee'])
        ->middleware('permission:monthly-timekeeping.update')
        ->name('monthly-timekeeping.employees.finalize');

    Route::patch('/monthly-timekeeping/cutoffs/{cutoff}/employees/{employee}/unfinalize', [MonthlyTimekeepingController::class, 'unfinalizeEmployee'])
        ->middleware('permission:monthly-timekeeping.update')
        ->name('monthly-timekeeping.employees.unfinalize');

    /*------------------------
    -------- LOCATIONS --------
    -------------------------*/

    Route::get('/locations', [LocationController::class, 'index'])
        ->middleware('permission:locations.view')
        ->name('locations.index');

    Route::get('/locations/create', [LocationController::class, 'create'])
        ->middleware('permission:locations.create')
        ->name('locations.create');

    Route::post('/locations', [LocationController::class, 'store'])
        ->middleware('permission:locations.create')
        ->name('locations.store');

    Route::get('/locations/{location}/edit', [LocationController::class, 'edit'])
        ->middleware('permission:locations.update')
        ->name('locations.edit');

    Route::put('/locations/{location}', [LocationController::class, 'update'])
        ->middleware('permission:locations.update')
        ->name('locations.update');

    Route::delete('/locations/{location}', [LocationController::class, 'destroy'])
        ->middleware('permission:locations.delete')
        ->name('locations.destroy');

    /*----------------------
    -------- HOLIDAYS --------
    ------------------------*/

    Route::get('/holidays', [HolidayController::class, 'index'])
        ->middleware('permission:holidays.view')
        ->name('holidays.index');

    Route::get('/holidays/create', [HolidayController::class, 'create'])
        ->middleware('permission:holidays.create')
        ->name('holidays.create');

    Route::post('/holidays', [HolidayController::class, 'store'])
        ->middleware('permission:holidays.create')
        ->name('holidays.store');

    Route::post('/holidays/generate', [HolidayController::class, 'generate'])
        ->middleware('permission:holidays.create')
        ->name('holidays.generate');

    Route::get('/holidays/{holiday}/edit', [HolidayController::class, 'edit'])
        ->middleware('permission:holidays.update')
        ->name('holidays.edit');

    Route::put('/holidays/{holiday}', [HolidayController::class, 'update'])
        ->middleware('permission:holidays.update')
        ->name('holidays.update');

    Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])
        ->middleware('permission:holidays.delete')
        ->name('holidays.destroy');
});

require __DIR__.'/auth.php';
