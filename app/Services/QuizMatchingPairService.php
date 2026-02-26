<?php

namespace App\Services;

use App\Models\QuizMatchingPair;
use App\Models\QuizQuestion;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class QuizMatchingPairService
{

    public function list(QuizQuestion $question)
    {
        return $question->matchingPairs()->get();
    }


    public function create(QuizQuestion $question, array $data): QuizMatchingPair
    {
        $this->ensureMatchingType($question);

        return DB::transaction(function () use ($question, $data) {

            $order = $data['sort_order']
                ?? $this->nextSortOrder($question);

            return QuizMatchingPair::create([
                'question_id' => $question->id,
                'left_text'   => $data['left_text'],
                'right_text'  => $data['right_text'],
                'sort_order'  => $order,
            ]);
        });
    }

    public function update(QuizMatchingPair $pair, array $data): QuizMatchingPair
    {
        $pair->update([
            'left_text'  => $data['left_text'] ?? $pair->left_text,
            'right_text' => $data['right_text'] ?? $pair->right_text,
            'sort_order' => $data['sort_order'] ?? $pair->sort_order,
        ]);

        return $pair;
    }

    public function delete(QuizMatchingPair $pair): void
    {
        $pair->delete();
    }

    // make sure the question is of type 'matching'
    private function ensureMatchingType(QuizQuestion $question): void
    {
        if ($question->question_type !== 'matching') {
            throw ValidationException::withMessages([
                'question' => 'Matching pairs only allowed for matching questions.'
            ]);
        }
    }

    private function nextSortOrder(QuizQuestion $question): int
    {
        return (int) $question->matchingPairs()->max('sort_order') + 1;
    }
}
