<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:student');
    }

    public function index()
    {
        return view('student.analytics.index');
    }

    public function performance()
    {
        return view('student.analytics.performance');
    }

    public function topics()
    {
        return view('student.analytics.topics');
    }
}
