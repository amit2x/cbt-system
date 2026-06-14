<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamSectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Store a newly created section.
     */
    public function store(Request $request, Exam $exam)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:1',
            'randomize_questions' => 'boolean'
        ]);

        $section = $exam->sections()->create([
            'title' => $request->title,
            'description' => $request->description,
            'duration_minutes' => $request->duration_minutes,
            'randomize_questions' => $request->boolean('randomize_questions'),
            'order' => $exam->sections()->max('order') + 1
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Section added successfully',
                'section' => $section->load('questions')
            ]);
        }

        return redirect()->route('admin.exams.show', $exam)
                        ->with('success', 'Section added successfully.');
    }

    /**
     * Update the specified section.
     */
    public function update(Request $request, Exam $exam, ExamSection $section)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:1',
            'randomize_questions' => 'boolean'
        ]);

        $section->update($request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Section updated successfully'
            ]);
        }

        return redirect()->route('admin.exams.show', $exam)
                        ->with('success', 'Section updated successfully.');
    }

    /**
     * Remove the specified section.
     */
    public function destroy(Request $request, Exam $exam, ExamSection $section)
    {
        // Check if section has questions
        if ($section->questions()->count() > 0) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete section with questions. Move or remove questions first.'
                ], 422);
            }
            return back()->with('error', 'Cannot delete section with questions. Move or remove questions first.');
        }

        $section->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Section deleted successfully'
            ]);
        }

        return redirect()->route('admin.exams.show', $exam)
                        ->with('success', 'Section deleted successfully.');
    }

    /**
     * Update sections order.
     */
    public function updateOrder(Request $request, Exam $exam)
    {
        $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:exam_sections,id',
            'sections.*.order' => 'required|integer'
        ]);

        foreach ($request->sections as $sectionData) {
            ExamSection::where('id', $sectionData['id'])
                       ->where('exam_id', $exam->id)
                       ->update(['order' => $sectionData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Section order updated'
        ]);
    }

    /**
     * Move questions between sections.
     */
    public function moveQuestions(Request $request, Exam $exam)
    {
        $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:exam_questions,id',
            'target_section_id' => 'nullable|exists:exam_sections,id'
        ]);

        foreach ($request->question_ids as $questionId) {
            DB::table('exam_questions')
              ->where('id', $questionId)
              ->update(['section_id' => $request->target_section_id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Questions moved successfully'
        ]);
    }
}
