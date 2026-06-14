<?php

namespace App\Exports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class QuestionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Question::with(['options', 'subject', 'categories']);

        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        if (!empty($this->filters['difficulty'])) {
            $query->where('difficulty', $this->filters['difficulty']);
        }

        if (!empty($this->filters['subject_id'])) {
            $query->where('subject_id', $this->filters['subject_id']);
        }

        if (!empty($this->filters['category_id'])) {
            $query->whereHas('categories', function($q) {
                $q->where('question_categories.id', $this->filters['category_id']);
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Question Text',
            'Type',
            'Difficulty',
            'Marks',
            'Negative Marks',
            'Subject',
            'Categories',
            'Explanation',
            'Option 1',
            'Option 1 Correct',
            'Option 2',
            'Option 2 Correct',
            'Option 3',
            'Option 3 Correct',
            'Option 4',
            'Option 4 Correct',
            'Created At'
        ];
    }

    public function map($question): array
    {
        $options = $question->options;

        $row = [
            $question->id,
            strip_tags($question->question_text),
            $question->type_name,
            $question->difficulty_name,
            $question->marks,
            $question->negative_marks,
            $question->subject->title ?? '',
            $question->categories->pluck('name')->implode(', '),
            strip_tags($question->explanation ?? ''),
        ];

        // Add options
        for ($i = 0; $i < 4; $i++) {
            if (isset($options[$i])) {
                $row[] = strip_tags($options[$i]->option_text);
                $row[] = $options[$i]->is_correct ? 'Yes' : 'No';
            } else {
                $row[] = '';
                $row[] = '';
            }
        }

        $row[] = $question->created_at->format('Y-m-d H:i:s');

        return $row;
    }
}
