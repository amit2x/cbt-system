<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'order'];

    protected $casts = [
        'value' => 'string'
    ];

    /**
     * Get setting value
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return match($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'float' => (float) $setting->value,
            'json' => json_decode($setting->value, true),
            'image' => $setting->value ? asset('storage/' . $setting->value) : $default,
            default => $setting->value
        };
    }

    /**
     * Set setting value
     */
    public static function set($key, $value, $type = 'text', $group = 'general')
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type,
                'group' => $group
            ]
        );

        Cache::forget('settings');

        return $setting;
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup($group)
    {
        return self::where('group', $group)
            ->orderBy('order')
            ->get()
            ->mapWithKeys(function($item) {
                $value = match($item->type) {
                    'boolean' => filter_var($item->value, FILTER_VALIDATE_BOOLEAN),
                    'integer' => (int) $item->value,
                    'float' => (float) $item->value,
                    'json' => json_decode($item->value, true),
                    'image' => $item->value ? asset('storage/' . $item->value) : null,
                    default => $item->value
                };
                return [$item->key => $value];
            });
    }
}
