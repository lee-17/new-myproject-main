<?php

use App\Http\Controllers\AdministratorRequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberPortalController;
use App\Http\Controllers\MemberRegistrationController;
use App\Http\Controllers\MembershipApplicationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WeatherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->middleware(['auth', 'role:admin'])->name('dashboard');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::get('/member/login', fn () => app(AuthController::class)->showLogin('member'))->name('member.login');
Route::post('/member/login', fn (Request $request) => app(AuthController::class)->login($request, 'member'))->name('member.login.store');
Route::get('/member/register', [MemberRegistrationController::class, 'create'])->name('member.register');
Route::post('/member/register', [MemberRegistrationController::class, 'store'])->name('member.register.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/tasks', [TaskController::class, 'index'])->middleware(['auth', 'role:admin'])->name('tasks.index');
Route::post('/tasks', [TaskController::class, 'store'])->middleware(['auth', 'role:admin'])->name('tasks.store');
Route::patch('/tasks/{task}', [TaskController::class, 'update'])->middleware(['auth', 'role:admin'])->name('tasks.update');
Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->middleware(['auth', 'role:admin'])->name('tasks.toggle');
Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->middleware(['auth', 'role:admin'])->name('tasks.destroy');

Route::resource('members', MemberController::class)->middleware(['auth', 'role:admin']);
Route::patch('/members/{member}/deactivate', [MemberController::class, 'deactivate'])->middleware(['auth', 'role:admin'])->name('members.deactivate');
Route::get('/members/{member}/print', [MemberController::class, 'print'])->middleware(['auth', 'role:admin'])->name('members.print');
Route::get('/members/{member}/portal-login', [MemberController::class, 'createPortalLogin'])->middleware(['auth', 'role:admin'])->name('members.portal-login.create');
Route::post('/members/{member}/portal-login', [MemberController::class, 'storePortalLogin'])->middleware(['auth', 'role:admin'])->name('members.portal-login.store');

Route::get('/membership/apply', [MembershipApplicationController::class, 'create'])->name('membership.create');
Route::post('/membership/apply', [MembershipApplicationController::class, 'store'])->name('membership.store');
Route::get('/weather', [WeatherController::class, 'show'])->name('weather.show');

Route::middleware(['auth', 'role:admin'])->group(function (): void {
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

Route::middleware(['auth', 'role:member'])->prefix('member')->name('member.')->group(function (): void {
    Route::get('/dashboard', [MemberPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [MemberPortalController::class, 'profile'])->name('profile');
    Route::patch('/profile', [MemberPortalController::class, 'updateProfile'])->name('profile.update');
    Route::get('/tasks', [MemberPortalController::class, 'tasks'])->name('tasks');
    Route::patch('/tasks/{task}/complete', [MemberPortalController::class, 'completeTask'])->name('tasks.complete');
    Route::get('/transactions', [MemberPortalController::class, 'transactions'])->name('transactions');
    Route::get('/transactions/request', [MemberPortalController::class, 'createTransactionRequest'])->name('transactions.request.create');
    Route::post('/transactions/request', [MemberPortalController::class, 'storeTransactionRequest'])->name('transactions.request.store');
    Route::get('/administrator-request', [AdministratorRequestController::class, 'create'])->name('administrator-request.create');
    Route::post('/administrator-request', [AdministratorRequestController::class, 'store'])->name('administrator-request.store');
    Route::get('/administrator-requests', [AdministratorRequestController::class, 'memberIndex'])->name('admin-requests');
});

Route::middleware(['auth', 'role:admin'])->group(function (): void {
    Route::get('/administrator-requests', [AdministratorRequestController::class, 'index'])->name('administrator-requests.index');
    Route::patch('/administrator-requests/{administratorRequest}/approve', [AdministratorRequestController::class, 'approve'])->name('administrator-requests.approve');
    Route::patch('/administrator-requests/{administratorRequest}/reject', [AdministratorRequestController::class, 'reject'])->name('administrator-requests.reject');
});
