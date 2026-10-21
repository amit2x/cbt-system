<?php

// app/Http/Controllers/Admin/ResultModerationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Result;
use App\Models\StudentAnswer;
use App\Services\Analytics\ResultAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultModerationController extends Controller
{
    protected $resultAnalysisService;

    public function __construct(ResultAnalysisService $resultAnalysisService)
    {
        $this->resultAnalysisService = $resultAnalysisService;
        $this->middleware('auth');
        $this->middleware('role:superadmin');

        // Use a middleware closure to ensure it runs during the request lifecycle
        $this->middleware(function ($request, $next) {
            activity()->disableLogging();

            return $next($request);
        });
    }

    /**
     * List all submissions for an exam (with draft/published filter).
     */
    public function index(Exam $exam, Request $request)
    {
        $query = Result::where('exam_id', $exam->id)
            ->with(['student', 'studentExam']);

        if ($request->filled('status')) {
            match ($request->status) {
                'published' => $query->where('is_published', true),
                'draft' => $query->where('is_published', false),
                default => null,
            };
        }

        $results = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => Result::where('exam_id', $exam->id)->count(),
            'published' => Result::where('exam_id', $exam->id)->where('is_published', true)->count(),
            'draft' => Result::where('exam_id', $exam->id)->where('is_published', false)->count(),
        ];

        return view('admin.results.moderation.index', compact('exam', 'results', 'stats'));
    }

    /**
     * Show a single student's answer sheet for moderation.
     */
    public function show(Result $result)
    {
        $result->load([
            'exam',
            'student',
            'studentExam.answers.question.options',
        ]);

        return view('admin.results.moderation.show', compact('result'));
    }

    /**
     * Update a single student's answer (admin correction).
     */
    // public function updateAnswer(Request $request, StudentAnswer $answer)
    // {
    //     $request->validate([
    //         'selected_options' => 'nullable|array',
    //         'selected_options.*' => 'integer',
    //         'answer_text' => 'nullable|string',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         $question = $answer->question;

    //         // Update answer based on type
    //         if ($question->type === 'numerical') {
    //             $answer->answer_text = $request->answer_text;
    //             $answer->selected_options = null;
    //             $answer->is_correct = $this->checkNumericalAnswer($question, $request->answer_text);
    //         } else {
    //             $answer->selected_options = $request->selected_options ?? [];
    //             $answer->answer_text = null;
    //             $answer->is_correct = $this->checkChoiceAnswer($question, $request->selected_options ?? []);
    //         }

    //         // Recompute marks
    //         $answer->marks_obtained = $answer->is_correct
    //             ? ($question->marks ?? 1)
    //             : -1 * ($question->negative_marks ?? 0);

    //         $answer->last_saved_at = now();
    //         $answer->save();

    //         // Recalculate result
    //         $result = $answer->studentExam->result;
    //         $result->recalculate();

    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Answer updated and result recalculated.',
    //             'data' => [
    //                 'is_correct' => $answer->is_correct,
    //                 'marks_obtained' => $answer->marks_obtained,
    //                 'percentage' => $result->percentage,
    //             ],
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // app/Http/Controllers/Admin/ResultModerationController.php

    // public function updateAnswer(Request $request, StudentAnswer $answer)
    // {
    //     $request->validate([
    //         'selected_options' => 'nullable|array',
    //         'selected_options.*' => 'integer',
    //         'answer_text' => 'nullable|string',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         $question = $answer->question;

    //         if ($question->type === 'numerical') {
    //             $answer->answer_text = $request->answer_text;
    //             $answer->selected_options = null;
    //             $answer->is_correct = $this->checkNumericalAnswer($question, $request->answer_text);
    //         } else {
    //             $answer->selected_options = $request->selected_options ?? [];
    //             $answer->answer_text = null;
    //             $answer->is_correct = $this->checkChoiceAnswer($question, $request->selected_options ?? []);
    //         }

    //         $answer->marks_obtained = $answer->is_correct
    //             ? ($question->marks ?? 1)
    //             : -1 * ($question->negative_marks ?? 0);
    //         $answer->last_saved_at = now();
    //         $answer->save();

    //         // ✅ Recalculate full result (incl. analytics, rank, percentile)
    //         $result = $answer->studentExam->result;
    //         $result->recalculate();

    //         // ✅ Refresh relations so response has updated values
    //         $result->refresh();

    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Answer updated and result recalculated.',
    //             'data' => [
    //                 'is_correct' => $answer->is_correct,
    //                 'marks_obtained' => $answer->marks_obtained,
    //                 'percentage' => $result->percentage,
    //                 'accuracy_percentage' => $result->accuracy_percentage,
    //                 'rank' => $result->rank,
    //                 'percentile' => $result->percentile,
    //                 'total_participants' => $result->total_participants,
    //             ],
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function updateAnswer(Request $request, StudentAnswer $answer)
    {
        // base case
        if ($answer->studentExam->result->is_published) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot update answer for a published result.',
            ], 403);
        }

        $request->validate([
            'selected_options' => 'nullable|array',
            'selected_options.*' => 'integer',
            'answer_text' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $question = $answer->question;

            if ($question->type === 'numerical') {
                $answer->answer_text = $request->answer_text;
                $answer->selected_options = null;
                $answer->is_correct = $this->checkNumericalAnswer($question, $request->answer_text);
            } else {
                $answer->selected_options = $request->selected_options ?? [];
                $answer->answer_text = null;
                $answer->is_correct = $this->checkChoiceAnswer($question, $request->selected_options ?? []);
            }

            $answer->marks_obtained = $answer->is_correct
                ? ($question->marks ?? 1)
                : -1 * ($question->negative_marks ?? 0);
            $answer->last_saved_at = now();
            $answer->save();

            // Recalculate this student's result (analytics)
            $result = $answer->studentExam->result;
            $result->recalculate();
            $result->refresh();

            // ✅ Recalculate ranks for ALL students in this exam
            $this->resultAnalysisService->recalculateRanks($result->exam_id);

            $result->refresh();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Answer updated and ranks recalculated.',
                'data' => [
                    'is_correct' => $answer->is_correct,
                    'marks_obtained' => $answer->marks_obtained,
                    'percentage' => $result->percentage,
                    'accuracy_percentage' => $result->accuracy_percentage,
                    'rank' => $result->rank,
                    'percentile' => $result->percentile,
                    'total_participants' => $result->total_participants,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk publish results for an exam.
     */
    // app/Http/Controllers/Admin/ResultModerationController.php

    /**
     * Bulk publish results for an exam.
     */
    public function bulkPublish(Request $request, Exam $exam)
    {
        $request->validate([
            'result_ids' => 'nullable|array',
            'result_ids.*' => 'exists:results,id',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $query = Result::where('exam_id', $exam->id);

            if (! empty($request->result_ids)) {
                $query->whereIn('id', $request->result_ids);
            }

            $count = $query->update([
                'is_published' => true,
                'is_draft' => false,
                'published_at' => now(),
                'published_by' => auth()->id(),
                'moderation_notes' => $request->notes,
            ]);

            // ✅ Recalculate ranks for the entire exam after publishing
            // (so rank/percentile reflect the final published state)
            $this->resultAnalysisService->recalculateRanks($exam->id);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "{$count} result(s) published successfully.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Publish a single result.
     */
    public function publish(Request $request, Result $result)
    {
        DB::beginTransaction();
        try {
            $result->publish(auth()->id(), $request->input('notes'));

            // ✅ Refresh ranks for the exam
            $this->resultAnalysisService->recalculateRanks($result->exam_id);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Result published successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Unpublish results.
     */
    public function bulkUnpublish(Request $request, Exam $exam)
    {
        $request->validate([
            'result_ids' => 'required|array',
            'result_ids.*' => 'exists:results,id',
        ]);

        $count = Result::where('exam_id', $exam->id)
            ->whereIn('id', $request->result_ids)
            ->update([
                'is_published' => false,
                'is_draft' => true,
                'published_at' => null,
                'published_by' => null,
                'last_modified_by' => auth()->id(),
            ]);

        return response()->json([
            'status' => 'success',
            'message' => "{$count} result(s) unpublished.",
        ]);
    }

    /**
     * Publish a single result.
     */
    // public function publish(Request $request, Result $result)
    // {
    //     $result->publish(auth()->id(), $request->input('notes'));

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Result published successfully.',
    //     ]);
    // }
    public function recalculateRanks(Exam $exam)
    {
        $this->resultAnalysisService->recalculateRanks($exam->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Ranks recalculated for all candidates in this exam.',
        ]);
    }

    // ==================== HELPERS ====================

    protected function checkChoiceAnswer($question, array $selectedOptions): bool
    {
        $correctIds = $question->options()->where('is_correct', true)->pluck('id')->sort()->values()->toArray();
        sort($selectedOptions);

        return $selectedOptions === $correctIds;
    }

    protected function checkNumericalAnswer($question, $value): bool
    {
        $correctOption = $question->options()->where('is_correct', true)->first();
        if (! $correctOption) {
            return false;
        }

        return abs((float) $value - (float) $correctOption->option_text) <= 0.001;
    }
}
