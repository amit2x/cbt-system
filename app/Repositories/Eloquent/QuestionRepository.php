<?php

namespace App\Repositories\Eloquent;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Repositories\Contracts\QuestionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class QuestionRepository extends BaseRepository implements QuestionRepositoryInterface
{
    public function __construct(Question $model)
    {
        parent::__construct($model);
    }

    /**
     * Create a new question with options (including images)
     */
    public function createWithOptions(array $questionData, array $options = [])
    {
        DB::beginTransaction();
        try {
            // Create question
            $question = $this->create($questionData);
            
            // Attach categories if provided
            if (isset($questionData['category_ids'])) {
                $question->categories()->sync($questionData['category_ids']);
            }
            
            // Create options for choice questions or numerical
            if (!empty($options)) {
                foreach ($options as $index => $optionData) {
                    // Ensure is_correct is a boolean
                    $isCorrect = isset($optionData['is_correct']) ? 
                        filter_var($optionData['is_correct'], FILTER_VALIDATE_BOOLEAN) : 
                        false;
                    
                    // Prepare option data with image support
                    $optionCreateData = [
                        'option_text' => $optionData['option_text'] ?? null,
                        'is_correct' => $isCorrect,
                        'order' => $optionData['order'] ?? $index
                    ];
                    
                    // Add option image if present
                    if (isset($optionData['option_image']) && !empty($optionData['option_image'])) {
                        $optionCreateData['option_image'] = $optionData['option_image'];
                    }
                    
                    // Add option image alt if present
                    if (isset($optionData['option_image_alt'])) {
                        $optionCreateData['option_image_alt'] = $optionData['option_image_alt'];
                    }
                    
                    $question->options()->create($optionCreateData);
                }
            }
            
            DB::commit();
            return $question->load(['options', 'categories']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating question with options: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a question with options (including images)
     */
    public function updateWithOptions($id, array $questionData, array $options = [])
    {
        DB::beginTransaction();
        try {
            // Find the question
            $question = $this->find($id);
            
            // Update question
            $question->update($questionData);

            // Sync categories
            if (isset($questionData['category_ids'])) {
                $question->categories()->sync($questionData['category_ids']);
            }

            // Update options for choice questions
            if (!empty($options)) {
                // First, collect existing option IDs to preserve images if needed
                $existingOptions = $question->options()->get()->keyBy('id');
                
                // Delete existing options (but images will be handled separately)
                $question->options()->delete();
                
                // Create new options with proper data
                foreach ($options as $index => $optionData) {
                    // Get the option image - handle both string paths and null
                    $optionImage = null;
                    if (isset($optionData['option_image']) && !empty($optionData['option_image'])) {
                        // If it's a string (path), use it
                        if (is_string($optionData['option_image'])) {
                            $optionImage = $optionData['option_image'];
                        }
                        // If it's an UploadedFile, it should have been stored already
                        // The controller should have stored it and passed the path
                    }
                    
                    // Prepare option data
                    $optionCreateData = [
                        'option_text' => $optionData['option_text'] ?? null,
                        'is_correct' => isset($optionData['is_correct']) ? 
                            filter_var($optionData['is_correct'], FILTER_VALIDATE_BOOLEAN) : 
                            false,
                        'order' => $optionData['order'] ?? $index
                    ];
                    
                    // Add image if present
                    if ($optionImage) {
                        $optionCreateData['option_image'] = $optionImage;
                    }
                    
                    // Add image alt if present
                    if (isset($optionData['option_image_alt'])) {
                        $optionCreateData['option_image_alt'] = $optionData['option_image_alt'];
                    }
                    
                    $question->options()->create($optionCreateData);
                }
            }

            DB::commit();
            return $question->load(['options', 'categories']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating question with options: ' . $e->getMessage(), [
                'question_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get questions by difficulty
     */
    public function getQuestionsByDifficulty($difficulty)
    {
        return $this->model->where('difficulty', $difficulty)
                          ->where('status', 'active')
                          ->with(['options', 'categories'])
                          ->get();
    }

    /**
     * Get questions by type
     */
    public function getQuestionsByType($type)
    {
        return $this->model->where('type', $type)
                          ->where('status', 'active')
                          ->with(['options', 'categories'])
                          ->get();
    }

    /**
     * Get questions by category
     */
    public function getQuestionsByCategory($categoryId)
    {
        return $this->model->whereHas('categories', function($q) use ($categoryId) {
                            $q->where('question_categories.id', $categoryId);
                        })
                        ->where('status', 'active')
                        ->with(['options', 'categories'])
                        ->get();
    }

    /**
     * Get random questions with filters
     */
    public function getRandomQuestions($count, $filters = [])
    {
        $query = $this->model->where('status', 'active');

        if (isset($filters['difficulty'])) {
            $query->where('difficulty', $filters['difficulty']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['category_id'])) {
            $query->whereHas('categories', function($q) use ($filters) {
                $q->where('question_categories.id', $filters['category_id']);
            });
        }

        if (isset($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (isset($filters['exclude_ids'])) {
            $query->whereNotIn('id', $filters['exclude_ids']);
        }

        return $query->inRandomOrder()
                     ->limit($count)
                     ->with(['options', 'categories'])
                     ->get();
    }

    /**
     * Bulk upload questions
     */
    public function bulkUpload(array $questions)
    {
        DB::beginTransaction();
        try {
            $imported = [];

            foreach ($questions as $questionData) {
                $options = $questionData['options'] ?? [];
                unset($questionData['options']);

                $question = $this->createWithOptions($questionData, $options);
                $imported[] = $question;
            }

            DB::commit();
            return $imported;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get questions for an exam
     */
    public function getQuestionsForExam($examId, $count = null)
    {
        $query = $this->model->whereHas('exams', function($q) use ($examId) {
                            $q->where('exam_id', $examId);
                        })
                        ->with(['options']);

        if ($count) {
            return $query->take($count)->get();
        }

        return $query->get();
    }

    /**
     * Delete question and its associated images
     */
    public function deleteWithImages($id)
    {
        DB::beginTransaction();
        try {
            $question = $this->find($id);
            
            // Delete question image
            if ($question->question_image) {
                Storage::disk('public')->delete($question->question_image);
            }
            
            // Delete option images
            foreach ($question->options as $option) {
                if ($option->option_image) {
                    Storage::disk('public')->delete($option->option_image);
                }
            }
            
            // Delete the question
            $question->delete();
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}