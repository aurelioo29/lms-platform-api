<?php

use App\Http\Controllers\Api\Admin\UserManagementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Dev\ActivityLogController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\Lms\CourseController;
use App\Http\Controllers\Api\Lms\CourseEnrollmentController;
use App\Http\Controllers\Api\Lms\CourseInstructorController;
use App\Http\Controllers\Api\Lms\CourseModuleController;
use App\Http\Controllers\Api\Lms\CourseProgressSummaryController;
use App\Http\Controllers\Api\Lms\LessonAssetController;
use App\Http\Controllers\Api\Lms\LessonController;
use App\Http\Controllers\Api\Lms\LessonProgressController;
use App\Http\Controllers\Api\Lms\QuizAnswerController;
use App\Http\Controllers\Api\Lms\QuizAttemptController;
use App\Http\Controllers\Api\Lms\QuizController;
use App\Http\Controllers\Api\Lms\QuizMatchingPairController;
use App\Http\Controllers\Api\Lms\QuizQuestionController;
use App\Http\Controllers\Api\Lms\QuizQuestionOptionController;
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

        // lesson progress
        Route::post('/{lesson}/start', [LessonProgressController::class, 'start']);
        Route::post('/{lesson}/complete', [LessonProgressController::class, 'complete']);

        // course progress summary
        Route::get('/{course}/progress', [CourseProgressSummaryController::class, 'show']);

        // quizzes
        Route::post('/{course}/quizzes', [QuizController::class, 'store']);
        Route::put('/quizzes/{quiz}', [QuizController::class, 'update']);
        Route::delete('/quizzes/{quiz}', [QuizController::class, 'destroy']);

        // quiz questions
        Route::get('/quizzes/{quiz}/questions', [QuizQuestionController::class, 'index']);
        Route::post('/quizzes/{quiz}/questions', [QuizQuestionController::class, 'store']);
        Route::put('/quiz-questions/{quizQuestion}', [QuizQuestionController::class, 'update']);
        Route::delete('/quiz-questions/{quizQuestion}', [QuizQuestionController::class, 'destroy']);

        // question options
        Route::get('/quiz-questions/{question}/options', [QuizQuestionOptionController::class, 'index']);
        Route::post('/quiz-questions/{question}/options', [QuizQuestionOptionController::class, 'store']);
        Route::put('/quiz-question-options/{option}', [QuizQuestionOptionController::class, 'update']);
        Route::delete('/quiz-question-options/{option}', [QuizQuestionOptionController::class, 'destroy']);

        // matching pairs
        Route::get('{question}/matching-pairs', [QuizMatchingPairController::class, 'index']);
        Route::post('{question}/matching-pairs', [QuizMatchingPairController::class, 'store']);
        Route::patch('matching-pairs/{pair}', [QuizMatchingPairController::class, 'update']);
        Route::delete('matching-pairs/{pair}', [QuizMatchingPairController::class, 'destroy']);

        // quiz attempts
        Route::prefix('quizzes')->group(function () {
            Route::get('{quiz}/attempts', [QuizAttemptController::class, 'index']);
            Route::post('{quiz}/attempts/start', [QuizAttemptController::class, 'start']);
            Route::post('attempts/{attempt}/submit', [QuizAttemptController::class, 'submit']);
        });

        Route::prefix('quiz-attempts')->group(function () {
            Route::post('{attempt}/questions/{question}/answer', [QuizAnswerController::class, 'save']);
        });

        Route::prefix('quiz-answers')->group(function () {
            Route::post('{answer}/grade', [QuizAnswerController::class, 'grade']);
        });
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
