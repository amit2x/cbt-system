<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAirport;


class ExamLog extends BaseModel
{
    use HasAirport;
    protected $fillable = [
        'student_exam_id', 'student_id', 'exam_id',
        'type', 'message', 'metadata', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function studentExam()
    {
        return $this->belongsTo(StudentExam::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    // Scopes
    public function scopeSecurity($query)
    {
        return $query->where('type', 'security');
    }

    public function scopeTabSwitches($query)
    {
        return $query->where('type', 'tab_switch');
    }

    public function scopeErrors($query)
    {
        return $query->where('type', 'error');
    }

    // Helper method to create log
    public static function add($studentExamId, $type, $message, $metadata = null)
    {
        $studentExam = StudentExam::find($studentExamId);

        return self::create([
            'student_exam_id' => $studentExamId,
            'student_id' => $studentExam->student_id,
            'exam_id' => $studentExam->exam_id,
            'type' => $type,
            'message' => $message,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}
