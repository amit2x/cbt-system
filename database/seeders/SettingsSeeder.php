<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        // General Settings
        Setting::set('app_name', 'CBT System', 'text', 'general');
        Setting::set('app_logo', null, 'image', 'general');
        Setting::set('app_favicon', null, 'image', 'general');
        Setting::set('app_description', 'Computer Based Test System', 'text', 'general');
        Setting::set('contact_email', 'admin@cbt.com', 'text', 'general');
        Setting::set('contact_phone', '+1234567890', 'text', 'general');
        Setting::set('address', '123 Education Street', 'text', 'general');

        // Theme Settings
        Setting::set('theme_mode', 'light', 'text', 'theme'); // light, dark, auto
        Setting::set('primary_color', '#4e73df', 'text', 'theme');
        Setting::set('secondary_color', '#858796', 'text', 'theme');
        Setting::set('success_color', '#1cc88a', 'text', 'theme');
        Setting::set('info_color', '#36b9cc', 'text', 'theme');
        Setting::set('warning_color', '#f6c23e', 'text', 'theme');
        Setting::set('danger_color', '#e74a3b', 'text', 'theme');
        Setting::set('sidebar_bg', '#1a1e2b', 'text', 'theme');
        Setting::set('sidebar_text', '#ffffff', 'text', 'theme');
        Setting::set('header_bg', '#ffffff', 'text', 'theme');
        Setting::set('header_text', '#3a3b45', 'text', 'theme');

        // System Settings
        Setting::set('timezone', 'UTC', 'text', 'system');
        Setting::set('date_format', 'd M Y', 'text', 'system');
        Setting::set('time_format', 'H:i', 'text', 'system');
        Setting::set('items_per_page', 15, 'integer', 'system');
        Setting::set('enable_registration', true, 'boolean', 'system');
        Setting::set('enable_notifications', true, 'boolean', 'system');
        Setting::set('maintenance_mode', false, 'boolean', 'system');

        // Exam Settings
        Setting::set('default_exam_duration', 60, 'integer', 'exam');
        Setting::set('default_passing_percentage', 40, 'integer', 'exam');
        Setting::set('default_negative_marking', 0, 'float', 'exam');
        Setting::set('enable_proctoring', false, 'boolean', 'exam');
        Setting::set('max_tab_switches', 3, 'integer', 'exam');
        Setting::set('auto_submit_on_timeout', true, 'boolean', 'exam');

        // Cache settings
        Setting::set('cache_enabled', true, 'boolean', 'performance');
        Setting::set('cache_ttl', 3600, 'integer', 'performance');

        // SEO Settings
        Setting::set('meta_title', 'CBT System - Online Exam Platform', 'text', 'seo');
        Setting::set('meta_description', 'Advanced Computer Based Test System', 'text', 'seo');
        Setting::set('meta_keywords', 'cbt, exam, test, online assessment', 'text', 'seo');
        Setting::set('google_analytics_id', null, 'text', 'seo');
    }
}
