<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        // Redirect based on role
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('instructor')) {
            return redirect()->route('instructor.dashboard');
        } elseif ($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }

        return view('home');
    }



    /**
     * Show privacy policy page
     */
    public function privacyPolicy()
    {
        $user = auth()->user();
        $role = $user ? $user->roles->first()->name ?? 'guest' : 'guest';

        return view('pages.privacy-policy', compact('role'));
    }

    /**
     * Show terms of service page
     */
    public function termsOfService()
    {
        $user = auth()->user();
        $role = $user ? $user->roles->first()->name ?? 'guest' : 'guest';

        return view('pages.terms-of-service', compact('role'));
    }

    /**
     * Show about us page
     */
    public function aboutUs()
    {
        $user = auth()->user();
        $role = $user ? $user->roles->first()->name ?? 'guest' : 'guest';

        return view('pages.about-us', compact('role'));
    }

    /**
     * Show contact us page
     */
    public function contactUs()
    {
        $user = auth()->user();
        $role = $user ? $user->roles->first()->name ?? 'guest' : 'guest';

        return view('pages.contact-us', compact('role'));
    }

    /**
     * Show FAQ page
     */
    public function faq()
    {
        $user = auth()->user();
        $role = $user ? $user->roles->first()->name ?? 'guest' : 'guest';

        return view('pages.faq', compact('role'));
    }

    /**
     * Show cookie policy page
     */
    public function cookiePolicy()
    {
        $user = auth()->user();
        $role = $user ? $user->roles->first()->name ?? 'guest' : 'guest';

        return view('pages.cookie-policy', compact('role'));
    }

}
