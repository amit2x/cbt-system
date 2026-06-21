<?php

namespace App\Repositories\Eloquent;

use App\Models\Question;
use App\Repositories\Contracts\QuestionRepositoryInterface;
use Illuminate\Support\Facades\DB;

class QuestionRepository extends BaseRepository implements QuestionRepositoryInterface
{
    public function __construct(Question $model)
    {
        parent::__construct($model);
    }

    // public function createWithOptions(array $questionData, array $options = [])
    // {
    //     DB::beginTransaction();
    //     try {
    //         // Create question
    //         $question = $this->create($questionData);

    //         // Attach categories if provided
    //         if (isset($questionData['category_ids'])) {
    //             $question->categories()->sync($questionData['category_ids']);
    //         }

    //         // Create options for choice questions
    //         if (!empty($options)) {
    //             foreach ($options as $index => $optionData) {
    //                 $question->options()->create([
    //                     'option_text' => $optionData['option_text'],
    //                     'is_correct' => $optionData['is_correct'] ?? false,
    //                     'order' => $optionData['order'] ?? $index
    //                 ]);
    //             }
    //         }

    //         DB::commit();
    //         return $question->load(['options', 'categories']);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }
    // }
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
                
                $question->options()->create([
                    'option_text' => $optionData['option_text'],
                    'is_correct' => $isCorrect,
                    'order' => $optionData['order'] ?? $index
                ]);
            }
        }
        
        DB::commit();
        return $question->load(['options', 'categories']);
        
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
    public function updateWithOptions($id, array $questionData, array $options = [])
    {
        DB::beginTransaction();
        try {
            // Update question
            $question = $this->update($id, $questionData);

            // Sync categories
            if (isset($questionData['category_ids'])) {
                $question->categories()->sync($questionData['category_ids']);
            }

            // Update options for choice questions
            if (!empty($options)) {
                // Delete existing options
                $question->options()->delete();

                // Create new options
                foreach ($options as $index => $optionData) {
                    $question->options()->create([
                        'option_text' => $optionData['option_text'],
                        'is_correct' => $optionData['is_correct'] ?? false,
                        'order' => $optionData['order'] ?? $index
                    ]);
                }
            }

            DB::commit();
            return $question->load(['options', 'categories']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getQuestionsByDifficulty($difficulty)
    {
        return $this->model->where('difficulty', $difficulty)
                          ->where('status', 'active')
                          ->with(['options', 'categories'])
                          ->get();
    }

    public function getQuestionsByType($type)
    {
        return $this->model->where('type', $type)
                          ->where('status', 'active')
                          ->with(['options', 'categories'])
                          ->get();
    }

    public function getQuestionsByCategory($categoryId)
    {
        return $this->model->whereHas('categories', function($q) use ($categoryId) {
                            $q->where('question_categories.id', $categoryId);
                        })
                        ->where('status', 'active')
                        ->with(['options', 'categories'])
                        ->get();
    }

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
}
