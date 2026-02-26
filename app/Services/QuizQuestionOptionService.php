<?php

namespace App\Services;

use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;

class QuizQuestionOptionService
{
    public function listByQuestion(QuizQuestion $question)
    {
        return $question->options()
            ->orderBy('sort_order')
            ->get();
    }

    public function create(QuizQuestion $question, array $data): QuizQuestionOption
    {
        // If question type is mcq_single → only 1 correct allowed
        if ($question->question_type === 'mcq_single' && ($data['is_correct'] ?? false)) {
            $question->options()->update(['is_correct' => false]);
        }

        return $question->options()->create([
            'label'      => $data['label'],
            'is_correct' => $data['is_correct'] ?? false,
            'sort_order' => $data['sort_order'] ?? 1,
        ]);
    }

    public function update(QuizQuestionOption $option, array $data): QuizQuestionOption
    {
        $question = $option->question;

        if (
            isset($data['is_correct']) &&
            $data['is_correct'] &&
            $question->question_type === 'mcq_single'
        ) {
            $question->options()->update(['is_correct' => false]);
        }

        $option->update($data);

        return $option;
    }

    public function delete(QuizQuestionOption $option): void
    {
        $option->delete();
    }
}
