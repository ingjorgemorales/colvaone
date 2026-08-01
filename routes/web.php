<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\Auth\PasswordCodeController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
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
    Route::get('/password/change', [PasswordChangeController::class, 'edit'])->name('password.change.edit');
    Route::put('/password/change', [PasswordChangeController::class, 'update'])->name('password.change.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('users', UserController::class)->except(['show']);
    Route::post('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

    Route::resource('roles', RoleController::class)->except(['show']);

    Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
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
