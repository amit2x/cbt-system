<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ==================== PERMISSIONS ====================
        $permissions = [
            // Dashboard
            'view admin dashboard',
            'view instructor dashboard',
            'view student dashboard',

            // Course Management
            'view courses',
            'create courses',
            'edit courses',
            'delete courses',

            // Batch Management
            'view batches',
            'create batches',
            'edit batches',
            'delete batches',
            'assign students to batch',
            'remove students from batch',
            'import students',
            'export students',

            // Question Bank
            'view questions',
            'create questions',
            'edit questions',
            'delete questions',
            'import questions',
            'export questions',

            // Exam Management
            'view exams',
            'create exams',
            'edit exams',
            'delete exams',
            'publish exams',
            'schedule exams',
            'monitor live exams',

            // Test Taking
            'attempt exams',
            'view own results',
            'view all results',

            // Reports
            'view reports',
            'generate reports',
            'export reports',

            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            'assign roles',

            // Feedback
            'give feedback',
            'view feedback',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ==================== ROLES ====================

        // Admin Role - gets ALL permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        // Instructor Role
        $instructorRole = Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        $instructorRole->syncPermissions([
            'view instructor dashboard',
            'view courses',
            'view batches',
            'view questions',
            'create questions',
            'edit questions',
            'view exams',
            'create exams',
            'edit exams',
            'monitor live exams',
            'view all results',
            'view reports',
            'view feedback',
        ]);

        // Student Role
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $studentRole->syncPermissions([
            'view student dashboard',
            'view courses',
            'view exams',
            'attempt exams',
            'view own results',
            'give feedback',
        ]);

        // ==================== CREATE USERS ====================

        // Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@cbt.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');

        // Instructor User
        $instructor = User::firstOrCreate(
            ['email' => 'instructor@cbt.com'],
            [
                'name' => 'Instructor User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $instructor->assignRole('instructor');

        // Student User
        $student = User::firstOrCreate(
            ['email' => 'student@cbt.com'],
            [
                'name' => 'Student User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $student->assignRole('student');

        $this->command->info('Roles, Permissions and Users created successfully!');
    }
}
