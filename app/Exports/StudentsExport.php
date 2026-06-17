<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = User::role('student')->with('batches');

        if (! empty($this->filters['batch_id'])) {
            $query->whereHas('batches', function ($q) {
                $q->where('batch_id', $this->filters['batch_id']);
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Roll No',
            'Batches',
            'Status',
            'Created At',
        ];
    }

    public function map($student): array
    {
        return [
            $student->id,
            $student->name,
            $student->email,
            $student->phone,
            $student->roll_no,
            $student->batches->pluck('name')->implode(', '),
            $student->is_active ? 'Active' : 'Inactive',
            $student->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
