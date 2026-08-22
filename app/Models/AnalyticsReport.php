<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAirport;

class AnalyticsReport extends Model
{
    use HasAirport;
    protected $table = 'analytics_reports';

    protected $fillable = [
        'report_type', 'title', 'filters', 'data', 'summary', 'charts',
        'generated_by', 'exam_id', 'batch_id', 'student_id',
        'date_from', 'date_to', 'file_path', 'file_type'
    ];

    protected $casts = [
        'filters' => 'array',
        'data' => 'array',
        'summary' => 'array',
        'charts' => 'array',
        'date_from' => 'date',
        'date_to' => 'date'
    ];

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Helper methods
    public function getFormattedDateRangeAttribute()
    {
        if ($this->date_from && $this->date_to) {
            return $this->date_from->format('d M Y') . ' - ' . $this->date_to->format('d M Y');
        }
        return 'All Time';
    }

    public function getReportTypeNameAttribute()
    {
        return match($this->report_type) {
            'exam' => 'Exam Report',
            'student' => 'Student Report',
            'batch' => 'Batch Report',
            'question' => 'Question Analysis',
            'overall' => 'Overall Analytics',
            default => ucfirst($this->report_type)
        };
    }
}
