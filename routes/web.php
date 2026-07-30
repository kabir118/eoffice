<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SupportQueryController;
use App\Http\Controllers\DesignationController;

// 1. Root redirect
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

// 2. Authentication Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// 3. Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/contact-admin', [SupportQueryController::class, 'store'])->name('contact.admin');

    // Support Queries Ticket System
    Route::get('/support', [SupportQueryController::class, 'index'])->name('support.index');
    Route::post('/support', [SupportQueryController::class, 'store'])->name('support.store');
    Route::get('/support/{query}', [SupportQueryController::class, 'show'])->name('support.show');
    Route::post('/support/{query}/reply', [SupportQueryController::class, 'reply'])->name('support.reply');
    Route::post('/support/{query}/message', [SupportQueryController::class, 'postMessage'])->name('support.postMessage');
    Route::post('/support/close-stale', [SupportQueryController::class, 'closeStale'])->name('support.closeStale');
    Route::post('/support/bulk-close', [SupportQueryController::class, 'bulkClose'])->name('support.bulkClose');

    // User Profile Change Password
    Route::get('/profile/password', [UserController::class, 'showPasswordForm'])->name('profile.password');
    Route::post('/profile/password', [UserController::class, 'updatePassword'])->name('profile.password.update');

    // Document / File Tracking Routes
    Route::get('/my-files', [DocumentController::class, 'myFiles'])->name('documents.myFiles');
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::get('/documents/{document}/print', [DocumentController::class, 'printView'])->name('documents.print');
    Route::post('/documents/{document}/forward', [DocumentController::class, 'forward'])->name('documents.forward');
    Route::post('/documents/{document}/status', [DocumentController::class, 'updateStatus'])->name('documents.status');
    Route::post('/documents/{document}/comment', [DocumentController::class, 'comment'])->name('documents.comment');
    Route::post('/documents/bulk-delete', [DocumentController::class, 'bulkDelete'])->name('documents.bulkDelete');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::post('/documents/{id}/restore', [DocumentController::class, 'restore'])->name('documents.restore');
    Route::delete('/documents/{id}/force-delete', [DocumentController::class, 'forceDelete'])->name('documents.forceDelete');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/attachments/{type}/{id}/download', [DocumentController::class, 'downloadAttachment'])->name('attachments.download');
    Route::get('/my-remarks', [DocumentController::class, 'myRemarks'])->name('documents.myRemarks');
    Route::get('/documents/{document}/my-comments', [DocumentController::class, 'myComments'])->name('documents.myComments');

    // e-Notifications Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/{id}/restore', [NotificationController::class, 'restore'])->name('notifications.restore');
    Route::delete('/notifications/{id}/force-delete', [NotificationController::class, 'forceDelete'])->name('notifications.forceDelete');

    // 4. Admin Only Routes
    Route::middleware('admin')->group(function () {
        // Departments CRUD
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        // Designations CRUD
        Route::get('/designations', [DesignationController::class, 'index'])->name('designations.index');
        Route::post('/designations', [DesignationController::class, 'store'])->name('designations.store');
        Route::put('/designations/{designation}', [DesignationController::class, 'update'])->name('designations.update');
        Route::delete('/designations/{designation}', [DesignationController::class, 'destroy'])->name('designations.destroy');

        // Users CRUD
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
