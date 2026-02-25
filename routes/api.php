<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Dev\ActivityLogController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\Lms\CourseController;
use App\Http\Controllers\Api\Lms\CourseEnrollmentController;
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

/*
|--------------------------------------------------------------------------
| LMS - COURSE ENROLLMENT
|--------------------------------------------------------------------------
*/
Route::prefix('courses')
    ->middleware(['auth:sanctum', 'verified'])
    ->group(function () {

        // student enroll via enroll key
        Route::post('/{course}/enroll', [CourseEnrollmentController::class, 'enrollWithKey']);

        // admin / teacher manual enroll student
        Route::post('/{course}/manual-enroll', [CourseEnrollmentController::class, 'manualEnroll']);

        // course management (admin)
        Route::get('/', [CourseController::class, 'index']);
        Route::post('/', [CourseController::class, 'store']);
        Route::patch('/{course}', [CourseController::class, 'update']);
        Route::post('/{course}/publish', [CourseController::class, 'publish']);
        Route::post('/{course}/archive', [CourseController::class, 'archive']);
    });


// ✅ Developer-only area
Route::prefix('dev')
    ->middleware(['auth:sanctum', 'dev.only'])
    ->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
        Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show']);
    });
