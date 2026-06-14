<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\FeedbackResponse;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:student');
    }

    /**
     * Show feedback form
     */
    public function show($code)
    {
        $feedback = Feedback::where('code', $code)
            ->with(['batch.course', 'instructor'])
            ->firstOrFail();

        // Check if feedback is still active
        if (! $feedback->isActive()) {
            return view('student.feedback.expired', compact('feedback'));
        }

        // Check if student has already responded
        $hasResponded = $feedback->hasResponded(auth()->id());

        if ($hasResponded) {
            return view('student.feedback.already-responded', compact('feedback'));
        }

        return view('student.feedback.show', compact('feedback'));
    }

    /**
     * Submit feedback response
     */
    /**
     * Submit feedback response via AJAX
     */
    public function respond(Request $request, $code)
    {
        try {
            Log::info('Feedback submission received', [
                'code' => $code,
                'student_id' => auth()->id(),
                'data' => $request->all(),
            ]);

            $feedback = Feedback::where('code', $code)->firstOrFail();

            // Check if feedback is still active
            if (! $feedback->isActive()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This feedback session has expired.',
                ], 400);
            }

            // Check if already responded
            if ($feedback->hasResponded(auth()->id())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have already submitted feedback for this session',
                ], 400);
            }

            // Validate based on feedback type
            if ($feedback->type === 'quick') {
                $validator = validator($request->all(), [
                    'rating' => 'required|integer|min:1|max:5',
                    'comment' => 'nullable|string|max:500',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Please select a rating.',
                    ], 422);
                }

                // Create response
                $response = FeedbackResponse::create([
                    'feedback_id' => $feedback->id,
                    'student_id' => auth()->id(),
                    'answers' => null,
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                    'submitted_at' => now(),
                ]);

                Log::info('Quick feedback submitted', ['response_id' => $response->id]);

            } else {
                // Detailed feedback
                $validator = validator($request->all(), [
                    'answers' => 'required|array',
                    'answers.*' => 'required|string|max:1000',
                    'rating' => 'nullable|integer|min:1|max:5',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Please answer all questions.',
                    ], 422);
                }

                // Create response
                $response = FeedbackResponse::create([
                    'feedback_id' => $feedback->id,
                    'student_id' => auth()->id(),
                    'answers' => $request->answers,
                    'rating' => $request->rating,
                    'comment' => null,
                    'submitted_at' => now(),
                ]);

                Log::info('Detailed feedback submitted', ['response_id' => $response->id]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Thank you for your feedback!',
            ]);

        } catch (\Exception $e) {
            Log::error('Feedback submission error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'code' => $code,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit feedback. Please try again.',
            ], 500);
        }
    }

    /**
     * Show feedback history for student
     */
    public function history()
    {
        $studentId = auth()->id();

        $responses = FeedbackResponse::where('student_id', $studentId)
            ->with(['feedback.batch.course', 'feedback.instructor'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => FeedbackResponse::where('student_id', $studentId)->count(),
            'average_rating' => FeedbackResponse::where('student_id', $studentId)
                ->whereNotNull('rating')
                ->avg('rating'),
            'recent' => FeedbackResponse::where('student_id', $studentId)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
        ];

        return view('student.feedback.history', compact('responses', 'stats'));
    }

    /**
     * Get pending feedback for student
     */
    public function pending()
    {
        $studentId = auth()->id();
        $batchIds = auth()->user()->batches()
            ->wherePivot('status', 'active')
            ->pluck('batches.id');

        $pendingFeedbacks = Feedback::whereIn('batch_id', $batchIds)
            ->where('status', 'active')
            ->whereDoesntHave('responses', function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            })
            ->with(['batch.course', 'instructor'])
            ->get();

        return view('student.feedback.pending', compact('pendingFeedbacks'));
    }

    /**
     * Get feedback details via AJAX
     */
    public function details(Feedback $feedback)
    {
        // Check if student has access to this feedback
        $studentId = auth()->id();
        $inBatch = auth()->user()->batches()
            ->where('batches.id', $feedback->batch_id)
            ->exists();

        if (! $inBatch) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $response = FeedbackResponse::where('feedback_id', $feedback->id)
            ->where('student_id', $studentId)
            ->first();

        return $this->successResponse([
            'feedback' => [
                'id' => $feedback->id,
                'type' => $feedback->type,
                'questions' => $feedback->questions,
                'expires_at' => $feedback->expires_at?->format('Y-m-d H:i:s'),
            ],
            'my_response' => $response ? [
                'answers' => $response->answers,
                'rating' => $response->rating,
                'comment' => $response->comment,
                'submitted_at' => $response->submitted_at->format('Y-m-d H:i:s'),
            ] : null,
        ]);
    }
}
