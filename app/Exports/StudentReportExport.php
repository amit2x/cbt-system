<?php
// app/Exports/StudentReportExport.php

namespace App\Exports;

use App\Models\User;
use App\Models\Result;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $student;
    protected $batchId;

    public function __construct(User $student, $batchId = null)
    {
        $this->student = $student;
        $this->batchId = $batchId;
    }

    public function collection()
    {
        $query = Result::where('student_id', $this->student->id)
            ->with(['exam', 'batch']);

        if ($this->batchId) {
            $query->where('batch_id', $this->batchId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
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
            'Accuracy',
            'Date'
        ];
    }

    public function map($result): array
    {
        return [
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

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}