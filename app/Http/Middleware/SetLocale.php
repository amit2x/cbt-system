<?php
// app/Http/Middleware/SetLocale.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = null;
        
        // 1. Check session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
        }
        
        // 2. Check authenticated user preference
        if (!$locale && Auth::check() && Auth::user()->preferred_locale) {
            $locale = Auth::user()->preferred_locale;
            Session::put('locale', $locale);
        }
        
        // 3. Check request header
        if (!$locale && $request->hasHeader('Accept-Language')) {
            $headerLang = $request->header('Accept-Language');
            if (strpos($headerLang, 'hi') !== false) {
                $locale = 'hi';
            }
        }
        
        // 4. Default to English
        if (!$locale || !in_array($locale, ['en', 'hi'])) {
            $locale = 'en';
        }
        
        // Set application locale
        App::setLocale($locale);
        Session::put('locale', $locale);
        
        return $next($request);
    }
}