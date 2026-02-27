<?php

namespace App\Services;

use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class QuizAnswerService
{

    public function save(QuizAttempt $attempt, QuizQuestion $question, array $data): QuizAnswer
    {

        // attempt harus masih berjalan
        if ($attempt->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'attempt' => 'Attempt already submitted.'
            ]);
        }

        // hanya owner attempt
        if ($attempt->user_id !== Auth::id()) {
            abort(403);
        }

        return QuizAnswer::updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ],
            [
                'answer_json' => $data['answer_json'],
            ]
        );
    }


    public function autoGrade(QuizAnswer $answer): QuizAnswer
    {
        $question = $answer->question;

        // contoh sederhana (MCQ)
        if ($question->question_type === 'mcq_single') {

            $correctOption = $question->options()
                ->where('is_correct', true)
                ->first();

            $selected = $answer->answer_json['option_id'] ?? null;

            $isCorrect = $correctOption
                && $correctOption->id == $selected;

            $answer->update([
                'is_correct' => $isCorrect,
                'points_awarded' => $isCorrect
                    ? $question->points
                    : 0,
            ]);
        }

        return $answer;
    }


    public function gradeManually(QuizAnswer $answer, int $points): QuizAnswer
    {

        $answer->update([
            'points_awarded' => $points,
            'is_correct' => null,
            'graded_by' => Auth::id(),
            'graded_at' => now(),
        ]);

        return $answer;
    }
}
