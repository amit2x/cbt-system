<?php
// app/Exports/AllStudentsReportExport.php

namespace App\Exports;

use App\Models\User;
use App\Models\Result;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AllStudentsReportExport implements WithMultipleSheets
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: Summary
        $sheets[] = new StudentsSummarySheet($this->filters);

        // Sheet 2: Detailed Results
        $sheets[] = new StudentsDetailedSheet($this->filters);

        // Sheet 3: Performance Metrics
        $sheets[] = new StudentsPerformanceSheet($this->filters);

        return $sheets;
    }
}

// Students Summary Sheet
class StudentsSummarySheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = User::role('student')->with(['batches', 'results']);

        if (isset($this->filters['batch_id'])) {
            $query->whereHas('batches', function($q) {
                $q->where('batches.id', $this->filters['batch_id']);
            });
        }

        $students = $query->get();

        $data = [];
        foreach ($students as $student) {
            $results = $student->results;
            $data[] = [
                $student->name ?? 'N/A',
                $student->email ?? 'N/A',
                $student->batches->first()->name ?? 'N/A',
                $results->count(),
                round($results->avg('percentage'), 2),
                $results->where('status', 'passed')->count(),
                $results->count() > 0 
                    ? round(($results->where('status', 'passed')->count() / $results->count()) * 100, 2)
                    : 0,
                round($results->avg('accuracy_percentage'), 2),
                $student->is_active ? 'Active' : 'Inactive'
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Email',
            'Batch',
            'Exams Taken',
            'Average Score (%)',
            'Exams Passed',
            'Pass Rate (%)',
            'Average Accuracy (%)',
            'Status'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

// Students Detailed Sheet
class StudentsDetailedSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Result::with(['student', 'exam', 'batch']);

        if (isset($this->filters['batch_id'])) {
            $query->where('batch_id', $this->filters['batch_id']);
        }

        $results = $query->orderBy('created_at', 'desc')->get();

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                $result->student->name ?? 'N/A',
                $result->student->email ?? 'N/A',
                $result->exam->title ?? 'N/A',
                $result->batch->name ?? 'N/A',
                $result->obtained_marks ?? 0,
                $result->total_marks ?? 0,
                round($result->percentage, 2),
                $result->correct_answers ?? 0,
                $result->incorrect_answers ?? 0,
                $result->unanswered ?? 0,
                ucfirst($result->status ?? 'N/A'),
                round($result->time_taken_seconds / 60, 2),
                round($result->accuracy_percentage ?? 0, 2),
                $result->created_at ? $result->created_at->format('d M Y') : 'N/A'
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Email',
            'Exam',
            'Batch',
            'Obtained Marks',
            'Total Marks',
            'Percentage',
            'Correct',
            'Incorrect',
            'Unanswered',
            'Status',
            'Time Taken (min)',
            'Accuracy (%)',
            'Date'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

// Students Performance Sheet
class StudentsPerformanceSheet implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = User::role('student')->with(['results']);

        if (isset($this->filters['batch_id'])) {
            $query->whereHas('batches', function($q) {
                $q->where('batches.id', $this->filters['batch_id']);
            });
        }

        $students = $query->get();

        $data = [];
        foreach ($students as $student) {
            $results = $student->results;
            $scores = $results->pluck('percentage')->toArray();
            sort($scores);
            
            $total = $results->count();
            $passed = $results->where('status', 'passed')->count();
            
            $data[] = [
                $student->name ?? 'N/A',
                $student->email ?? 'N/A',
                $total,
                $passed,
                $total - $passed,
                round($results->avg('percentage'), 2),
                round($results->max('percentage'), 2),
                round($results->min('percentage'), 2),
                $this->calculateMedian($scores),
                round($results->avg('accuracy_percentage'), 2),
                round($results->avg('speed_per_minute'), 2),
            ];
        }

        return collect($data);
    }

    protected function calculateMedian($array)
    {
        $count = count($array);
        if ($count == 0) return 0;
        if ($count == 1) return $array[0];
        
        $middle = floor($count / 2);
        if ($count % 2 == 0) {
            return ($array[$middle - 1] + $array[$middle]) / 2;
        }
        return $array[$middle];
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Email',
            'Exams Taken',
            'Exams Passed',
            'Exams Failed',
            'Average Score',
            'Highest Score',
            'Lowest Score',
            'Median Score',
            'Average Accuracy',
            'Average Speed'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}