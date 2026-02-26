<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizQuestion;

class QuizQuestionService
{
    public function listByQuiz(Quiz $quiz)
    {
        return $quiz->questions()
            ->orderBy('sort_order')
            ->get();
    }

    public function create(Quiz $quiz, array $data): QuizQuestion
    {
        return $quiz->questions()->create([
            'question_type' => $data['question_type'],
            'prompt'        => $data['prompt'],
            'prompt_json'   => $data['prompt_json'] ?? null,
            'points'        => $data['points'] ?? 1,
            'sort_order'    => $data['sort_order'] ?? 1,
        ]);
    }

    public function update(QuizQuestion $question, array $data): QuizQuestion
    {
        $question->update($data);

        return $question;
    }

    public function delete(QuizQuestion $question): void
    {
        $question->delete();
    }
}
