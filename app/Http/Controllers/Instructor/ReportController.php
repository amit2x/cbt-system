<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:instructor');
    }

    public function index()
    {
        return view('instructor.reports.index');
    }

    public function examReports()
    {
        return view('instructor.reports.exams');
    }

    public function studentReports()
    {
        return view('instructor.reports.students');
    }

    public function batchReports()
    {
        return view('instructor.reports.batches');
    }

    public function export(Request $request)
    {
        // Export logic here
        return redirect()->back()->with('info', 'Export feature coming soon');
    }
}
