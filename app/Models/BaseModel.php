<?php

// app/Models/BaseModel.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

abstract class BaseModel extends Model
{
    use LogsActivity;

    /**
     * Get the activity log options.
     * ✅ For v5.0.0, use correct method names
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            // ->dontLogEmptyActivities()  // ✅ Changed from dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $userName = Auth::user()?->name ?? 'System';
                $modelName = class_basename($this);
                $modelIdentifier = $this->getLogIdentifier();

                return match($eventName) {
                    'created' => "{$userName} created {$modelName}: {$modelIdentifier}",
                    'updated' => "{$userName} updated {$modelName}: {$modelIdentifier}",
                    'deleted' => "{$userName} deleted {$modelName}: {$modelIdentifier}",
                    'restored' => "{$userName} restored {$modelName}: {$modelIdentifier}",
                    default => "{$userName} performed {$eventName} on {$modelName}: {$modelIdentifier}",
                };
            })
            ->useLogName('default')
            ->logOnly(['*'])
            ->logExcept(['updated_at', 'created_at', 'deleted_at']);
    }

    /**
     * Get the log identifier for the model.
     */
    protected function getLogIdentifier(): string
    {
        if (isset($this->name)) {
            return $this->name;
        }
        if (isset($this->title)) {
            return $this->title;
        }
        if (isset($this->email)) {
            return $this->email;
        }
        return "#{$this->id}";
    }

    /**
     * Get the subject of the activity.
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Get the causer of the activity.
     */
    public function causer()
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter activities by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('event', $type);
    }

    /**
     * Scope to filter activities by causer.
     */
    public function scopeCausedBy($query, $userId)
    {
        return $query->where('causer_id', $userId)
                     ->where('causer_type', User::class);
    }

    /**
     * Scope to filter activities by subject.
     */
    // public function scopeForSubject($query, $subjectId, $subjectType)
    // {
    //     return $query->where('subject_id', $subjectId)
    //                  ->where('subject_type', $subjectType);
    // }

    /**
     * Scope to filter activities by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter activities by log name.
     */
    public function scopeInLog($query, $logName)
    {
        return $query->where('log_name', $logName);
    }
}