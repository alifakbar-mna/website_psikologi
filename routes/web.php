<?php

use App\Http\Controllers\AccessPermissionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookmarkLikeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentRequestController;
use App\Http\Controllers\MemberImportController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    $destinations = [
        'catalog' => 'student.catalog',
        'dashboard' => 'admin.dashboard',
        'approve_access' => 'lecturer.requests',
        'upload' => 'reports.mine',
        'manage_users' => 'admin.users',
        'manage_permissions' => 'permissions.index',
        'review_uploads' => 'reports.reviews',
        'manage_documents' => 'reports.manage',
        'request_access' => 'student.requests',
        'manage_categories' => 'lecturer.categories',
        'grant_access' => 'access.grant.create',
        'activity_logs' => 'admin.logs',
        'bookmarks' => 'student.bookmarks',
    ];

    foreach ($destinations as $permission => $route) {
        if (auth()->user()->permits($permission)) {
            return redirect()->route($route);
        }
    }

    return response()->view('cms.no-access');
})->name('home');

Route::view('/login', 'auth-login')
    ->name('login');

Route::get(
    '/auth/google',
    [AuthController::class, 'redirectToGoogle']
)->name('auth.google');

Route::get(
    '/auth/google/callback',
    [AuthController::class, 'handleGoogleCallback']
);

Route::post(
    '/logout',
    [AuthController::class, 'logout']
)->name('logout');

Route::middleware('auth')->group(function () {
    Route::get(
        '/catalog',
        [DocumentController::class, 'index']
    )
        ->middleware('permission:catalog')
        ->name('student.catalog');

    Route::get(
        '/document/{id}',
        [DocumentController::class, 'show']
    )
        ->middleware('permission:read')
        ->name('student.document.show');

    Route::get(
        '/pdf/{filename}',
        [DocumentController::class, 'stream']
    )->name('pdf.stream');

    Route::get(
        '/reports/{id}/preview',
        [DocumentController::class, 'preview']
    )->name('reports.preview');

    Route::middleware('permission:request_access')
        ->group(function () {
            Route::get(
                '/requests',
                [DocumentRequestController::class, 'studentIndex']
            )->name('student.requests');

            Route::post(
                '/requests',
                [DocumentRequestController::class, 'store']
            )->name('student.requests.store');
        });

    Route::middleware('permission:bookmarks')
        ->group(function () {
            Route::get(
                '/student/bookmarks',
                [BookmarkLikeController::class, 'index']
            )->name('student.bookmarks');

            Route::post(
                '/document/{id}/bookmark',
                [BookmarkLikeController::class, 'toggleBookmark']
            )->name('document.bookmark');

            Route::post(
                '/document/{id}/like',
                [BookmarkLikeController::class, 'toggleLike']
            )->name('document.like');
        });

    Route::middleware('permission:upload')
        ->group(function () {
            Route::get(
                '/lecturer/upload',
                [DocumentController::class, 'create']
            )->name('lecturer.upload');

            Route::post(
                '/lecturer/upload',
                [DocumentController::class, 'storeUpload']
            )->name('lecturer.upload.store');

            Route::get(
                '/reports/mine',
                [ReportController::class, 'mine']
            )->name('reports.mine');
        });

    Route::get(
        '/reports/{id}/edit',
        [ReportController::class, 'edit']
    )->name('reports.edit');

    Route::put(
        '/reports/{id}',
        [ReportController::class, 'update']
    )->name('reports.update');

    Route::middleware('permission:review_uploads')
        ->group(function () {
            Route::get(
                '/review-uploads',
                [ReportController::class, 'reviews']
            )->name('reports.reviews');

            Route::post(
                '/review-uploads/{id}',
                [ReportController::class, 'decide']
            )->name('reports.decide');
        });

    Route::get(
        '/manage-reports',
        [ReportController::class, 'manage']
    )
        ->middleware('permission:manage_documents')
        ->name('reports.manage');

    Route::middleware('permission:manage_categories')
        ->group(function () {
            Route::get(
                '/lecturer/categories',
                [CategoryController::class, 'index']
            )->name('lecturer.categories');

            Route::post(
                '/lecturer/categories',
                [CategoryController::class, 'store']
            )->name('lecturer.categories.store');

            Route::put(
                '/lecturer/categories/{id}',
                [CategoryController::class, 'update']
            )->name('lecturer.categories.update');

            Route::patch(
                '/lecturer/categories/{id}/toggle',
                [CategoryController::class, 'toggleHide']
            )->name('lecturer.categories.toggle');

            Route::delete(
                '/lecturer/categories/{id}',
                [CategoryController::class, 'destroy']
            )->name('lecturer.categories.destroy');
        });

    Route::middleware('permission:approve_access')
        ->group(function () {
            Route::get(
                '/lecturer/requests',
                [DocumentRequestController::class, 'lecturerIndex']
            )->name('lecturer.requests');

            Route::post(
                '/lecturer/requests/{id}/process',
                [DocumentRequestController::class, 'process']
            )->name('lecturer.requests.process');
        });

    Route::get(
        '/admin/dashboard',
        [AdminController::class, 'index']
    )
        ->middleware('permission:dashboard')
        ->name('admin.dashboard');

    Route::get(
        '/admin/logs',
        [AdminController::class, 'activityLogs']
    )
        ->middleware('permission:activity_logs')
        ->name('admin.logs');

    Route::middleware('permission:manage_users')
        ->group(function () {
            Route::get(
                '/admin/users',
                [UserController::class, 'index']
            )->name('admin.users');

            Route::post(
                '/admin/users',
                [UserController::class, 'store']
            )->name('admin.users.store');

            Route::put(
                '/admin/users/{id}',
                [UserController::class, 'update']
            )->name('admin.users.update');

            Route::delete(
                '/admin/users/{id}',
                [UserController::class, 'destroy']
            )->name('admin.users.destroy');

            Route::get(
                '/admin/members/template',
                [MemberImportController::class, 'template']
            )->name('members.template');

            Route::post(
                '/admin/members/preview',
                [MemberImportController::class, 'preview']
            )->name('members.preview');

            Route::post(
                '/admin/members/import',
                [MemberImportController::class, 'store']
            )->name('members.import');
        });

    Route::middleware('permission:grant_access')
        ->group(function () {
            Route::get(
                '/access/grant',
                [AccessPermissionController::class, 'create']
            )->name('access.grant.create');

            Route::post(
                '/access/grant',
                [AccessPermissionController::class, 'store']
            )->name('access.grant.store');
        });

    Route::middleware('permission:manage_permissions')
        ->group(function () {
            Route::get(
                '/admin/permissions',
                [PermissionController::class, 'index']
            )->name('permissions.index');

            Route::post(
                '/admin/permissions',
                [PermissionController::class, 'store']
            )->name('permissions.store');
        });
});