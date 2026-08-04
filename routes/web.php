<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\Auth\PasswordCodeController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/password/code', [PasswordCodeController::class, 'show'])->name('password.code');
    Route::post('/password/code', [PasswordCodeController::class, 'verify'])->name('password.code.verify');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::get('/password/change', [PasswordChangeController::class, 'forcedEdit'])->name('password.change.edit');
    Route::put('/password/change', [PasswordChangeController::class, 'forcedUpdate'])->name('password.change.update');
    Route::get('/password/profile', [PasswordChangeController::class, 'edit'])->name('password.profile.edit');
    Route::put('/password/profile', [PasswordChangeController::class, 'update'])->name('password.profile.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('users', UserController::class)->except(['show'])->middleware('permission:users.view');
    Route::post('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle')->middleware('permission:users.edit');

    Route::resource('roles', RoleController::class)->except(['show'])->middleware('permission:roles.view');
    Route::post('roles/{role}/toggle', [RoleController::class, 'toggle'])->name('roles.toggle')->middleware('permission:roles.edit');

    Route::resource('groups', GroupController::class)->except(['show'])->middleware('permission:groups.view');
    Route::get('groups/{group}', [GroupController::class, 'show'])->name('groups.show')->middleware('permission:groups.view');
    Route::post('groups/{group}/toggle', [GroupController::class, 'toggle'])->name('groups.toggle')->middleware('permission:groups.disable');
    Route::post('groups/{group}/members', [GroupController::class, 'addMember'])->name('groups.members.add')->middleware('permission:groups.manage_members');
    Route::delete('groups/{group}/members/{user}', [GroupController::class, 'removeMember'])->name('groups.members.remove')->middleware('permission:groups.manage_members');

    Route::get('audit', [AuditController::class, 'index'])->name('audit.index')->middleware('permission:audit.view');

    Route::resource('tasks', TaskController::class)->middleware('permission:group_tasks.view');
    Route::get('tasks-group-members/{group}', [TaskController::class, 'getGroupMembers'])->name('tasks.group-members')->middleware('auth');
    Route::post('tasks/{task}/progress', [TaskController::class, 'updateProgress'])->name('tasks.progress.update')->middleware('permission:group_tasks.update_progress');
    Route::post('tasks/{task}/comment', [TaskController::class, 'addComment'])->name('tasks.comments.add')->middleware('permission:group_tasks.comment');
    Route::post('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status.update')->middleware('permission:group_tasks.update');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'navigationItems' => collect(config('navigation.items'))
            ->where('enabled', true)
            ->sortBy('order')
            ->values(),
    ]);
})->middleware(['auth', 'verified', 'password.changed'])->name('dashboard');

Route::view('/politica-tratamiento-datos', 'legal.data-policy')
    ->name('legal.data-policy');

Route::view('/terminos', 'legal.terms')
    ->name('legal.terms');
