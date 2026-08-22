<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Module;
use App\Models\QuestionCategory;
use App\Models\QuestionOption;
use App\Http\Requests\Question\StoreQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Http\Requests\Question\ImportQuestionsRequest;
use App\Repositories\Contracts\QuestionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Imports\QuestionsImport;
use App\Exports\QuestionsExport;
use Maatwebsite\Excel\Facades\Excel;

class QuestionController extends Controller
{
    protected $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of questions.
     */
    public function index(Request $request)
    {
        $query = Question::with(['creator', 'subject', 'categories', 'options']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('difficulty')) {
            $query->difficulty($request->difficulty);
        }

        if ($request->filled('subject_id')) {
            $query->forSubject($request->subject_id);
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('question_categories.id', $request->category_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $questions = $query->latest()->paginate(15);

        $subjects = Module::where('status', 'active')->get();
        $categories = QuestionCategory::active()->get();
        $types = ['single_choice' => 'Single Choice', 'multiple_choice' => 'Multiple Choice', 'numerical' => 'Numerical'];
        $difficulties = ['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'];

        return view('admin.questions.index', compact('questions', 'subjects', 'categories', 'types', 'difficulties'));
    }

    /**
     * Show form for creating new question.
     */
    public function create()
    {
        $subjects = Module::where('status', 'active')->get();
        $categories = QuestionCategory::active()->get();
        $types = [
            'single_choice' => 'Single Choice',
            'multiple_choice' => 'Multiple Choice',
            'numerical' => 'Numerical'
        ];
        $difficulties = [
            'easy' => 'Easy',
            'medium' => 'Medium',
            'hard' => 'Hard'
        ];

        return view('admin.questions.create', compact('subjects', 'categories', 'types', 'difficulties'));
    }

    /**
     * Store a newly created question with image support.
     */
    public function store(StoreQuestionRequest $request)
    {
        try {
            $questionData = $request->except(['options', 'correct_numerical', 'tolerance', 'question_image']);
            $options = $request->input('options', []);
            
            // Handle question image upload
            if ($request->hasFile('question_image')) {
                $imagePath = $request->file('question_image')->store('questions/images', 'public');
                $questionData['question_image'] = $imagePath;
                $questionData['question_image_alt'] = $request->question_image_alt;
            }

            // Process options - FIX: Properly handle uploaded files
            if (!empty($options)) {
                foreach ($options as $index => &$option) {
                    // Handle option image upload - FIX: Check if option_image is an UploadedFile
                    if (isset($option['option_image'])) {
                        // Check if it's an UploadedFile object or an array containing it
                        $imageFile = null;
                        
                        if ($option['option_image'] instanceof \Illuminate\Http\UploadedFile) {
                            $imageFile = $option['option_image'];
                        } elseif (is_array($option['option_image']) && isset($option['option_image']['UploadedFile'])) {
                            // Handle nested UploadedFile from JSON request
                            $imageFile = $option['option_image']['UploadedFile'];
                        } elseif (is_array($option['option_image']) && isset($option['option_image']['tmp_name'])) {
                            // Handle array representation of file
                            $imageFile = $option['option_image'];
                        }
                        
                        // If we have a valid file, store it
                        if ($imageFile instanceof \Illuminate\Http\UploadedFile) {
                            $optionImagePath = $imageFile->store('questions/options', 'public');
                            $option['option_image'] = $optionImagePath;
                        } else {
                            // If it's not a file, remove it from data
                            unset($option['option_image']);
                        }
                    }
                    
                    // Set default values
                    $option['order'] = $option['order'] ?? $index;
                    $option['is_correct'] = isset($option['is_correct']) ? (int)$option['is_correct'] : 0;
                    
                    // Ensure option_text is not null
                    $option['option_text'] = $option['option_text'] ?? null;
                }
            }
            
            // Handle numerical question
            if ($request->type === 'numerical') {
                $options = [
                    [
                        'option_text' => $request->correct_numerical,
                        'is_correct' => true,
                        'order' => 0
                    ]
                ];
            }
            
            // Create question using repository
            $question = $this->questionRepository->createWithOptions($questionData, $options);
            
            return redirect()->route('admin.questions.index')
                            ->with('success', 'Question created successfully.');
                            
        } catch (\Exception $e) {
            Log::error('Question creation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return back()->with('error', 'Error creating question: ' . $e->getMessage())
                         ->withInput();
        }
    }

    /**
     * Display the specified question.
     */
    public function show(Question $question)
    {
        $question->load(['creator', 'subject', 'categories', 'options']);

        return view('admin.questions.show', compact('question'));
    }

    /**
     * Show form for editing question.
     */
    public function edit(Question $question)
    {
        $subjects = Module::where('status', 'active')->get();
        $categories = QuestionCategory::active()->get();
        $types = [
            'single_choice' => 'Single Choice',
            'multiple_choice' => 'Multiple Choice',
            'numerical' => 'Numerical'
        ];
        $difficulties = [
            'easy' => 'Easy',
            'medium' => 'Medium',
            'hard' => 'Hard'
        ];

        $question->load(['categories', 'options']);

        return view('admin.questions.edit', compact('question', 'subjects', 'categories', 'types', 'difficulties'));
    }

    /**
     * Update the specified question with image support.
     */
    /**
 * Update the specified question with image support.
 */
/**
 * Update the specified question with image support.
 */
/**
 * Update the specified question with image support.
 */
public function update(UpdateQuestionRequest $request, Question $question)
{
    try {
        $questionData = $request->except(['options', 'correct_numerical', 'tolerance', 'question_image']);
        $options = $request->input('options', []);
        
        // Handle question image upload
        if ($request->hasFile('question_image')) {
            if ($question->question_image) {
                Storage::disk('public')->delete($question->question_image);
            }
            $imagePath = $request->file('question_image')->store('questions/images', 'public');
            $questionData['question_image'] = $imagePath;
            $questionData['question_image_alt'] = $request->question_image_alt;
        }
        
        if ($request->has('remove_question_image') && $request->remove_question_image == '1') {
            if ($question->question_image) {
                Storage::disk('public')->delete($question->question_image);
            }
            $questionData['question_image'] = null;
            $questionData['question_image_alt'] = null;
        }

        // Process options - Store images and update option data
        if (!empty($options)) {
            foreach ($options as $index => &$option) {
                // Handle option image upload
                $hasNewImage = false;
                $uploadedFile = null;
                
                // Check if option_image is an UploadedFile
                if (isset($option['option_image'])) {
                    if ($option['option_image'] instanceof \Illuminate\Http\UploadedFile) {
                        $hasNewImage = true;
                        $uploadedFile = $option['option_image'];
                    }
                    // Check if it's an array with UploadedFile
                    elseif (is_array($option['option_image']) && isset($option['option_image']['UploadedFile'])) {
                        $hasNewImage = true;
                        $uploadedFile = $option['option_image']['UploadedFile'];
                    }
                }
                
                // Also check the request files
                if (!$hasNewImage && $request->hasFile("options.{$index}.option_image")) {
                    $uploadedFile = $request->file("options.{$index}.option_image");
                    $hasNewImage = true;
                }
                
                // If we have a file, store it
                if ($hasNewImage && $uploadedFile instanceof \Illuminate\Http\UploadedFile) {
                    // Delete old image if this is an existing option
                    if (isset($option['id']) && $option['id']) {
                        $oldOption = QuestionOption::find($option['id']);
                        if ($oldOption && $oldOption->option_image) {
                            Storage::disk('public')->delete($oldOption->option_image);
                        }
                    }
                    
                    // Store the new image
                    $optionImagePath = $uploadedFile->store('questions/options', 'public');
                    $option['option_image'] = $optionImagePath; // Pass the path to repository
                } 
                // If no file and we need to keep existing image
                elseif (isset($option['id']) && $option['id']) {
                    // Keep existing image (or remove if remove_image is checked)
                    if (isset($option['remove_image']) && $option['remove_image'] == '1') {
                        $oldOption = QuestionOption::find($option['id']);
                        if ($oldOption && $oldOption->option_image) {
                            Storage::disk('public')->delete($oldOption->option_image);
                        }
                        $option['option_image'] = null;
                    } else {
                        // Keep the existing image path
                        $oldOption = QuestionOption::find($option['id']);
                        if ($oldOption && $oldOption->option_image) {
                            $option['option_image'] = $oldOption->option_image;
                        }
                    }
                }
                // New option without image
                else {
                    unset($option['option_image']);
                }
                
                // Ensure is_correct is properly set
                $option['is_correct'] = isset($option['is_correct']) ? (int)$option['is_correct'] : 0;
                $option['order'] = $option['order'] ?? $index;
                $option['option_text'] = $option['option_text'] ?? null;
            }
        }
        
        // Handle numerical question
        if ($request->type === 'numerical') {
            $options = [
                [
                    'option_text' => $request->correct_numerical,
                    'is_correct' => true,
                    'order' => 0
                ]
            ];
        }
        
        // Update question using repository
        $this->questionRepository->updateWithOptions($question->id, $questionData, $options);

        return redirect()->route('admin.questions.index')
                        ->with('success', 'Question updated successfully.');
                        
    } catch (\Exception $e) {
        Log::error('Question update error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'question_id' => $question->id
        ]);
        
        return back()->with('error', 'Error updating question: ' . $e->getMessage())
                     ->withInput();
    }
}

    /**
     * Remove the specified question.
     */
    public function destroy(Question $question)
    {
        try {
            if ($question->exams()->count() > 0) {
                return back()->with('error', 'Cannot delete question that is used in exams.');
            }

            if ($question->question_image) {
                Storage::disk('public')->delete($question->question_image);
            }
            
            foreach ($question->options as $option) {
                if ($option->option_image) {
                    Storage::disk('public')->delete($option->option_image);
                }
            }

            $question->delete();

            return redirect()->route('admin.questions.index')
                            ->with('success', 'Question deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting question: ' . $e->getMessage());
        }
    }

    /**
     * Show import form.
     */
    public function showImportForm()
    {
        return view('admin.questions.import');
    }

    /**
     * Import questions from file.
     */
    // public function import(ImportQuestionsRequest $request)
    // {
    //     try {
    //         Excel::import(new QuestionsImport($request->type), $request->file('file'));

    //         return redirect()->route('admin.questions.index')
    //                         ->with('success', 'Questions imported successfully.');
    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Error importing questions: ' . $e->getMessage());
    //     }
    // }
    
    /**
 * Import questions from file - Updated with bilingual support
 */
/**
 * Import questions from file - With image upload support
 */
public function import(ImportQuestionsRequest $request)
{
    // Log::info($request->all());
    try {
        // Get uploaded images
        $uploadedFiles = $request->file('images') ?? [];
        
        // Log::info($uploadedFiles);
        // Create import instance with uploaded files
        $import = new QuestionsImport($request->type, $uploadedFiles);
        
        // Import the Excel file
        $import->import($request->file('file'));

        $successCount = $import->getSuccessCount();
        $errors = $import->getErrors();
        $totalRows = $import->getRowCount();

        $message = "Successfully imported {$successCount} questions.";

        if (!empty($errors)) {
            $message .= " There were " . count($errors) . " errors.";
            Log::warning('Question import errors:', $errors);
        }

        if ($totalRows > 0 && $successCount === 0) {
            return back()->with('error', 'Failed to import any questions. Please check the file format.');
        }

        if (!empty($errors)) {
            return redirect()->route('admin.questions.index')
                ->with('warning', $message)
                ->with('import_errors', $errors);
        }

        return redirect()->route('admin.questions.index')
            ->with('success', $message);

    } catch (\Exception $e) {
        Log::error('Question import error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);

        return back()->with('error', 'Error importing questions: ' . $e->getMessage());
    }
}

    /**
     * Export questions to file.
     */
    public function export(Request $request)
    {
        try {
            $filters = $request->only(['type', 'difficulty', 'subject_id', 'category_id']);

            return Excel::download(new QuestionsExport($filters), 'questions_' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting questions: ' . $e->getMessage());
        }
    }

    /**
     * Download import template.
     */
    // public function downloadTemplate()
    // {
    //     $headers = [
    //         'question_text', 'question_image', 'question_image_alt', 'type', 'difficulty', 
    //         'marks', 'negative_marks', 'explanation', 'subject_id', 
    //         'option_1_text', 'option_1_image', 'option_1_image_alt', 'option_1_correct',
    //         'option_2_text', 'option_2_image', 'option_2_image_alt', 'option_2_correct',
    //         'option_3_text', 'option_3_image', 'option_3_image_alt', 'option_3_correct',
    //         'option_4_text', 'option_4_image', 'option_4_image_alt', 'option_4_correct'
    //     ];

    //     $filename = 'question_import_template.xlsx';

    //     return Excel::download(new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
    //         private $headers;

    //         public function __construct($headers)
    //         {
    //             $this->headers = $headers;
    //         }

    //         public function array(): array
    //         {
    //             return [
    //                 $this->headers,
    //                 [
    //                     'Sample question?', '', '', 'single_choice', 'medium', 
    //                     '1', '0', 'This is a sample explanation', '1',
    //                     'Option A', '', '', '1', 
    //                     'Option B', '', '', '0', 
    //                     'Option C', '', '', '0', 
    //                     'Option D', '', '', '0'
    //                 ]
    //             ];
    //         }
    //     }, $filename);
    // }
    
    /**
 * Download import template - Updated with bilingual and image support
 */
public function downloadTemplate()
{
    $headers = [
        // Question fields
        'question_text',
        'question_text_hi',
        'question_image',
        'question_image_alt',
        'question_image_alt_hi',
        'explanation_image',
        'explanation_image_alt',
        'explanation_image_alt_hi',
        'type',
        'difficulty',
        'marks',
        'negative_marks',
        'explanation',
        'explanation_hi',
        'subject_id',
        
        // Option 1
        'option_1_text',
        'option_1_text_hi',
        'option_1_image',
        'option_1_image_alt',
        'option_1_image_alt_hi',
        'option_1_correct',
        
        // Option 2
        'option_2_text',
        'option_2_text_hi',
        'option_2_image',
        'option_2_image_alt',
        'option_2_image_alt_hi',
        'option_2_correct',
        
        // Option 3
        'option_3_text',
        'option_3_text_hi',
        'option_3_image',
        'option_3_image_alt',
        'option_3_image_alt_hi',
        'option_3_correct',
        
        // Option 4
        'option_4_text',
        'option_4_text_hi',
        'option_4_image',
        'option_4_image_alt',
        'option_4_image_alt_hi',
        'option_4_correct',
        
        // Option 5
        'option_5_text',
        'option_5_text_hi',
        'option_5_image',
        'option_5_image_alt',
        'option_5_image_alt_hi',
        'option_5_correct',
        
        // Option 6
        'option_6_text',
        'option_6_text_hi',
        'option_6_image',
        'option_6_image_alt',
        'option_6_image_alt_hi',
        'option_6_correct',
        
        // Option 7
        'option_7_text',
        'option_7_text_hi',
        'option_7_image',
        'option_7_image_alt',
        'option_7_image_alt_hi',
        'option_7_correct',
        
        // Option 8
        'option_8_text',
        'option_8_text_hi',
        'option_8_image',
        'option_8_image_alt',
        'option_8_image_alt_hi',
        'option_8_correct',
        
        // Option 9
        'option_9_text',
        'option_9_text_hi',
        'option_9_image',
        'option_9_image_alt',
        'option_9_image_alt_hi',
        'option_9_correct',
        
        // Option 10
        'option_10_text',
        'option_10_text_hi',
        'option_10_image',
        'option_10_image_alt',
        'option_10_image_alt_hi',
        'option_10_correct',
    ];

    $filename = 'question_import_template.xlsx';

    return Excel::download(new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithTitle {
        private $headers;

        public function __construct($headers)
        {
            $this->headers = $headers;
        }

        public function array(): array
        {
            return [
                $this->headers,
                [
                    // Question
                    'Sample question?',
                    'नमूना प्रश्न?',
                    'https://example.com/question-image.jpg',
                    'Question image alt text',
                    'प्रश्न छवि वैकल्पिक पाठ',
                    'https://example.com/explanation-image.jpg',
                    'Explanation image alt text',
                    'व्याख्या छवि वैकल्पिक पाठ',
                    'single_choice',
                    'medium',
                    '1',
                    '0',
                    'This is a sample explanation. It explains the correct answer.',
                    'यह एक नमूना व्याख्या है। यह सही उत्तर की व्याख्या करती है।',
                    '1', // subject_id
                    
                    // Option 1
                    'Option A',
                    'विकल्प ए',
                    'https://example.com/option-a.jpg',
                    'Option A image alt',
                    'विकल्प ए छवि वैकल्पिक पाठ',
                    '1', // correct
                    
                    // Option 2
                    'Option B',
                    'विकल्प बी',
                    '',
                    '',
                    '',
                    '0', // Incorrect
                    
                    // Option 3
                    'Option C',
                    'विकल्प सी',
                    '',
                    '',
                    '',
                    '0', // Incorrect
                    
                    // Option 4
                    'Option D',
                    'विकल्प डी',
                    '',
                    '',
                    '',
                    '0', // Incorrect
                    
                ]
            ];
        }

        public function title(): string
        {
            return 'Questions Import Template';
        }
    }, $filename);
}

    /**
     * Bulk delete questions.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id'
        ]);

        try {
            $questions = Question::whereIn('id', $request->question_ids)
                                 ->whereDoesntHave('exams')
                                 ->get();
            
            foreach ($questions as $question) {
                if ($question->question_image) {
                    Storage::disk('public')->delete($question->question_image);
                }
                foreach ($question->options as $option) {
                    if ($option->option_image) {
                        Storage::disk('public')->delete($option->option_image);
                    }
                }
                $question->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Selected questions deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting questions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update status.
     */
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            Question::whereIn('id', $request->question_ids)
                    ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Question status updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }
}