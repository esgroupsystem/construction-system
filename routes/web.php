<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeFaceRegistrationController;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

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

    // List employees
    Route::get('/face-registration', [EmployeeFaceRegistrationController::class, 'index'])
        ->middleware('permission:employees.view')
        ->name('face-registration.index');

    // Show registration page
    Route::get('/face-registration/{employee}', [EmployeeFaceRegistrationController::class, 'show'])
        ->middleware('permission:employees.update')
        ->name('face-registration.show');

    // STORE (save face samples)
    Route::post('/face-registration/{employee}', [EmployeeFaceRegistrationController::class, 'store'])
        ->middleware('permission:employees.update')
        ->name('face-registration.store');

    // UPDATE (set primary)
    Route::put('/face-registration/{employee}/{sample}', [EmployeeFaceRegistrationController::class, 'update'])
        ->middleware('permission:employees.update')
        ->name('face-registration.update');

    // DELETE
    Route::delete('/face-registration/{employee}/{sample}', [EmployeeFaceRegistrationController::class, 'destroy'])
        ->middleware('permission:employees.update')
        ->name('face-registration.destroy');

    /*----------------------
     -------- FACE RECOGNITION --------
    ------------------------*/
    Route::get('/face-recognition', [FaceRecognitionController::class, 'index'])
        ->middleware('permission:employees.view')
        ->name('face-recognition.index');

    Route::post('/face-recognition/identify', [FaceRecognitionController::class, 'identify'])
        ->middleware('permission:employees.view')
        ->name('face-recognition.identify');
});

require __DIR__.'/auth.php';
