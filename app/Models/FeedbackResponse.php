<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAirport;

class FeedbackResponse extends BaseModel
{

    use HasAirport;
    protected $fillable = [
        'feedback_id', 'student_id', 'answers', 'rating', 'submitted_at'
    ];

    protected $casts = [
        'answers' => 'array',
        'submitted_at' => 'datetime'
    ];

    public function feedback()
    {
        return $this->belongsTo(Feedback::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
