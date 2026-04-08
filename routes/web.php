<?php

use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    // DASHBOARD (WELCOME)
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    // USERS
    Route::resource('users', UserController::class);

    // ROLES
    Route::resource('roles', RoleController::class);

});

require __DIR__.'/auth.php';
