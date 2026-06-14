<?php

namespace App\Helpers;

class Constants
{
    // Question types
    const QUESTION_TYPE_SINGLE = 'single_choice';
    const QUESTION_TYPE_MULTIPLE = 'multiple_choice';
    const QUESTION_TYPE_NUMERICAL = 'numerical';

    // Difficulty levels
    const DIFFICULTY_EASY = 'easy';
    const DIFFICULTY_MEDIUM = 'medium';
    const DIFFICULTY_HARD = 'hard';

    // Exam status
    const EXAM_STATUS_DRAFT = 'draft';
    const EXAM_STATUS_PUBLISHED = 'published';
    const EXAM_STATUS_IN_PROGRESS = 'in_progress';
    const EXAM_STATUS_COMPLETED = 'completed';

    // Student exam status
    const STUDENT_EXAM_PENDING = 'pending';
    const STUDENT_EXAM_STARTED = 'started';
    const STUDENT_EXAM_COMPLETED = 'completed';
    const STUDENT_EXAM_EXPIRED = 'expired';
}
