<?php

use App\Http\Controllers\Api\Admin\UserManagementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Dev\ActivityLogController;
use App\Http\Controllers\Api\Discussion\CourseDiscussionController;
use App\Http\Controllers\Api\Discussion\DiscussionCommentController;
use App\Http\Controllers\Api\Discussion\ReactionController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\Lms\CourseController;
use App\Http\Controllers\Api\Lms\CourseEnrollmentController;
use App\Http\Controllers\Api\Lms\CourseInstructorController;
use App\Http\Controllers\Api\Lms\CourseModuleController;
use App\Http\Controllers\Api\Lms\LessonAssetController;
use App\Http\Controllers\Api\Lms\LessonController;
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

    // discussions list/create per course
    Route::get('/courses/{course}/discussions', [CourseDiscussionController::class, 'index']);
    Route::post('/courses/{course}/discussions', [CourseDiscussionController::class, 'store']);

    // single discussion
    Route::get('/discussions/{discussion}', [CourseDiscussionController::class, 'show']);
    Route::patch('/discussions/{discussion}', [CourseDiscussionController::class, 'update']);
    Route::delete('/discussions/{discussion}', [CourseDiscussionController::class, 'destroy']);

    // comments
    Route::post('/discussions/{discussion}/comments', [DiscussionCommentController::class, 'store']);
    Route::delete('/comments/{comment}', [DiscussionCommentController::class, 'destroy']);

    // reactions
    Route::post('/reactions/toggle', [ReactionController::class, 'toggle']);
});

Route::middleware(['auth:sanctum', 'dev.only'])
    ->prefix('dev')
    ->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
        Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show']);
    });

// Public-ish (authenticated + verified): list published courses + enroll via key
Route::prefix('courses')
    ->middleware(['auth:sanctum', 'verified'])
    ->group(function () {
        Route::get('/', [CourseController::class, 'index']);
        Route::post('/{course}/enroll', [CourseEnrollmentController::class, 'enrollWithKey']);
    });

// Admin (or admin middleware): manage courses + manual enroll
Route::prefix('admin/courses')
    ->middleware(['auth:sanctum', 'verified', 'admin.dev']) // or your admin middleware
    ->group(function () {
        Route::get('/', [CourseController::class, 'adminIndex']);
        Route::post('/', [CourseController::class, 'store']);
        Route::patch('/{course}', [CourseController::class, 'update']);
        Route::post('/{course}/publish', [CourseController::class, 'publish']);
        Route::post('/{course}/archive', [CourseController::class, 'archive']);

        // course modules
        Route::get('/{course}/modules', [CourseModuleController::class, 'index']);
        Route::post('/{course}/modules', [CourseModuleController::class, 'store']);
        Route::patch('/modules/{module}', [CourseModuleController::class, 'update']);
        Route::delete('/modules/{module}', [CourseModuleController::class, 'destroy']);

        // course lessons
        Route::post('/lessons', [LessonController::class, 'store']);
        Route::put('/lessons/{lesson}', [LessonController::class, 'update']);
        Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy']);

        // lesson assets
        Route::post('/lesson-assets', [LessonAssetController::class, 'store']);
        Route::delete('/lesson-assets/{lessonAsset}', [LessonAssetController::class, 'destroy']);
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


        // assign / manage course instructors
        Route::post('/courses/{course}/instructors', [CourseInstructorController::class, 'store']);
        Route::patch('/courses/instructors/{courseInstructor}', [CourseInstructorController::class, 'update']);
        Route::delete('/courses/instructors/{courseInstructor}', [CourseInstructorController::class, 'destroy']);
    });
