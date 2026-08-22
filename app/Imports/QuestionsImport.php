<?php
// app/Imports/QuestionsImport.php

namespace App\Imports;

use App\Models\Question;
use App\Models\QuestionOption;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class QuestionsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure, WithBatchInserts, WithChunkReading
{
    use Importable;

    protected $type;
    protected $errors = [];
    protected $rowCount = 0;
    protected $successCount = 0;
    protected $uploadedFiles = [];
    protected $uploadedFileMap = [];

    public function __construct($type = null, $uploadedFiles = [])
    {
        $this->type = $type;
        $this->uploadedFiles = $uploadedFiles;
        
        // Build filename map for quick lookup
        foreach ($uploadedFiles as $file) {
            $filename = $file->getClientOriginalName();
            $this->uploadedFileMap[$filename] = $file;
            
            // Also map without extension for flexibility
            $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
            $this->uploadedFileMap[$nameWithoutExt] = $file;
        }
    }

    public function model(array $row)
    {
        $this->rowCount++;

        try {
            // Determine question type
            $type = $this->type ?? $row['type'] ?? 'single_choice';

            // Get subject_id
            $subjectId = $row['subject_id'] ?? null;

            // Prepare question data
            $questionData = [
                'question_text' => $row['question_text'] ?? '',
                'question_text_hi' => $row['question_text_hi'] ?? null,
                'type' => $type,
                'difficulty' => $row['difficulty'] ?? 'medium',
                'marks' => (int) ($row['marks'] ?? 1),
                'negative_marks' => (float) ($row['negative_marks'] ?? 0),
                'explanation' => $row['explanation'] ?? null,
                'explanation_hi' => $row['explanation_hi'] ?? null,
                'subject_id' => $subjectId,
                'created_by' => auth()->id(),
                'status' => 'active'
            ];

            // Handle question image
            if (!empty($row['question_image'])) {
                $questionData['question_image'] = $this->processImage($row['question_image'], 'questions/images');
            }

            // Handle question image alt
            $questionData['question_image_alt'] = $row['question_image_alt'] ?? null;
            $questionData['question_image_alt_hi'] = $row['question_image_alt_hi'] ?? null;

            // Handle explanation image
            if (!empty($row['explanation_image'])) {
                $questionData['explanation_image'] = $this->processImage($row['explanation_image'], 'questions/explanation');
            }

            // Handle explanation image alt
            $questionData['explanation_image_alt'] = $row['explanation_image_alt'] ?? null;
            $questionData['explanation_image_alt_hi'] = $row['explanation_image_alt_hi'] ?? null;

            // Create question
            $question = Question::create($questionData);

            // Create options for choice questions
            if (in_array($type, ['single_choice', 'multiple_choice'])) {
                $this->createOptions($question, $row);
            }

            $this->successCount++;

            return $question;

        } catch (\Exception $e) {
            Log::error('Import question error: ' . $e->getMessage(), [
                'row' => $row,
                'trace' => $e->getTraceAsString()
            ]);
            $this->errors[] = "Row {$this->rowCount}: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Create options for the question
     */
    protected function createOptions($question, $row)
    {
        $options = [];
        $maxOptions = 10;

        for ($i = 1; $i <= $maxOptions; $i++) {
            $optionText = $row["option_{$i}_text"] ?? null;
            $optionTextHi = $row["option_{$i}_text_hi"] ?? null;

            // If no text and no image, skip this option
            if (empty($optionText) && empty($optionTextHi) && empty($row["option_{$i}_image"])) {
                continue;
            }

            $optionData = [
                'question_id' => $question->id,
                'option_text' => $optionText,
                'option_text_hi' => $optionTextHi,
                'is_correct' => ($row["option_{$i}_correct"] ?? 0) == 1,
                'order' => $i - 1
            ];

            // Handle option image
            if (!empty($row["option_{$i}_image"])) {
                $optionData['option_image'] = $this->processImage($row["option_{$i}_image"], 'questions/options');
            }

            // Handle option image alt
            $optionData['option_image_alt'] = $row["option_{$i}_image_alt"] ?? null;
            $optionData['option_image_alt_hi'] = $row["option_{$i}_image_alt_hi"] ?? null;

            $options[] = $optionData;
        }

        // Create options
        foreach ($options as $option) {
            QuestionOption::create($option);
        }

        // Validate for single choice - ensure exactly one correct
        if ($question->type === 'single_choice') {
            $correctCount = collect($options)->where('is_correct', true)->count();
            if ($correctCount !== 1) {
                Log::warning('Single choice question has ' . $correctCount . ' correct options', [
                    'question_id' => $question->id,
                    'question_text' => $question->question_text
                ]);
            }
        }
    }

    /**
     * Process image - supports URL, Google Drive, and uploaded files
     */
    protected function processImage($imagePath, $storagePath)
    {
        if (empty($imagePath)) {
            return null;
        }

        // Clean the path
        $imagePath = trim($imagePath);
        $imagePath = preg_replace('/[\x{200E}\x{200F}\x{202A}-\x{202E}]/u', '', $imagePath);
        $imagePath = str_replace(['"', "'", '‪', '‬'], '', $imagePath);
        $imagePath = str_replace('\\', '/', $imagePath);

        try {
            // 1. Check if it's a Google Drive URL
            if ($this->isGoogleDriveUrl($imagePath)) {
                $imagePath = $this->convertGoogleDriveToDirectUrl($imagePath);
            }

            // 2. Check if it's a URL (http:// or https://)
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                return $this->downloadImageFromUrl($imagePath, $storagePath);
            }

            // 3. Check if it's a local file path
            if ($this->isLocalFilePath($imagePath)) {
                return $this->processLocalFile($imagePath, $storagePath);
            }

            // 4. Check if it's a filename from uploaded files
            $result = $this->processUploadedFileByName($imagePath, $storagePath);
            if ($result) {
                return $result;
            }

            // 5. If it's already a stored path, return it
            if (Str::startsWith($imagePath, 'storage/') || Str::startsWith($imagePath, '/storage/')) {
                return str_replace('/storage/', '', $imagePath);
            }

            Log::warning('Unrecognized image path format: ' . $imagePath);
            return null;

        } catch (\Exception $e) {
            Log::error('Error processing image: ' . $e->getMessage(), ['path' => $imagePath]);
            return null;
        }
    }

    /**
     * Check if the path is a local file path
     */
    protected function isLocalFilePath($path)
    {
        // Windows paths: C:\Users\... or D:\...
        if (preg_match('/^[A-Za-z]:[\/\\\\]/', $path)) {
            return true;
        }
        
        // Unix paths: /home/... or /tmp/...
        if (Str::startsWith($path, '/') && !Str::startsWith($path, '//')) {
            return true;
        }
        
        // Relative paths: ./uploads/... or ../...
        if (Str::startsWith($path, '.') || Str::startsWith($path, '..')) {
            return true;
        }

        return false;
    }

    /**
     * Process a local file path
     */
    protected function processLocalFile($localPath, $storagePath)
    {
        try {
            $filename = basename($localPath);
            
            // First try to find the file in uploaded files
            $uploadedFile = $this->findUploadedFile($filename);
            if ($uploadedFile) {
                return $this->storeUploadedFile($uploadedFile, $storagePath);
            }

            // Try to find the file in storage
            if (File::exists($localPath)) {
                $extension = File::extension($localPath);
                $newFilename = Str::random(40) . '.' . $extension;
                $fullPath = $storagePath . '/' . $newFilename;

                Storage::disk('public')->put($fullPath, File::get($localPath));
                return $fullPath;
            }

            Log::warning('Local file not found: ' . $localPath);
            return null;

        } catch (\Exception $e) {
            Log::error('Error processing local file: ' . $e->getMessage(), ['path' => $localPath]);
            return null;
        }
    }

    /**
     * Process uploaded file by name (with extension or without)
     */
    protected function processUploadedFileByName($fileName, $storagePath)
    {
        // Try exact match
        $uploadedFile = $this->findUploadedFile($fileName);
        if ($uploadedFile) {
            return $this->storeUploadedFile($uploadedFile, $storagePath);
        }

        // Try without extension
        $nameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
        $uploadedFile = $this->findUploadedFile($nameWithoutExt);
        if ($uploadedFile) {
            return $this->storeUploadedFile($uploadedFile, $storagePath);
        }

        // Try with common extensions
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        foreach ($extensions as $ext) {
            $testName = $nameWithoutExt . '.' . $ext;
            $uploadedFile = $this->findUploadedFile($testName);
            if ($uploadedFile) {
                return $this->storeUploadedFile($uploadedFile, $storagePath);
            }
        }

        Log::warning('Uploaded file not found: ' . $fileName);
        return null;
    }

    /**
     * Store an uploaded file to storage
     */
    protected function storeUploadedFile($uploadedFile, $storagePath)
    {
        try {
            $extension = $uploadedFile->getClientOriginalExtension() ?: 'jpg';
            $newFilename = Str::random(40) . '.' . $extension;
            $fullPath = $storagePath . '/' . $newFilename;

            Storage::disk('public')->put($fullPath, File::get($uploadedFile->getRealPath()));

            return $fullPath;

        } catch (\Exception $e) {
            Log::error('Error storing uploaded file: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find an uploaded file by name
     */
    protected function findUploadedFile($fileName)
    {
        // Normalize the filename for comparison
        $fileName = trim($fileName);
        $fileName = str_replace(' ', '_', $fileName);
        
        // Check in the map
        if (isset($this->uploadedFileMap[$fileName])) {
            return $this->uploadedFileMap[$fileName];
        }

        // Case-insensitive search
        $fileNameLower = strtolower($fileName);
        foreach ($this->uploadedFileMap as $key => $file) {
            if (strtolower($key) === $fileNameLower) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Download image from URL and store it
     */
    protected function downloadImageFromUrl($url, $path)
    {
        if (empty($url)) {
            return null;
        }

        try {
            // If it's already a local path, return it
            if (Str::startsWith($url, 'storage/') || Str::startsWith($url, '/storage/')) {
                return str_replace('/storage/', '', $url);
            }

            // Download image from URL
            $response = Http::timeout(60)
                ->retry(3, 1000)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->get($url);
            
            if (!$response->successful()) {
                Log::warning('Image download failed.', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);                
                return null;
            }

            // Get content type
            $contentType = $response->header('Content-Type');
            $extension = $this->getExtensionFromContentType($contentType);

            // Generate filename
            $filename = Str::random(40) . '.' . $extension;
            $fullPath = $path . '/' . $filename;

            // Store image
            Storage::disk('public')->put($fullPath, $response->body());

            return $fullPath;

        } catch (\Exception $e) {
            Log::error('Error downloading image from URL: ' . $e->getMessage(), ['url' => $url]);
            return null;
        }
    }

    /**
     * Get file extension from content type
     */
    protected function getExtensionFromContentType($contentType)
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
        ];

        return $map[$contentType] ?? 'jpg';
    }

    /**
     * Check if the URL belongs to Google Drive.
     */
    protected function isGoogleDriveUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
    
        return in_array($host, [
            'drive.google.com',
            'www.drive.google.com',
            'docs.google.com',
        ]);
    }
    
    /**
     * Convert a Google Drive sharing URL to a direct download URL.
     */
    protected function convertGoogleDriveToDirectUrl(string $url): string
    {
        // Already a direct download URL
        if (Str::contains($url, 'uc?export=download')) {
            return $url;
        }
    
        $fileId = null;
    
        // Format: https://drive.google.com/file/d/FILE_ID/view
        if (preg_match('#/file/d/([a-zA-Z0-9_-]+)#', $url, $matches)) {
            $fileId = $matches[1];
        }
    
        // Format: https://drive.google.com/open?id=FILE_ID
        elseif (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $fileId = $matches[1];
        }
    
        // Format: https://drive.google.com/uc?id=FILE_ID
        elseif (preg_match('#/uc\?id=([a-zA-Z0-9_-]+)#', $url, $matches)) {
            $fileId = $matches[1];
        }
    
        if (!$fileId) {
            Log::warning('Unable to extract Google Drive file ID.', [
                'url' => $url
            ]);
    
            return $url;
        }
    
        return "https://drive.google.com/uc?export=download&id={$fileId}";
    }

     /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            '*.question_text' => 'required|string',
            '*.type' => 'nullable|in:single_choice,multiple_choice,numerical',
            '*.difficulty' => 'nullable|in:easy,medium,hard',
            '*.marks' => 'nullable|numeric|min:0',
            '*.negative_marks' => 'nullable|numeric|min:0',
            '*.subject_id' => 'nullable|exists:modules,id',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'question_text.required' => 'The question text is required.',
            'type.in' => 'The type must be single_choice, multiple_choice, or numerical.',
            'difficulty.in' => 'The difficulty must be easy, medium, or hard.',
            'subject_id.exists' => 'The subject/module does not exist.',
        ];
    }

    /**
     * Handle failures
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }

    /**
     * Handle errors
     */
    public function onError(\Throwable $e)
    {
        $this->errors[] = "Error: " . $e->getMessage();
        Log::error('Import error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
    }

    /**
     * Get import errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get success count
     */
    public function getSuccessCount()
    {
        return $this->successCount;
    }

    /**
     * Get total rows processed
     */
    public function getRowCount()
    {
        return $this->rowCount;
    }

    /**
     * Batch insert size
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Chunk reading size
     */
    public function chunkSize(): int
    {
        return 100;
    }
    
}