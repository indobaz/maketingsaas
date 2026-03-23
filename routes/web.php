<?php

use App\Http\Controllers\Auth\AcceptInviteController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\ContentPillarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PostApprovalController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

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

        Route::get('/channels', [ChannelController::class, 'index'])->name('channels.index');
        Route::post('/channels', [ChannelController::class, 'store'])->name('channels.store');
        Route::put('/channels/{channel}', [ChannelController::class, 'update'])->name('channels.update');
        Route::delete('/channels/{channel}', [ChannelController::class, 'destroy'])->name('channels.destroy');
        Route::post('/channels/{channel}/followers', [ChannelController::class, 'updateFollowers'])->name('channels.followers.update');

        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
        Route::post('/calendar/reschedule/{post}', [CalendarController::class, 'reschedule'])->name('calendar.reschedule');

        Route::get('/pillars', [ContentPillarController::class, 'index'])->name('pillars.index');
        Route::post('/pillars', [ContentPillarController::class, 'store'])->name('pillars.store');
        Route::put('/pillars/{contentPillar}', [ContentPillarController::class, 'update'])->name('pillars.update');
        Route::delete('/pillars/{contentPillar}', [ContentPillarController::class, 'destroy'])->name('pillars.destroy');
        Route::post('/pillars/defaults', [ContentPillarController::class, 'loadDefaults'])->name('pillars.defaults');

        Route::get('/content', [PostController::class, 'index'])->name('content.index');
        Route::get('/content/create', [PostController::class, 'create'])->name('content.create');
        Route::post('/content', [PostController::class, 'store'])->name('content.store');
        Route::get('/content/{post}/edit', [PostController::class, 'edit'])->name('content.edit');
        Route::put('/content/{post}', [PostController::class, 'update'])->name('content.update');
        Route::delete('/content/{post}', [PostController::class, 'destroy'])->name('content.destroy');

        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
        Route::post('/tasks/{task}/notes', [TaskController::class, 'addNote'])->name('tasks.notes.store');
        Route::get('/tasks/{task}/detail', [TaskController::class, 'getTaskDetail'])->name('tasks.detail');
        Route::post('/tasks/{task}/checklist', [TaskController::class, 'addChecklist'])->name('tasks.checklist.store');
        Route::post('/tasks/checklist/{checklist}/toggle', [TaskController::class, 'toggleChecklist'])->name('tasks.checklist.toggle');
        Route::delete('/tasks/checklist/{checklist}', [TaskController::class, 'deleteChecklist'])->name('tasks.checklist.destroy');
        Route::post('/tasks/{task}/subtask', [TaskController::class, 'addSubtask'])->name('tasks.subtask.store');
        Route::put('/tasks/subtask/{subtask}', [TaskController::class, 'updateSubtask'])->name('tasks.subtask.update');
        Route::delete('/tasks/subtask/{subtask}', [TaskController::class, 'deleteSubtask'])->name('tasks.subtask.destroy');

        Route::post('/posts/{post}/submit-review', [PostApprovalController::class, 'submitForReview'])->name('posts.submit-review');
        Route::post('/posts/{post}/approve', [PostApprovalController::class, 'approve'])->name('posts.approve');
        Route::post('/posts/{post}/reject', [PostApprovalController::class, 'reject'])->name('posts.reject');
        Route::post('/posts/{post}/publish', [PostApprovalController::class, 'publishPost'])->name('posts.publish');
        Route::post('/posts/{post}/comment', [PostApprovalController::class, 'addComment'])->name('posts.comment');

        Route::middleware(['role:owner,admin'])->group(function () {
            Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::get('/email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');
            Route::get('/email-templates/{key}/edit', [EmailTemplateController::class, 'edit'])->name('email-templates.edit');
            Route::get('/email-templates/{key}/preview', [EmailTemplateController::class, 'preview'])->name('email-templates.preview');
        });

        Route::middleware(['role:owner'])->group(function () {
            Route::post('/settings/smtp/test', [SettingsController::class, 'testSmtp'])->name('settings.smtp.test');
            Route::post('/settings/smtp', [SettingsController::class, 'updateSmtp'])->name('settings.smtp');
            Route::post('/settings/brand', [SettingsController::class, 'updateBrandKit'])->name('settings.brand');
            Route::post('/settings/company', [SettingsController::class, 'updateCompany'])->name('settings.company');
            Route::post('/email-templates/{key}', [EmailTemplateController::class, 'update'])->name('email-templates.update');
            Route::post('/email-templates/{key}/reset', [EmailTemplateController::class, 'reset'])->name('email-templates.reset');
            Route::post('/email-templates/{key}/test', [EmailTemplateController::class, 'sendTest'])->name('email-templates.test');
        });
    });

    Route::middleware(['role:owner'])->group(function () {
        Route::get('/role-test', fn () => response('Role middleware passed (owner).'));
    });

    Route::get('/team', [TeamController::class, 'index']);
    Route::post('/team/invite', [TeamController::class, 'invite']);
    Route::post('/team/{user}/resend', [TeamController::class, 'resendInvite']);
    Route::delete('/team/{user}/remove', [TeamController::class, 'removeUser']);

    Route::get('/super-admin/smtp', [SuperAdminController::class, 'smtpSettings'])->name('super-admin.smtp');
    Route::post('/super-admin/smtp', [SuperAdminController::class, 'updateSmtp'])->name('super-admin.smtp.update');
    Route::post('/super-admin/smtp/test', [SuperAdminController::class, 'testSmtp'])->name('super-admin.smtp.test');
});
