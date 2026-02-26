<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizMatchingPair extends Model
{
    use HasFactory;

    protected $table = 'quiz_matching_pairs';

    protected $fillable = [
        'question_id',
        'left_text',
        'right_text',
        'sort_order',
    ];

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}
