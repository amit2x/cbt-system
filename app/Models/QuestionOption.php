<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Traits\HasAirport;
use Illuminate\Support\Facades\App;

class QuestionOption extends BaseModel
{
    use HasAirport;
    
    protected $fillable = [
        'question_id',
        'option_text',
        'option_text_hi',
        'option_image',
        'option_image_alt',
        'option_image_alt_hi',
        'is_correct',
        'order'
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'order' => 'integer'
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the question that this option belongs to
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the student answers for this option
     */
    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class, 'question_option_id');
    }

    // ==================== ACCESSORS ====================

    /**
     * Get the option display with image
     */
    public function getOptionDisplayAttribute()
    {
        $letter = $this->letter;
        $display = $letter . '. ' . $this->option_text;
        
        if ($this->option_image) {
            $display .= '<br><img src="' . asset('storage/' . $this->option_image) . '" alt="' . ($this->option_image_alt ?? 'Option image') . '" class="img-fluid mt-1">';
        }
        
        return $display;
    }

    /**
     * Get the option image URL
     */
    public function getOptionImageUrlAttribute()
    {
        return $this->option_image ? asset('storage/' . $this->option_image) : null;
    }

    /**
     * Get the formatted option text
     */
    public function getFormattedOptionAttribute()
    {
        return nl2br(e($this->option_text));
    }

    /**
     * Get option letter (A, B, C, etc.)
     */
    public function getLetterAttribute()
    {
        return chr(65 + ($this->order ?? 0));
    }

    /**
     * Get the option text with letter prefix
     */
    public function getTextWithLetterAttribute()
    {
        return $this->letter . '. ' . $this->option_text;
    }

    /**
     * Get if option has image
     */
    public function getHasImageAttribute()
    {
        return !empty($this->option_image);
    }

    /**
     * Get the correct status badge
     */
    public function getCorrectBadgeAttribute()
    {
        if ($this->is_correct) {
            return '<span class="badge bg-success">✓ Correct</span>';
        }
        return '<span class="badge bg-secondary">✗ Incorrect</span>';
    }

    // ==================== SCOPES ====================

    /**
     * Scope for correct options
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope for incorrect options
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    /**
     * Scope for options with images
     */
    public function scopeWithImages($query)
    {
        return $query->whereNotNull('option_image');
    }

    /**
     * Scope for options without images
     */
    public function scopeWithoutImages($query)
    {
        return $query->whereNull('option_image');
    }

    /**
     * Scope ordered by order field
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if option has text
     */
    public function hasText()
    {
        return !empty($this->option_text);
    }

    /**
     * Check if option has image
     */
    public function hasImage()
    {
        return !empty($this->option_image);
    }

    /**
     * Check if option has content (text or image)
     */
    public function hasContent()
    {
        return $this->hasText() || $this->hasImage();
    }

    /**
     * Delete option image
     */
    public function deleteImage()
    {
        if ($this->option_image && Storage::disk('public')->exists($this->option_image)) {
            Storage::disk('public')->delete($this->option_image);
            $this->update(['option_image' => null, 'option_image_alt' => null]);
            return true;
        }
        return false;
    }

    /**
     * Get the option content for display
     */
    public function getContent()
    {
        $content = [];
        
        if ($this->hasText()) {
            $content['text'] = $this->option_text;
        }
        
        if ($this->hasImage()) {
            $content['image'] = $this->getOptionImageUrlAttribute();
            $content['image_alt'] = $this->option_image_alt;
        }
        
        return $content;
    }

    /**
     * Get statistics for this option
     */
    public function getStatistics()
    {
        $total = $this->studentAnswers()->count();
        $selected = $this->studentAnswers()->count();
        
        return [
            'total_selections' => $selected,
            'percentage' => $total > 0 ? round(($selected / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Duplicate option for a new question
     */
    public function duplicate($newQuestionId)
    {
        $newOption = $this->replicate();
        $newOption->question_id = $newQuestionId;
        
        // Copy image if exists
        if ($this->option_image) {
            $oldPath = $this->option_image;
            $newPath = 'questions/options/' . basename($oldPath);
            
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->copy($oldPath, $newPath);
                $newOption->option_image = $newPath;
            }
        }
        
        $newOption->save();
        return $newOption;
    }
    
    
     // ==================== ACCESSORS FOR BILINGUAL CONTENT ====================

    /**
     * Get option text based on current locale
     */
    public function getOptionTextAttribute($value)
    {
        $locale = App::getLocale();
        if ($locale === 'hi' && !empty($this->attributes['option_text_hi'])) {
            return $this->attributes['option_text_hi'];
        }
        return $value;
    }

    /**
     * Get option image alt text based on current locale
     */
    public function getOptionImageAltAttribute($value)
    {
        $locale = App::getLocale();
        if ($locale === 'hi' && !empty($this->attributes['option_image_alt_hi'])) {
            return $this->attributes['option_image_alt_hi'];
        }
        return $value;
    }

    // ==================== FORMATTED DISPLAY ====================

    /**
     * Get option display with letter and content
     */
    // public function getOptionDisplayAttribute()
    // {
    //     $letter = $this->letter;
    //     $display = $letter . '. ';
        
    //     // Option text
    //     if ($this->option_text) {
    //         $display .= $this->option_text;
    //     }
        
    //     // Option image
    //     if ($this->option_image) {
    //         $display .= '<br><img src="' . asset('storage/' . $this->option_image) . 
    //                     '" alt="' . $this->option_image_alt . '" class="img-fluid mt-1" style="max-height: 80px;">';
    //     }
        
    //     return $display;
    // }

    /**
     * Get option letter (A, B, C, etc.)
     */
    // public function getLetterAttribute()
    // {
    //     return chr(65 + ($this->order ?? 0));
    // }

    /**
     * Check if option has image
     */
    // public function hasImage()
    // {
    //     return !empty($this->option_image);
    // }

    /**
     * Check if option has Hindi content
     */
    public function hasHindiContent()
    {
        return !empty($this->option_text_hi);
    }
}