<?php

use App\Http\Controllers\Api\Admin\UserManagementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Dev\ActivityLogController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', function () {
    return response()->json([
        'ok' => true,
        'time' => now()->toDateTimeString(),
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/me', [AuthController::class, 'me'])->middleware('auth');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

    // email verification
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->middleware('throttle:2,1');

    // password reset
    Route::post('/forgot-password', [PasswordController::class, 'forgot']);
    Route::post('/reset-password', [PasswordController::class, 'reset']);

    // profile management
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar']);
    Route::patch('/profile/username', [ProfileController::class, 'updateUsername']);
    Route::patch('/profile/email', [ProfileController::class, 'updateEmail']);
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword']);
});

Route::middleware(['auth:sanctum', 'dev.only'])
    ->prefix('dev')
    ->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
        Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show']);
    });

Route::middleware(['auth:sanctum', 'admin.dev'])
    ->prefix('admin')
    ->group(function () {

        // Teachers
        Route::get('/teachers', [UserManagementController::class, 'indexTeachers']);
        Route::post('/teachers', [UserManagementController::class, 'storeTeacher']);
        Route::get('/teachers/{user}', [UserManagementController::class, 'showTeacher']);
        Route::patch('/teachers/{user}', [UserManagementController::class, 'updateTeacher']);
        Route::delete('/teachers/{user}', [UserManagementController::class, 'destroyTeacher']);

        // Students
        Route::get('/students', [UserManagementController::class, 'indexStudents']);
        Route::post('/students', [UserManagementController::class, 'storeStudent']);
        Route::get('/students/{user}', [UserManagementController::class, 'showStudent']);
        Route::patch('/students/{user}', [UserManagementController::class, 'updateStudent']);
        Route::delete('/students/{user}', [UserManagementController::class, 'destroyStudent']);
    });
