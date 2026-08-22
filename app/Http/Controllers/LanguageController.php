<?php
// app/Http/Controllers/LanguageController.php

namespace App\Http\Controllers;

use App\Helpers\LanguageHelper;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Switch language
     */
    public function switch(Request $request)
    {
        $request->validate([
            'locale' => 'required|in:en,hi'
        ]);

        $locale = $request->locale;
        
        // Set locale
        LanguageHelper::setLocale($locale);

        // If user is logged in, update their preference
        if (auth()->check()) {
            auth()->user()->update(['preferred_locale' => $locale]);
        }

        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Language switched to ' . ($locale === 'hi' ? 'Hindi' : 'English'),
                'locale' => $locale
            ]);
        }

        // Redirect back for non-AJAX requests
        return redirect()->back()->with('success', 'Language switched to ' . ($locale === 'hi' ? 'Hindi' : 'English'));
    }

    /**
     * Get current language
     */
    public function current()
    {
        return response()->json([
            'locale' => app()->getLocale(),
            'language' => LanguageHelper::getCurrentLanguageLabel(),
            'is_hindi' => LanguageHelper::isHindi(),
        ]);
    }
}