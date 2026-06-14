<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:instructor');
    }

    public function index()
    {
        return view('instructor.analytics.index');
    }

    public function examAnalytics()
    {
        return view('instructor.analytics.exams');
    }

    public function studentAnalytics()
    {
        return view('instructor.analytics.students');
    }

    public function batchAnalytics()
    {
        return view('instructor.analytics.batches');
    }
}
