<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAnswer;
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
                'attempt' => 'Attempt limit reached.',
            ]);
        }

        return DB::transaction(function () use ($quiz, $userId, $attemptCount) {

            return QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'user_id' => $userId,
                'attempt_no' => $attemptCount + 1,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        });
    }

    public function submit(QuizAttempt $attempt): QuizAttempt
    {
        if ($attempt->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'attempt' => 'Attempt already submitted.',
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

    public function storePublic(Quiz $quiz, array $data): QuizAttempt
    {
        $answers = $data['answers'] ?? [];

        return DB::transaction(function () use ($quiz, $answers) {
            // 1) Start attempt
            $attempt = $this->start($quiz);

            // 2) Save answers
            foreach ($answers as $a) {
                $questionId = $a['question_id'] ?? null;
                if (! $questionId) {
                    continue;
                }

                QuizAnswer::updateOrCreate(
                    [
                        'attempt_id' => $attempt->id,
                        'question_id' => $questionId,
                    ],
                    [
                        // simpan semua payload answer ke JSON
                        'answer_json' => $a,
                    ]
                );
            }

            // 3) Submit attempt
            $attempt = $this->submit($attempt);

            return $attempt->fresh();
        });
    }
}
