<?php

namespace App\Exports;

use App\Models\Result;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ResultsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $examId;

    public function __construct($examId)
    {
        $this->examId = $examId;
    }

    public function collection()
    {
        return Result::with(['student', 'exam'])
            ->where('exam_id', $this->examId)
            ->orderBy('percentage', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Student Name',
            'Email',
            'Total Marks',
            'Obtained Marks',
            'Percentage',
            'Correct',
            'Incorrect',
            'Unanswered',
            'Time Taken',
            'Status',
            'Date'
        ];
    }

    public function map($result): array
    {
        return [
            $result->rank ?? 'N/A',
            $result->student->name,
            $result->student->email,
            $result->total_marks,
            $result->obtained_marks,
            $result->percentage . '%',
            $result->correct_answers,
            $result->incorrect_answers,
            $result->unanswered,
            gmdate('H:i:s', $result->time_taken_seconds),
            ucfirst($result->status),
            $result->created_at->format('Y-m-d H:i:s')
        ];
    }
}
