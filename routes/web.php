<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentRequestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BookmarkLikeController;
use App\Http\Controllers\AccessPermissionController;

/*
|--------------------------------------------------------------------------
| Guest / Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return view('auth-login');
})->name('login');

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])
    ->name('auth.google');

Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | REQUEST AKSES
    |--------------------------------------------------------------------------
    */

    Route::get('/requests', [DocumentRequestController::class, 'studentIndex'])
        ->name('student.requests');

    Route::post('/requests', [DocumentRequestController::class, 'store'])
        ->name('student.requests.store');


    /*
    |--------------------------------------------------------------------------
    | KATALOG & DOKUMEN MAHASISWA
    |--------------------------------------------------------------------------
    */

    Route::get('/catalog', [DocumentController::class, 'index'])
        ->name('student.catalog');

    Route::get('/document/{id}', [DocumentController::class, 'show'])
        ->name('student.document.show');


    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    Route::get('/document/{id}/pdf', [DocumentController::class, 'pdf'])
        ->name('document.pdf');

    Route::get('/pdf/{filename}', [DocumentController::class, 'stream'])
        ->name('pdf.stream');

    Route::get('/document/{id}/download', [DocumentController::class, 'download'])
        ->name('pdf.download');


    /*
    |--------------------------------------------------------------------------
    | BOOKMARK & LIKE
    |--------------------------------------------------------------------------
    */

    Route::get('/student/bookmarks', [BookmarkLikeController::class, 'index'])
        ->name('student.bookmarks');

    Route::post('/document/{id}/bookmark', [BookmarkLikeController::class, 'toggleBookmark'])
        ->name('document.bookmark');

    Route::post('/document/{id}/like', [BookmarkLikeController::class, 'toggleLike'])
        ->name('document.like');


    /*
    |--------------------------------------------------------------------------
    | UPLOAD LAPORAN
    |--------------------------------------------------------------------------
    |
    | Admin/Dosen  -> langsung boleh
    | Mahasiswa    -> hanya boleh jika memiliki can_upload aktif
    |
    | Permission mahasiswa akan dicek di DocumentController.
    |
    */

    Route::get('/lecturer/upload', [DocumentController::class, 'create'])
        ->name('lecturer.upload');

    Route::post('/lecturer/upload', [DocumentController::class, 'storeUpload'])
        ->name('lecturer.upload.store');


    /*
    |--------------------------------------------------------------------------
    | DOSEN & ADMIN
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:dosen,admin'])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        Route::get('/lecturer/categories', [CategoryController::class, 'index'])
            ->name('lecturer.categories');

        Route::post('/lecturer/categories', [CategoryController::class, 'store'])
            ->name('lecturer.categories.store');

        Route::put('/lecturer/categories/{id}', [CategoryController::class, 'update'])
            ->name('lecturer.categories.update');

        Route::patch('/lecturer/categories/{id}/toggle', [CategoryController::class, 'toggleHide'])
            ->name('lecturer.categories.toggle');

        Route::delete('/lecturer/categories/{id}', [CategoryController::class, 'destroy'])
            ->name('lecturer.categories.destroy');


        /*
        |--------------------------------------------------------------------------
        | APPROVAL REQUEST
        |--------------------------------------------------------------------------
        */

        Route::get('/lecturer/requests', [DocumentRequestController::class, 'lecturerIndex'])
            ->name('lecturer.requests');

        Route::post('/lecturer/requests/{id}/process', [DocumentRequestController::class, 'process'])
            ->name('lecturer.requests.process');
    });


    /*
    |--------------------------------------------------------------------------
    | ADMIN ONLY
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:admin'])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */

        Route::get('/admin/dashboard', [AdminController::class, 'index'])
            ->name('admin.dashboard');

        Route::get('/admin/logs', [AdminController::class, 'activityLogs'])
            ->name('admin.logs');


        /*
        |--------------------------------------------------------------------------
        | KELOLA USER
        |--------------------------------------------------------------------------
        */

        Route::get('/admin/users', [UserController::class, 'index'])
            ->name('admin.users');

        Route::post('/admin/users', [UserController::class, 'store'])
            ->name('admin.users.store');

        Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])
            ->name('admin.users.destroy');


        /*
        |--------------------------------------------------------------------------
        | BERIKAN AKSES SEMENTARA
        |--------------------------------------------------------------------------
        */

        Route::get('/access/grant', [AccessPermissionController::class, 'create'])
            ->name('access.grant.create');

        Route::post('/access/grant', [AccessPermissionController::class, 'store'])
            ->name('access.grant.store');
    });

});