<?php
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        // For backward compatibility during setup/installation
        if (!app()->runningInConsole() && !app()->resolved('db') && !Schema::hasTable('settings')) {
            return $default;
        }

        try {
            static $settings = null;

            if ($settings === null) {
                if (Schema::hasTable('settings')) {
                    $settings = Cache::remember('settings', 3600, function() {
                        return \App\Models\Setting::all()->keyBy('key');
                    });
                } else {
                    return $default;
                }
            }

            $setting = $settings[$key] ?? null;

            if (!$setting) {
                return $default;
            }

            return match($setting->type) {
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'integer' => (int) $setting->value,
                'float' => (float) $setting->value,
                'json' => json_decode($setting->value, true),
                'image' => $setting->value ? asset('storage/' . $setting->value) : $default,
                default => $setting->value ?? $default
            };
        } catch (\Exception $e) {
            // If any database error occurs, return default value
            return $default;
        }
    }
}

if (!function_exists('theme_color')) {
    function theme_color($color, $default = '#4e73df')
    {
        try {
            return setting('theme_' . $color, $default);
        } catch (\Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('is_dark_mode')) {
    function is_dark_mode()
    {
        try {
            $mode = setting('theme_mode', 'light');
            if ($mode === 'auto') {
                return isset($_COOKIE['dark_mode']) ? $_COOKIE['dark_mode'] === 'true' : false;
            }
            return $mode === 'dark';
        } catch (\Exception $e) {
            return false;
        }
    }
}
