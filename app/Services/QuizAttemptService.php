<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizAttemptService
{

    public function start(Quiz $quiz): QuizAttempt
    {
        $userId = Auth::id();

        // cek limit attempt
        $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $userId)
            ->count();

        if (
            $quiz->attempt_limit &&
            $attemptCount >= $quiz->attempt_limit
        ) {

            throw ValidationException::withMessages([
                'attempt' => 'Attempt limit reached.'
            ]);
        }

        return DB::transaction(function () use ($quiz, $userId, $attemptCount) {

            return QuizAttempt::create([
                'quiz_id'    => $quiz->id,
                'user_id'    => $userId,
                'attempt_no' => $attemptCount + 1,
                'status'     => 'in_progress',
                'started_at' => now(),
            ]);
        });
    }

    public function submit(QuizAttempt $attempt): QuizAttempt
    {
        if ($attempt->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'attempt' => 'Attempt already submitted.'
            ]);
        }

        $attempt->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return $attempt;
    }

    public function myAttempts(Quiz $quiz)
    {
        return QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    }
}
