<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MembershipApplicationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');

Route::resource('members', MemberController::class);
Route::patch('/members/{member}/deactivate', [MemberController::class, 'deactivate'])->name('members.deactivate');

Route::get('/membership/apply', [MembershipApplicationController::class, 'create'])->name('membership.create');
Route::post('/membership/apply', [MembershipApplicationController::class, 'store'])->name('membership.store');
Route::get('/weather', [WeatherController::class, 'show'])->name('weather.show');

Route::middleware('auth')->group(function (): void {
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('levels', LevelController::class)->except('show');

    Route::get('/transactions/member/{member}', [TransactionController::class, 'history'])->name('transactions.history');
    Route::resource('transactions', TransactionController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('/transactions/{transaction}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
    Route::patch('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');

    Route::get('/transactions/{transaction}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/transactions/{transaction}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/transactions/{transaction}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
});
