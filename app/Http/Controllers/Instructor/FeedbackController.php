<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Feedback;
use App\Models\FeedbackResponse;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeedbackController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:instructor');
    }

    /**
     * Display a listing of feedback sessions.
     */
    public function index()
    {
        $instructor = auth()->user();

        $feedbacks = Feedback::where('instructor_id', $instructor->id)
            ->with(['batch.course'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('instructor.feedback.index', compact('feedbacks'));
    }

    /**
     * Show feedback creation form.
     */
    public function create()
    {
        $instructor = auth()->user();

        $batches = Batch::where('instructor_id', $instructor->id)
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->with('course')
            ->get();

        return view('instructor.feedback.create', compact('batches'));
    }

    /**
     * Launch feedback.
     */
    public function launch(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'type' => 'required|in:quick,detailed',
        ]);

        try {
            $instructor = auth()->user();
            $batch = Batch::findOrFail($request->batch_id);

            // Verify instructor owns this batch
            if ($batch->instructor_id !== $instructor->id) {
                return redirect()->back()->with('error', 'You are not authorized to create feedback for this batch.');
            }

            $questions = [];
            if ($request->type === 'detailed' && $request->has('questions')) {
                $questions = array_filter($request->questions, function ($q) {
                    return ! empty(trim($q));
                });

                if (empty($questions)) {
                    return redirect()->back()->with('error', 'Please add at least one question for detailed feedback.');
                }
            }

            // Generate unique code
            $code = strtoupper(Str::random(6));

            // Check if code is unique
            while (Feedback::where('code', $code)->exists()) {
                $code = strtoupper(Str::random(6));
            }

            // Create feedback
            $feedback = Feedback::create([
                'instructor_id' => $instructor->id,
                'batch_id' => $request->batch_id,
                'course_id' => $batch->course_id,
                'type' => $request->type,
                'questions' => $questions,
                'code' => $code,
                'status' => 'active',
                'expires_at' => now()->addDays(7),
            ]);

            return redirect()->route('instructor.feedback.show', $feedback)
                ->with('success', 'Feedback launched successfully! Share this code with your students: '.$code);

        } catch (\Exception $e) {
            \Log::error('Feedback launch error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Error launching feedback: '.$e->getMessage());
        }
    }

    /**
     * Show feedback details.
     */
    public function show(Feedback $feedback)
    {
        $instructor = auth()->user();

        // Verify instructor owns this feedback
        if ($feedback->instructor_id !== $instructor->id) {
            abort(403);
        }

        $feedback->load(['batch.course']);

        $responsesCount = FeedbackResponse::where('feedback_id', $feedback->id)->count();

        $shareUrl = url('/student/feedback/'.$feedback->code);

        return view('instructor.feedback.show', compact('feedback', 'responsesCount', 'shareUrl'));
    }

    /**
     * Show feedback responses.
     */
    public function responses(Feedback $feedback)
    {
        $instructor = auth()->user();

        // Verify instructor owns this feedback
        if ($feedback->instructor_id !== $instructor->id) {
            abort(403);
        }

        $feedback->load(['batch.course', 'batch.students']);

        $responses = FeedbackResponse::where('feedback_id', $feedback->id)
            ->with('student')
            ->get();

        $summary = [
            'total_responses' => $responses->count(),
            'average_rating' => round($responses->avg('rating'), 2),
            'rating_distribution' => $responses->groupBy('rating')->map->count()->toArray(),
            'comments' => $responses->whereNotNull('comment')->map(function ($r) {
                return ['text' => $r->comment, 'rating' => $r->rating];
            })->values()->toArray(),
            'responses_by_question' => $this->getResponsesByQuestion($feedback, $responses),
        ];

        return view('instructor.feedback.responses', compact('feedback', 'responses', 'summary'));
    }

    /**
     * Close feedback.
     */
    public function close(Feedback $feedback)
    {
        if ($feedback->instructor_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $feedback->update(['status' => 'closed']);

        return redirect()->route('instructor.feedback.show', $feedback)
            ->with('success', 'Feedback closed successfully');
    }

    /**
     * Export responses.
     */
    public function exportResponses(Feedback $feedback)
    {
        if ($feedback->instructor_id !== auth()->id()) {
            abort(403);
        }

        $responses = FeedbackResponse::where('feedback_id', $feedback->id)
            ->with('student')
            ->get();

        $filename = "feedback_{$feedback->id}_".now()->format('Y-m-d').'.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        // Add headers
        $headers = ['Student Name', 'Email', 'Submitted At'];
        if ($feedback->type === 'quick') {
            $headers[] = 'Rating';
            $headers[] = 'Comment';
        } else {
            foreach ($feedback->questions as $index => $question) {
                $headers[] = 'Q'.($index + 1).': '.$question;
            }
            $headers[] = 'Rating';
        }
        fputcsv($handle, $headers);

        // Add data
        foreach ($responses as $response) {
            $row = [
                $response->student->name,
                $response->student->email,
                $response->submitted_at->format('Y-m-d H:i:s'),
            ];

            if ($feedback->type === 'quick') {
                $row[] = $response->rating;
                $row[] = $response->comment;
            } else {
                if ($response->answers) {
                    foreach ($response->answers as $answer) {
                        $row[] = $answer;
                    }
                } else {
                    for ($i = 0; $i < count($feedback->questions); $i++) {
                        $row[] = '';
                    }
                }
                $row[] = $response->rating;
            }

            fputcsv($handle, $row);
        }

        fclose($handle);
        exit;
    }

    /**
     * Get responses by question for detailed feedback.
     */
    protected function getResponsesByQuestion($feedback, $responses)
    {
        if ($feedback->type !== 'detailed' || ! $feedback->questions) {
            return [];
        }

        $responsesByQuestion = [];
        foreach ($feedback->questions as $index => $question) {
            $answers = [];
            foreach ($responses as $response) {
                if ($response->answers && isset($response->answers[$index])) {
                    $answers[] = $response->answers[$index];
                }
            }
            $responsesByQuestion[] = [
                'question' => $question,
                'responses' => $answers,
            ];
        }

        return $responsesByQuestion;
    }

    /**
     * Delete feedback.
     */
    public function destroy(Feedback $feedback)
    {
        if ($feedback->instructor_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        // Delete all responses first
        $feedback->responses()->delete();
        $feedback->delete();

        return redirect()->route('instructor.feedback.index')
            ->with('success', 'Feedback deleted successfully');
    }
}
