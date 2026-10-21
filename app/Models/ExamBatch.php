<?php

// app/Models/ExamBatch.php

namespace App\Models;

use App\Traits\HasAirport;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ExamBatch extends Pivot
{
    use HasAirport;

    protected $table = 'exam_batches';

    public $incrementing = true;

    protected $fillable = [
        'batch_id',
        'exam_id',
        'airport_id',
        'available_from',
        'available_until',
    ];

    protected $casts = [
        'available_from' => 'date',
        'available_until' => 'date',
    ];
}
