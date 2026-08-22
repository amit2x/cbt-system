<?php
// app/Exports/BatchReportExport.php

namespace App\Exports;

use App\Models\Batch;
use App\Models\Result;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BatchReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $batch;

    public function __construct(Batch $batch)
    {
        $this->batch = $batch;
    }

    public function collection()
    {
        return Result::where('batch_id', $this->batch->id)
            ->with(['student', 'exam'])
            ->orderBy('percentage', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Student Email',
            'Exam',
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
        return [
            $result->student->name ?? 'N/A',
            $result->student->email ?? 'N/A',
            $result->exam->title ?? 'N/A',
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
        ];
    }
}