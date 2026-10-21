<?php

// app/Models/BatchStudent.php

namespace App\Models;

use App\Traits\HasAirport;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BatchStudent extends Pivot
{
    use HasAirport;

    protected $table = 'batch_student';

    public $incrementing = true;

    protected $fillable = [
        'batch_id',
        'student_id',
        'airport_id',
        'enrolled_at',
        'status',
    ];

    protected $casts = [
        'enrolled_at' => 'date',
    ];
}
