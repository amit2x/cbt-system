<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAirport;

class Module extends BaseModel
{
    use HasAirport;
    protected $fillable = [
        'course_id', 'title', 'description', 'order', 'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }
}
