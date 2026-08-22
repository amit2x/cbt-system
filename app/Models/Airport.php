<?php
// app/Models/Airport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Airport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'icao_code',
        'slug',
        'domain',
        'timezone',
        'country',
        'city',
        'address',
        'phone',
        'email',
        'website',
        'logo',
        'favicon',
        'settings',
        'features',
        'status',
        'subscription_plan',
        'subscription_expires_at',
        'max_students',
        'max_instructors',
        'max_exams',
        'storage_limit_gb',
        'created_by'
    ];

    protected $casts = [
        'settings' => 'array',
        'features' => 'array',
        'subscription_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->name . ' (' . $this->code . ')';
    }

    public function getStatusColorAttribute()
    {
        return [
            'active' => 'success',
            'inactive' => 'secondary',
            'suspended' => 'warning',
            'maintenance' => 'danger'
        ][$this->status] ?? 'secondary';
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'active' => 'success',
            'inactive' => 'secondary',
            'suspended' => 'warning',
            'maintenance' => 'danger'
        ];

        return '<span class="badge bg-' . ($colors[$this->status] ?? 'secondary') . '">' . ucfirst($this->status) . '</span>';
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function hasCapacity()
    {
        $studentCount = $this->users()->whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->count();

        return $studentCount < $this->max_students;
    }

    public function getUsagePercentage()
    {
        if ($this->max_students == 0) return 0;
        $studentCount = $this->users()->whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->count();
        
        return round(($studentCount / $this->max_students) * 100, 2);
    }
}