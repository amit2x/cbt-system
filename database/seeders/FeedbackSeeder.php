<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\Batch;
use App\Models\User;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    public function run()
    {
        // Get first instructor and batch
        $instructor = User::role('instructor')->first();
        $batch = Batch::first();

        if ($instructor && $batch) {
            // Create quick feedback
            Feedback::create([
                'code' => 'QUICK' . rand(100, 999),
                'instructor_id' => $instructor->id,
                'batch_id' => $batch->id,
                'course_id' => $batch->course_id,
                'type' => 'quick',
                'questions' => null,
                'status' => 'active',
                'expires_at' => now()->addDays(7)
            ]);

            // Create detailed feedback
            Feedback::create([
                'code' => 'DETAIL' . rand(100, 999),
                'instructor_id' => $instructor->id,
                'batch_id' => $batch->id,
                'course_id' => $batch->course_id,
                'type' => 'detailed',
                'questions' => [
                    'How would you rate today\'s class?',
                    'What topics were most helpful?',
                    'Any suggestions for improvement?'
                ],
                'status' => 'active',
                'expires_at' => now()->addDays(7)
            ]);
        }
    }
}
