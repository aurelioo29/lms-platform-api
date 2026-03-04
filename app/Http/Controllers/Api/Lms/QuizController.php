<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lms\Quiz\StoreQuizRequest;
use App\Http\Requests\Lms\Quiz\UpdateQuizRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Services\QuizService;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    public function __construct(private QuizService $service) {}

    public function showByLesson(Lesson $lesson)
    {
        // content_json bisa array (cast) atau string, tergantung model cast
        $content = $lesson->content_json;

        if (is_string($content)) {
            $content = json_decode($content, true);
        }

        $quizId = data_get($content, 'quiz_id');

        // Debug (hapus nanti)
        Log::info('showByLesson', [
            'lesson_id' => $lesson->id,
            'content_json' => $content,
            'quiz_id' => $quizId,
        ]);

        if (! $quizId) {
            return response()->json(null, 200);
        }

        $quiz = Quiz::query()
            ->with([
                'questions' => fn ($q) => $q->orderBy('sort_order'),
                'questions.options' => fn ($q) => $q->orderBy('sort_order'),
            ])
            ->find($quizId);

        if (! $quiz) {
            return response()->json(null, 200);
        }

        // Return shape that matches quizPublicSchema EXACTLY
        return response()->json([
            'id' => (int) $quiz->id,
            'title' => (string) $quiz->title,
            'type' => (string) $quiz->type,
            'questions' => $quiz->questions->map(function ($q) {
                return [
                    'id' => (int) $q->id,
                    'question_type' => $q->question_type,
                    'prompt' => (string) $q->prompt,
                    'points' => (int) ($q->points ?? 1),
                    'sort_order' => (int) ($q->sort_order ?? 1),

                    'media_type' => $q->media_type ?? 'none',
                    'media_url' => $q->media_url,

                    'options' => $q->options->map(function ($o) {
                        return [
                            'id' => (int) $o->id,
                            'label' => $o->label,
                            'text' => (string) $o->text,
                        ];
                    })->values(),
                ];
            })->values(),
        ]);
    }

    public function store(StoreQuizRequest $request, Course $course)
    {
        $this->authorize('create', [Quiz::class, $course]);

        $quiz = $this->service->create($course, $request->validated());

        return response()->json($quiz, 201);
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);

        $quiz = $this->service->update($quiz, $request->validated());

        return response()->json($quiz);
    }

    public function destroy(Quiz $quiz)
    {
        $this->authorize('delete', $quiz);

        $this->service->delete($quiz);

        return response()->json(['message' => 'Quiz deleted']);
    }
}
