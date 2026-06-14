<?php

namespace App\Exports;

use App\Models\Batch;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BatchStudentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $batch;

    public function __construct(Batch $batch)
    {
        $this->batch = $batch;
    }

    public function collection()
    {
        return $this->batch->students()->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Enrolled Date',
            'Status'
        ];
    }

    public function map($student): array
    {
        return [
            $student->id,
            $student->name,
            $student->email,
            $student->phone,
            $student->pivot->enrolled_at,
            $student->pivot->status
        ];
    }
}
