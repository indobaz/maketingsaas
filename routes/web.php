<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\AcceptInviteController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\TeamController;

// Public routes
Route::get('/', fn () => redirect('/login'));
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [RegisterController::class, 'showRegisterForm']);
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/verify-email', [VerifyEmailController::class, 'showVerifyForm']);
Route::post('/verify-email', [VerifyEmailController::class, 'verify']);
Route::post('/verify-email/resend', [VerifyEmailController::class, 'resend']);

Route::get('/invite/accept', [AcceptInviteController::class, 'show'])->name('invite.accept');
Route::post('/invite/accept', [AcceptInviteController::class, 'accept']);

Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::get('/reset-password', [PasswordResetController::class, 'showResetForm']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);

    Route::get('/onboarding', [OnboardingController::class, 'index']);
    Route::get('/onboarding/step1', [OnboardingController::class, 'step1']);
    Route::post('/onboarding/step1', [OnboardingController::class, 'saveStep1']);
    Route::get('/onboarding/step2', [OnboardingController::class, 'step2']);
    Route::post('/onboarding/step2', [OnboardingController::class, 'saveStep2']);
    Route::get('/onboarding/step3', [OnboardingController::class, 'step3']);
    Route::post('/onboarding/step3', [OnboardingController::class, 'saveStep3']);

    Route::middleware(['company.setup'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
    });

    Route::middleware(['role:owner,admin'])->group(function () {
        Route::get('/settings', fn () => response('Settings placeholder'));
    });

    Route::middleware(['role:owner'])->group(function () {
        Route::get('/role-test', fn () => response('Role middleware passed (owner).'));
    });

    Route::get('/team', [TeamController::class, 'index']);
    Route::post('/team/invite', [TeamController::class, 'invite']);
    Route::post('/team/{user}/resend', [TeamController::class, 'resendInvite']);
    Route::delete('/team/{user}/remove', [TeamController::class, 'removeUser']);
});
