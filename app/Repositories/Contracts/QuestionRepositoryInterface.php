<?php

namespace App\Repositories\Contracts;

interface QuestionRepositoryInterface
{
    public function createWithOptions(array $questionData, array $options = []);
    public function updateWithOptions($id, array $questionData, array $options = []);
    public function getQuestionsByDifficulty($difficulty);
    public function getQuestionsByType($type);
    public function getQuestionsByCategory($categoryId);
    public function getRandomQuestions($count, $filters = []);
    public function bulkUpload(array $questions);
    public function getQuestionsForExam($examId, $count = null);
}
