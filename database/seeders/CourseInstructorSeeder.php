<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseInstructorSeeder extends Seeder
{
    public function run()
    {
        // Get the instructor user (email: instructor@cbt.com)
        $instructor = User::where('email', 'instructor@cbt.com')->first();

        // Get all courses
        $courses = Course::all();

        if ($instructor && $courses->count() > 0) {
            // Assign first 3 courses to this instructor
            foreach ($courses->take(3) as $course) {
                if (! $course->instructors()->where('user_id', $instructor->id)->exists()) {
                    $course->instructors()->attach($instructor->id);
                    $this->command->info("Assigned course '{$course->title}' to instructor '{$instructor->name}'");
                }
            }
        }
    }
}
