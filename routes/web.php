<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::controller(TaskController::class)->group(function () {
        Route::get('tasks/assignable-users', 'assignableUsers')->name('tasks.assignable-users');
    });

    Route::resource('tasks', TaskController::class);
});
