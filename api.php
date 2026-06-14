<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\Student\ExamController as StudentExamAPIController;
use App\Http\Controllers\API\Student\ResultController as StudentResultAPIController;
use App\Http\Controllers\API\Student\DashboardController as StudentDashboardAPIController;

// Public API routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Protected API routes with Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Student API - using Spatie for permission checks in controllers
    Route::prefix('student')->group(function () {
        // Dashboard
        Route::get('/dashboard', [StudentDashboardAPIController::class, 'index']);

        // Exams
        Route::get('/exams', [StudentExamAPIController::class, 'index']);
        Route::get('/exams/{exam}', [StudentExamAPIController::class, 'show']);
        Route::post('/exams/{exam}/start', [StudentExamAPIController::class, 'start']);
        Route::get('/active-exam/{studentExam}', [StudentExamAPIController::class, 'getActiveExam']);
        Route::post('/active-exam/{studentExam}/save-answer', [StudentExamAPIController::class, 'saveAnswer']);
        Route::post('/active-exam/{studentExam}/submit', [StudentExamAPIController::class, 'submit']);

        // Results
        Route::get('/results', [StudentResultAPIController::class, 'index']);
        Route::get('/results/{result}', [StudentResultAPIController::class, 'show']);
        Route::get('/results/{result}/analytics', [StudentResultAPIController::class, 'analytics']);
    });

    // Instructor API (future)
    Route::middleware('role:instructor')->prefix('instructor')->group(function () {
        // Will add instructor API endpoints later
    });

    // Admin API (future)
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Will add admin API endpoints later
    });
});
