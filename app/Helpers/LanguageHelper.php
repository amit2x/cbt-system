<?php
// app/Helpers/LanguageHelper.php

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageHelper
{
    /**
     * Get current locale
     */
    public static function getLocale()
    {
        return App::getLocale();
    }

    /**
     * Check if current locale is Hindi
     */
    public static function isHindi()
    {
        return App::getLocale() === 'hi';
    }

    /**
     * Check if current locale is English
     */
    public static function isEnglish()
    {
        return App::getLocale() === 'en';
    }

    /**
     * Get language label for current locale
     */
    public static function getCurrentLanguageLabel()
    {
        return self::isHindi() ? 'हिन्दी' : 'English';
    }

    /**
     * Get available languages
     */
    public static function getAvailableLanguages()
    {
        return [
            'en' => 'English',
            'hi' => 'हिन्दी',
        ];
    }

    /**
     * Get content based on locale
     */
    public static function getLocalizedContent($englishContent, $hindiContent)
    {
        if (self::isHindi() && !empty($hindiContent)) {
            return $hindiContent;
        }
        return $englishContent;
    }

    /**
     * Get localized alt text for image
     */
    public static function getLocalizedAlt($englishAlt, $hindiAlt)
    {
        if (self::isHindi() && !empty($hindiAlt)) {
            return $hindiAlt;
        }
        return $englishAlt;
    }

    /**
     * Set language and store in session
     */
    public static function setLocale($locale)
    {
        if (in_array($locale, ['en', 'hi'])) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            
            // Update user preference if logged in
            if (auth()->check()) {
                auth()->user()->update(['preferred_locale' => $locale]);
            }
            
            return true;
        }
        return false;
    }

    /**
     * Get language code for meta tags
     */
    public static function getHtmlLang()
    {
        return App::getLocale() === 'hi' ? 'hi' : 'en';
    }

    /**
     * Get language direction
     */
    public static function getDir()
    {
        return App::getLocale() === 'hi' ? 'ltr' : 'ltr';
    }
}