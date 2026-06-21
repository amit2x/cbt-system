<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class StudyMaterial extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'description', 'content', 
        'file_path', 'file_name', 'file_type', 'file_size',
        'course_id', 'module_id', 'category_id', 'created_by',
        'material_type', 'external_url', 'status',
        'metadata', 'views', 'downloads'
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
        'views' => 'integer',
        'downloads' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($material) {
            $material->slug = Str::slug($material->title);
        });
        
        static::updating(function ($material) {
            if ($material->isDirty('title')) {
                $material->slug = Str::slug($material->title);
            }
        });
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function category()
    {
        return $this->belongsTo(StudyMaterialCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeForModule($query, $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }

    public function scopeForStudent($query, $studentId)
    {
        // Get materials for courses the student is enrolled in
        $batchIds = \DB::table('batch_student')
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->pluck('batch_id');

        $courseIds = \DB::table('batches')
            ->whereIn('id', $batchIds)
            ->pluck('course_id');

        return $query->whereIn('course_id', $courseIds)
                     ->where('status', 'published');
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }

    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) return 'N/A';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < 3) {
            $size /= 1024;
            $unitIndex++;
        }
        
        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    public function getMaterialTypeBadgeAttribute()
    {
        return match($this->material_type) {
            'pdf' => 'danger',
            'document' => 'primary',
            'video' => 'success',
            'link' => 'info',
            'content' => 'warning',
            default => 'secondary'
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'published' => 'success',
            'draft' => 'secondary',
            'archived' => 'danger',
            default => 'secondary'
        };
    }

    public function viewLogs()
    {
        return $this->hasMany(StudyMaterialView::class);
    }
    public function incrementViews()
    {
        $this->increment('views');
    }

    public function incrementDownloads()
    {
        $this->increment('downloads');
    }
}