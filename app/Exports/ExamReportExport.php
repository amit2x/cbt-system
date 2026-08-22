<?php
// app/Exports/ExamReportExport.php

namespace App\Exports;

use App\Models\Exam;
use App\Models\Result;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExamReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $exam;

    public function __construct(Exam $exam)
    {
        $this->exam = $exam;
    }

    public function collection()
    {
        return Result::where('exam_id', $this->exam->id)
            ->with(['student', 'batch'])
            ->orderBy('percentage', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Student Name',
            'Student Email',
            'Batch',
            'Obtained Marks',
            'Total Marks',
            'Percentage',
            'Correct',
            'Incorrect',
            'Unanswered',
            'Status',
            'Time Taken (min)',
            'Accuracy',
            'Date'
        ];
    }

    public function map($result): array
    {
        static $rank = 0;
        $rank++;

        return [
            $rank,
            $result->student->name ?? 'N/A',
            $result->student->email ?? 'N/A',
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

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A' => ['font' => ['bold' => true]],
        ];
    }
}