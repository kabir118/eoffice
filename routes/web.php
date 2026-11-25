<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SuggestionController;
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

    // Suggestions / Issues — sends email to admin
    Route::get('/support', [SuggestionController::class, 'show'])->name('support.index');
    Route::post('/support', [SuggestionController::class, 'send'])->name('support.store');

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
    Route::get('/documents/{document}/forward', fn($document) => redirect()->route('documents.show', $document));
    Route::post('/documents/{document}/status', [DocumentController::class, 'updateStatus'])->name('documents.status');
    Route::get('/documents/{document}/status', fn($document) => redirect()->route('documents.show', $document));
    Route::post('/documents/{document}/comment', [DocumentController::class, 'comment'])->name('documents.comment');
    Route::delete('/documents/{document}/comments/{id}', [DocumentController::class, 'deleteComment'])->name('documents.deleteComment');
    Route::post('/documents/{document}/comments/{id}/restore', [DocumentController::class, 'restoreComment'])->name('documents.restoreComment');
    Route::delete('/documents/{document}/comments/{id}/force-delete', [DocumentController::class, 'forceDeleteComment'])->name('documents.forceDeleteComment');
    Route::post('/documents/{document}/comments/bulk-action', [DocumentController::class, 'bulkCommentAction'])->name('documents.bulkCommentAction');
    Route::post('/documents/bulk-delete', [DocumentController::class, 'bulkDelete'])->name('documents.bulkDelete');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::post('/documents/{id}/restore', [DocumentController::class, 'restore'])->name('documents.restore');
    Route::delete('/documents/{id}/force-delete', [DocumentController::class, 'forceDelete'])->name('documents.forceDelete');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/attachments/{type}/{id}/download', [DocumentController::class, 'downloadAttachment'])->name('attachments.download');
    Route::get('/my-remarks', [DocumentController::class, 'myRemarks'])->name('documents.myRemarks');
    Route::get('/documents/{document}/my-comments', [DocumentController::class, 'myComments'])->name('documents.myComments');
    Route::get('/users/search', [DocumentController::class, 'searchUsers'])->name('users.search');

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

        // Archived Comments Central Management
        Route::get('/archives/comments', [DocumentController::class, 'archivedCommentsIndex'])->name('archives.comments');
        Route::post('/archives/comments/bulk-action', [DocumentController::class, 'bulkArchivedCommentsAll'])->name('archives.comments.bulkAction');
    });
});
