<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $fillable = [
        'quiz_id',
        'question_type',
        'prompt',
        'prompt_json',
        'points',
        'sort_order',
    ];

    protected $casts = [
        'prompt_json' => 'array',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options()
    {
        return $this->hasMany(QuizQuestionOption::class, 'question_id');
    }

    public function matchingPairs()
    {
        return $this->hasMany(QuizMatchingPair::class)
            ->orderBy('sort_order');
    }
}
