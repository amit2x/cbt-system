<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StudyMaterialCategory extends Model
{
    protected $table = 'study_material_categories';
    
    protected $fillable = [
        'name', 'slug', 'description', 'order', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            $category->slug = Str::slug($category->name);
        });
        
        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function materials()
    {
        return $this->hasMany(StudyMaterial::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getMaterialCountAttribute()
    {
        return $this->materials()->where('status', 'published')->count();
    }
}