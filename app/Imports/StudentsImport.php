<?php

namespace App\Imports;

use App\Models\Batch;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements SkipsOnError, ToModel, WithChunkReading, WithHeadingRow, WithValidation
{
    use Importable, SkipsErrors;

    protected $batch;

    protected $stats = [
        'created' => 0,
        'existing' => 0,
        'enrolled' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    public function __construct(Batch $batch)
    {
        $this->batch = $batch;
    }

    /**
     * Define chunk size for memory efficiency
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Define validation rules
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|regex:/^[0-9]{10,15}$/',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email format',
        ];
    }

    /**
     * Process each row
     */
    public function model(array $row)
    {
        // Skip empty rows
        if (empty(array_filter($row))) {
            Log::info('Skipping empty row');

            return null;
        }

        try {
            // Trim and clean the data
            $row = array_map('trim', $row);

            // Log the row being processed (excluding sensitive data)
            Log::info('Processing import row', [
                'email' => $row['email'] ?? 'no email',
                'name' => $row['name'] ?? 'no name',
            ]);

            // Validate required fields
            if (empty($row['name']) || empty($row['email'])) {
                throw new \Exception('Name and email are required');
            }

            // Check if email exists
            $student = User::where('email', $row['email'])->first();

            if (! $student) {
                // Create new student
                $student = User::create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => Hash::make('password123'), // Default password
                    'phone' => $row['phone'] ?? null,
                    'is_active' => true,
                ]);

                // Assign student role
                $student->assignRole('student');

                $this->stats['created']++;
                Log::info('New student created', ['email' => $row['email']]);
            } else {
                $this->stats['existing']++;
                Log::info('Existing student found', ['email' => $row['email']]);
            }

            // Check if already enrolled in this batch
            // IMPORTANT: Use 'student_id' not 'user_id' in the where clause
            $exists = $this->batch->students()
                ->where('student_id', $student->id)
                ->exists();

            if (! $exists) {
                // Enroll in batch
                $this->batch->students()->attach($student->id, [
                    'enrolled_at' => now(),
                    'status' => 'active',
                ]);

                $this->stats['enrolled']++;
                Log::info('Student enrolled in batch', [
                    'student_id' => $student->id,
                    'batch_id' => $this->batch->id,
                ]);
            } else {
                $this->stats['skipped']++;
                Log::info('Student already enrolled', ['email' => $row['email']]);
            }

            return null;

        } catch (\Exception $e) {
            $this->stats['errors'][] = [
                'row' => $row,
                'error' => $e->getMessage(),
            ];

            Log::error('Error processing import row', [
                'error' => $e->getMessage(),
                'row' => $row,
            ]);

            // Re-throw to stop import on critical errors
            throw $e;
        }
    }

    /**
     * Get import statistics
     */
    public function getStats()
    {
        return $this->stats;
    }
}
