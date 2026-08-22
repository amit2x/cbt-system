<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\HasAirport;

class QuestionCategory extends BaseModel
{
    use HasAirport;
    protected $table = 'question_categories';

    protected $fillable = [
        'name', 'slug', 'description', 'parent_id', 'order', 'is_active'
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
            $category->slug = Str::slug($category->name);
        });
    }

    public function parent()
    {
        return $this->belongsTo(QuestionCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(QuestionCategory::class, 'parent_id')->orderBy('order');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_category');
    }

    public function activeChildren()
    {
        return $this->children()->where('is_active', true);
    }

    public function getFullPathAttribute()
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    public function getQuestionCountAttribute()
    {
        return $this->questions()->count();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}
