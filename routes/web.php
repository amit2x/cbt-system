<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AirportController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamSectionController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\QuestionCategoryController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ResultModerationController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudyMaterialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Instructor\FeedbackController;
use App\Http\Controllers\Instructor\LiveMonitoringController;
use App\Http\Controllers\Instructor\MonitoringController;
use App\Http\Controllers\Instructor\QuizController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\ResultController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes & Home
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/home');
    }

    return redirect()->route('login');
});

Auth::routes(['register' => false]);

Route::get('/home', [HomeController::class, 'index'])->name('home');
/*
|--------------------------------------------------------------------------
| Public Static Pages
|--------------------------------------------------------------------------
*/
Route::prefix('pages')->name('')->group(function () {
    Route::get('/privacy-policy', [HomeController::class, 'privacyPolicy'])->name('privacy-policy');
    Route::get('/terms-of-service', [HomeController::class, 'termsOfService'])->name('terms-of-service');
    Route::get('/about-us', [HomeController::class, 'aboutUs'])->name('about');
    Route::get('/contact-us', [HomeController::class, 'contactUs'])->name('contact');
    Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
    Route::get('/cookie-policy', [HomeController::class, 'cookiePolicy'])->name('cookie-policy');
});

// Language Routes
Route::post('/language/switch', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('/language/current', [LanguageController::class, 'current'])->name('language.current');
/*
|--------------------------------------------------------------------------
| Profile Routes (Authenticated Users)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'show'])->name('show');
    Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
    Route::put('/update', [ProfileController::class, 'update'])->name('update');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::put('/settings/update', [ProfileController::class, 'updateSettings'])->name('update-settings');
    Route::post('/avatar', [ProfileController::class, 'uploadAvatar'])->name('upload-avatar');
    Route::delete('/remove-avatar', [ProfileController::class, 'removeAvatar'])->name('remove-avatar');
    Route::get('/activity', [ProfileController::class, 'activity'])->name('activity');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
    Route::post('/revoke-session', [ProfileController::class, 'revokeSession'])->name('revoke-session');
    Route::post('/revoke-all-sessions', [ProfileController::class, 'revokeAllSessions'])->name('revoke-all-sessions');
    Route::post('/enable-2fa', [ProfileController::class, 'enable2FA'])->name('enable-2fa');
    Route::post('/disable-2fa', [ProfileController::class, 'disable2FA'])->name('disable-2fa');
    Route::post('/deactivate', [ProfileController::class, 'deactivate'])->name('deactivate');

});

Route::middleware(['auth'])->group(function () {
    // for all auth user Notification
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
// Admin Routes
Route::middleware(['auth', 'role:admin|superadmin'])->prefix('admin')->name('admin.')->group(function () {

    Route::post('/sidebar/toggle', function (Request $request) {
        $request->session()->put('sidebar_collapsed', $request->open);

        return response()->json(['success' => true]);
    })->name('sidebar.toggle');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::post('/activity-logs/clear', [ActivityLogController::class, 'clear'])->name('activity-logs.clear');
    Route::get('/activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');

    // Admin Notification
    Route::get('/notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('admin.notifications.index');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('admin.notifications.mark-read');
    Route::post('/notifications/read-all', [App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-read');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== Course Management ====================
    Route::resource('courses', CourseController::class);

    // ==================== Module Management ====================
    // Option 1: Using CourseController (backward compatibility)
    Route::post('courses/{course}/modules', [CourseController::class, 'storeModule'])->name('courses.modules.store');
    Route::put('courses/{course}/modules/{module}', [CourseController::class, 'updateModule'])->name('courses.modules.update');
    Route::delete('courses/{course}/modules/{module}', [CourseController::class, 'deleteModule'])->name('courses.modules.delete');
    Route::post('courses/{course}/modules/order', [CourseController::class, 'updateModuleOrder'])->name('courses.modules.order');
    Route::get('courses/{course}/modules/api', [ModuleController::class, 'getModules'])->name('courses.modules.api');
    Route::get('courses/{course}/batches/api', [CourseController::class, 'getBatchesApi'])->name('courses.batches.api');

    // Option 2: Using ModuleController (new dedicated module management)
    Route::prefix('courses/{course}/modules')->name('modules.')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('index');
        Route::get('/create', [ModuleController::class, 'create'])->name('create');
        Route::post('/', [ModuleController::class, 'store'])->name('store');
        Route::get('/{module}', [ModuleController::class, 'show'])->name('show');
        Route::get('/{module}/edit', [ModuleController::class, 'edit'])->name('edit');
        Route::put('/{module}', [ModuleController::class, 'update'])->name('update');
        Route::delete('/{module}', [ModuleController::class, 'destroy'])->name('destroy');
        Route::post('/order', [ModuleController::class, 'updateOrder'])->name('order');
        Route::post('/{module}/toggle-status', [ModuleController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/bulk-delete', [ModuleController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-status', [ModuleController::class, 'bulkStatus'])->name('bulk-status');
        Route::get('/api', [ModuleController::class, 'getModules'])->name('api');
    });

    // ==================== Batch Management ====================
    Route::get('students/import-template', [BatchController::class, 'downloadImportTemplate'])
        ->name('students.import-template');

    Route::get('batches/{batch}/add-students', [BatchController::class, 'addStudents'])->name('batches.add-students');
    Route::post('batches/{batch}/students', [BatchController::class, 'storeStudents'])->name('batches.store-students');
    Route::delete('batches/{batch}/students/{student}', [BatchController::class, 'removeStudent'])->name('batches.remove-student');
    Route::post('batches/{batch}/students/{student}/status', [BatchController::class, 'updateStudentStatus'])->name('batches.student-status');
    Route::post('batches/{batch}/import-students', [BatchController::class, 'importStudents'])->name('batches.import-students');
    Route::get('batches/{batch}/export-students', [BatchController::class, 'exportStudents'])->name('batches.export-students');
    Route::get('batches/{batch}/statistics', [BatchController::class, 'statistics'])->name('batches.statistics');
    Route::post('batches/bulk-delete', [BatchController::class, 'bulkDelete'])->name('batches.bulk-delete');
    Route::post('batches/bulk-status', [BatchController::class, 'bulkStatus'])->name('batches.bulk-status');

    Route::resource('batches', BatchController::class);

    // ==================== Student Management ====================
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('/create', [StudentController::class, 'create'])->name('create');
        Route::post('/', [StudentController::class, 'store'])->name('store');
        Route::get('/import', [StudentController::class, 'import'])->name('import');
        Route::post('/import', [StudentController::class, 'importStudents'])->name('import.post');
        Route::get('/export', [StudentController::class, 'export'])->name('export');
        Route::get('/download-template', [StudentController::class, 'downloadTemplate'])->name('download-template');
        Route::get('/{student}', [StudentController::class, 'show'])->name('show');
        Route::get('/{student}/edit', [StudentController::class, 'edit'])->name('edit');
        Route::put('/{student}', [StudentController::class, 'update'])->name('update');
        Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-delete', [StudentController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-status', [StudentController::class, 'bulkStatus'])->name('bulk-status');
        Route::post('/{student}/toggle-status', [StudentController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{student}/reset-password', [StudentController::class, 'resetPassword'])->name('reset-password');
    });

    // ==================== Question Bank ====================
    Route::get('questions/import', [QuestionController::class, 'showImportForm'])->name('questions.import');
    Route::post('questions/import', [QuestionController::class, 'import'])->name('questions.import.post');
    Route::get('questions/export/template', [QuestionController::class, 'downloadTemplate'])->name('questions.template');
    Route::get('questions/export', [QuestionController::class, 'export'])->name('questions.export');
    Route::post('questions/bulk-delete', [QuestionController::class, 'bulkDelete'])->name('questions.bulk-delete');
    Route::post('questions/bulk-status', [QuestionController::class, 'bulkStatus'])->name('questions.bulk-status');
    Route::post('questions/{question}/duplicate', [QuestionController::class, 'duplicate'])->name('questions.duplicate');
    Route::resource('questions', QuestionController::class);

    // Question Categories
    Route::resource('question-categories', QuestionCategoryController::class);
    Route::post('question-categories/{question_category}/toggle-status', [QuestionCategoryController::class, 'toggleStatus'])->name('question-categories.toggle-status');
    Route::get('question-categories/tree', [QuestionCategoryController::class, 'getTree'])->name('question-categories.tree');
    Route::post('question-categories/order', [QuestionCategoryController::class, 'updateOrder'])->name('question-categories.order');

    // ==================== Exam Management ====================
    Route::resource('exams', ExamController::class);
    Route::get('exams/{exam}/add-questions', [ExamController::class, 'addQuestions'])->name('exams.add-questions');
    Route::post('exams/{exam}/questions', [ExamController::class, 'storeQuestions'])->name('exams.store-questions');
    Route::delete('exams/{exam}/questions/{question}', [ExamController::class, 'removeQuestion'])->name('exams.remove-question');
    Route::post('exams/{exam}/questions/order', [ExamController::class, 'updateQuestionsOrder'])->name('exams.questions-order');
    Route::post('exams/{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');
    Route::post('exams/{exam}/duplicate', [ExamController::class, 'duplicate'])->name('exams.duplicate');
    Route::get('exams/{exam}/statistics', [ExamController::class, 'statistics'])->name('exams.statistics');
    Route::get('exams/{exam}/results', [ExamController::class, 'results'])->name('exams.results');
    Route::get('exams/{exam}/results/export', [ExamController::class, 'exportResults'])->name('exams.export-results');

    Route::post('/exams/{exam}/add-random-questions', [ExamController::class, 'addRandomQuestions'])->name('exams.add-random-questions');
    Route::post('/exams/{exam}/questions/bulk-remove', [ExamController::class, 'bulkRemoveQuestions'])->name('exams.bulk-remove');
    // ==================== Exam Sections ====================
    Route::post('exams/{exam}/sections', [ExamSectionController::class, 'store'])->name('exams.sections.store');
    Route::put('exams/{exam}/sections/{section}', [ExamSectionController::class, 'update'])->name('exams.sections.update');
    Route::delete('exams/{exam}/sections/{section}', [ExamSectionController::class, 'destroy'])->name('exams.sections.destroy');
    Route::post('exams/{exam}/sections/order', [ExamSectionController::class, 'updateOrder'])->name('exams.sections.order');
    Route::post('exams/{exam}/sections/move-questions', [ExamSectionController::class, 'moveQuestions'])->name('exams.sections.move-questions');

    // ==================== Reports ====================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/exams', [ReportController::class, 'examReports'])->name('exams');
        Route::get('/exams/{exam}', [ReportController::class, 'examDetail'])->name('exam-detail');
        Route::get('/exams/{exam}', [ReportController::class, 'examDetail'])->name('exam-detail');
        Route::get('/exams/{exam}/export', [ReportController::class, 'exportExam'])->name('export-exam');
        Route::get('/students', [ReportController::class, 'studentReports'])->name('students');
        Route::get('/students/{student}', [ReportController::class, 'studentDetail'])->name('student-detail');
        Route::get('/students/export', [ReportController::class, 'exportStudents'])->name('export-students');
        Route::get('/students/{student}', [ReportController::class, 'exportStudent'])->name('export-student');
        Route::get('/batches', [ReportController::class, 'batchReports'])->name('batches');
        Route::get('/batches/{batch}', [ReportController::class, 'batchDetail'])->name('batch-detail');
        Route::get('/batches/{batch}', [ReportController::class, 'batchDetail'])->name('batch-detail');
        Route::get('/batches/{batch}/export', [ReportController::class, 'exportBatch'])->name('export-batch');
        Route::post('/export', [ReportController::class, 'export'])->name('export');
        Route::post('/exams/{exam}/generate-ranklist', [ReportController::class, 'generateRankList'])->name('generate-ranklist');
        Route::get('/question-analysis', [ReportController::class, 'questionAnalysis'])->name('question-analysis');
        Route::get('/question-analysis/{question}', [ReportController::class, 'questionDetail'])->name('question-detail');
        Route::get('/ranklist/{rankList}', [ReportController::class, 'viewRankList'])->name('rank-list');
        Route::post('/ranklist/{rankList}/publish', [ReportController::class, 'publishRankList'])->name('publish-ranklist');
    });

    // ==================== Analytics ====================
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/dashboard', [AnalyticsController::class, 'dashboard'])->name('dashboard');
        Route::get('/realtime', [AnalyticsController::class, 'getRealTimeData'])->name('realtime');

        // Exam Analytics
        Route::get('/exams', [AnalyticsController::class, 'examAnalytics'])->name('exams');
        Route::get('/exams/{exam}', [AnalyticsController::class, 'showExamAnalytics'])->name('exam-detail');

        // Student Analytics
        Route::get('/students', [AnalyticsController::class, 'studentAnalytics'])->name('students');
        Route::get('/students/{student}', [AnalyticsController::class, 'showStudentAnalytics'])->name('student-detail');

        // Batch Analytics
        Route::get('/batches', [AnalyticsController::class, 'batchAnalytics'])->name('batches');
        Route::get('/batches/{batch}', [AnalyticsController::class, 'showBatchAnalytics'])->name('batch-detail');

        // Question Analytics
        Route::get('/questions', [AnalyticsController::class, 'questionAnalytics'])->name('questions');
        Route::get('/questions/{question}', [AnalyticsController::class, 'showQuestionAnalytics'])->name('question-detail');

        // Comparison
        Route::post('/compare', [AnalyticsController::class, 'getComparisonData'])->name('compare');

        // Export
        Route::get('/export/{exam}', [AnalyticsController::class, 'exportExamAnalytics'])->name('export');
        Route::get('/export/student/{student}', [AnalyticsController::class, 'exportStudentAnalytics'])->name('export-student');
        Route::get('/export/batch/{batch}', [AnalyticsController::class, 'exportBatchAnalytics'])->name('export-batch');

        // Reports
        Route::get('/reports', [AnalyticsController::class, 'listReports'])->name('reports');
        Route::post('/reports/generate', [AnalyticsController::class, 'generateReport'])->name('generate');
        Route::get('/reports/{report}', [AnalyticsController::class, 'showReport'])->name('show-report');
        Route::delete('/reports/{report}', [AnalyticsController::class, 'deleteReport'])->name('delete-report');
        Route::get('/reports/{report}/export', [AnalyticsController::class, 'exportReport'])->name('export-report');
    });

    // =================study-materials==========================================
    Route::prefix('study-materials')->name('study-materials.')->group(function () {
        Route::get('/', [StudyMaterialController::class, 'index'])->name('index');
        Route::get('/create', [StudyMaterialController::class, 'create'])->name('create');
        Route::post('/', [StudyMaterialController::class, 'store'])->name('store');
        Route::get('/{studyMaterial}', [StudyMaterialController::class, 'show'])->name('show');
        Route::get('/{studyMaterial}/edit', [StudyMaterialController::class, 'edit'])->name('edit');
        Route::put('/{studyMaterial}', [StudyMaterialController::class, 'update'])->name('update');
        Route::delete('/{studyMaterial}', [StudyMaterialController::class, 'destroy'])->name('destroy');
        Route::get('/{studyMaterial}/download', [StudyMaterialController::class, 'download'])->name('download');
        Route::post('/{studyMaterial}/toggle-status', [StudyMaterialController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/modules/{course}', [StudyMaterialController::class, 'getModules'])->name('get-modules');
    });

    // ==================== Settings ====================
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/update', [SettingsController::class, 'update'])->name('update');
        Route::post('/upload-logo', [SettingsController::class, 'uploadLogo'])->name('upload-logo');
        Route::post('/upload-favicon', [SettingsController::class, 'uploadFavicon'])->name('upload-favicon');
        Route::post('/reset', [SettingsController::class, 'reset'])->name('reset');
        Route::get('/get/{key}', [SettingsController::class, 'get'])->name('get');
    });

    // ==================== User Management ====================
    Route::resource('users', UserController::class)
        ->middleware('permission:view users|create users|edit users|delete users');
    Route::post('users/{user}/roles', [UserController::class, 'assignRole'])
        ->middleware('permission:assign roles')
        ->name('users.assign-role');
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');
    Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])
        ->name('users.bulk-delete');
    Route::get('users/export', [UserController::class, 'export'])
        ->name('users.export');
    Route::get('users/{user}/activity', [UserController::class, 'activity'])
        ->name('users.activity');
    Route::post('users/{user}/impersonate', [UserController::class, 'impersonate'])
        ->name('users.impersonate');
    Route::get('users/leave-impersonate', [UserController::class, 'leaveImpersonate'])
        ->name('users.leave-impersonate');

    Route::get('users/{user}/reset-password', [UserController::class, 'showResetPasswordForm'])
        ->name('reset-password');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->name('reset-password.post');

    Route::prefix('monitoring')->group(function () {
        Route::get('/', [MonitoringController::class, 'adminIndex'])
            ->name('monitoring.index');
        Route::get('/{session}', [MonitoringController::class, 'show'])
            ->name('monitoring.show');
        Route::post('/{session}/end', [MonitoringController::class, 'endSession'])
            ->name('monitoring.end');
        Route::post('/review-images', [MonitoringController::class, 'reviewImages'])
            ->name('monitoring.review-images');
    });

});

/**
 * Moderation Routes for Admin and Instructor
 *  */
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {

    // Airport Management
    // Airport Management Routes
    Route::resource('airports', AirportController::class);
    // Additional airport routes
    Route::post('airports/{airport}/toggle-status', [AirportController::class, 'toggleStatus'])->name('airports.toggle-status');
    Route::post('airports/restore/{id}', [AirportController::class, 'restore'])->name('airports.restore');

    // Bulk actions
    Route::post('airports/bulk-delete', [AirportController::class, 'bulkDelete'])->name('airports.bulk-delete');

    Route::post('airports/bulk-status', [AirportController::class, 'bulkStatus'])->name('airports.bulk-status');

    // Result moderation
    Route::prefix('exam-moderation/{exam}/results')->name('results.moderation.')->group(function () {
        Route::get('/', [ResultModerationController::class, 'index'])->name('index');
        Route::post('/bulk-publish', [ResultModerationController::class, 'bulkPublish'])->name('bulk-publish');
        Route::post('/bulk-unpublish', [ResultModerationController::class, 'bulkUnpublish'])->name('bulk-unpublish');
    });
    Route::post('/exam-moderation/{exam}/results/recalculate-ranks',
        [ResultModerationController::class, 'recalculateRanks']
    )->name('results.moderation.recalculate-ranks');

    Route::prefix('results-moderation')->name('results.')->group(function () {
        Route::get('/{result}', [ResultModerationController::class, 'show'])->name('show');
        Route::post('/{result}/publish', [ResultModerationController::class, 'publish'])->name('publish');
    });

    Route::patch('/answers/{answer}', [ResultModerationController::class, 'updateAnswer'])->name('answers.update');
});

/*
|--------------------------------------------------------------------------
| Instructor Routes
|--------------------------------------------------------------------------
*/
// Instructor Routes
Route::middleware(['auth', 'role:instructor'])->prefix('instructor')->name('instructor.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Instructor\DashboardController::class, 'index'])->name('dashboard');

    // Instructor Notification
    Route::get('/notifications', [App\Http\Controllers\Instructor\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\Instructor\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [App\Http\Controllers\Instructor\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // ==================== Courses ====================
    Route::get('courses', [App\Http\Controllers\Instructor\CourseController::class, 'index'])
        ->middleware('permission:view courses')
        ->name('courses.index');
    Route::get('courses/{course}', [App\Http\Controllers\Instructor\CourseController::class, 'show'])
        ->middleware('permission:view courses')
        ->name('courses.show');
    Route::get('courses/{course}/modules', [App\Http\Controllers\Instructor\CourseController::class, 'getModules'])
        ->name('courses.modules');

    // ==================== Batches ====================
    Route::prefix('batches')->name('batches.')->group(function () {
        Route::get('/', [App\Http\Controllers\Instructor\BatchController::class, 'index'])->name('index');
        Route::get('/{batch}', [App\Http\Controllers\Instructor\BatchController::class, 'show'])->name('show');
        Route::get('/{batch}/students', [App\Http\Controllers\Instructor\BatchController::class, 'students'])->name('students');
        Route::get('/{batch}/exams', [App\Http\Controllers\Instructor\BatchController::class, 'exams'])->name('exams');
        Route::get('/{batch}/performance', [App\Http\Controllers\Instructor\BatchController::class, 'performance'])->name('performance');
    });

    // ==================== Exams ====================
    Route::prefix('exams')->name('exams.')->group(function () {
        Route::get('/', [App\Http\Controllers\Instructor\ExamController::class, 'index'])->name('index');
        Route::get('/{exam}', [App\Http\Controllers\Instructor\ExamController::class, 'show'])->name('show');
        Route::get('/{exam}/results', [App\Http\Controllers\Instructor\ExamController::class, 'results'])->name('results');
        Route::get('/{exam}/results/export', [App\Http\Controllers\Instructor\ExamController::class, 'exportResults'])->name('export-results');
        Route::post('/{exam}/publish', [App\Http\Controllers\Instructor\ExamController::class, 'publish'])->name('publish');
    });

    // ==================== Quizzes ====================
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/', [QuizController::class, 'index'])->name('index');
        Route::get('/create', [QuizController::class, 'create'])->name('create');
        Route::post('/launch', [QuizController::class, 'launch'])->name('launch');
        Route::post('/launch-instant', [QuizController::class, 'launchInstant'])->name('launch-instant');
        Route::get('/{quiz}', [QuizController::class, 'show'])->name('show');
        Route::get('/{quiz}/edit', [QuizController::class, 'edit'])->name('edit');
        Route::put('/{quiz}', [QuizController::class, 'update'])->name('update');
        Route::delete('/{quiz}', [QuizController::class, 'destroy'])->name('destroy');
        Route::get('/{quiz}/results', [QuizController::class, 'results'])->name('results');
        Route::post('/{quiz}/publish', [QuizController::class, 'publish'])->name('publish');
        Route::get('/{quiz}/available-questions', [QuizController::class, 'getAvailableQuestions'])->name('available-questions');
        Route::post('/{quiz}/add-questions', [QuizController::class, 'addQuestions'])->name('add-questions');
        Route::get('/{quiz}/export-results', [QuizController::class, 'exportResults'])->name('export-results');
        Route::get('/template', [QuizController::class, 'getTemplate'])->name('template');

        Route::get('/questions-by-batch/{batchId}', [QuizController::class, 'getQuestionsByBatch'])->name('questions-by-batch');
        Route::post('/generate-questions', [QuizController::class, 'generateQuestions'])->name('generate-questions');
    });

    // ==================== Live Monitoring ====================
    Route::prefix('live-monitoring')->name('live-monitoring.')->group(function () {
        Route::get('/', [LiveMonitoringController::class, 'index'])->name('index');
        Route::post('/start/{exam}', [LiveMonitoringController::class, 'startSession'])->name('start');
        Route::get('/{session}', [LiveMonitoringController::class, 'show'])->name('show');
        Route::get('/{session}/updates', [LiveMonitoringController::class, 'getUpdates'])->name('updates');
        Route::get('/{session}/student/{student}', [LiveMonitoringController::class, 'getStudentDetails'])->name('student');
        Route::post('/{session}/student/{student}/warn', [LiveMonitoringController::class, 'sendWarning'])->name('warn');
        Route::post('/{session}/student/{student}/terminate', [LiveMonitoringController::class, 'terminateStudent'])->name('terminate');
        Route::post('/{session}/end', [LiveMonitoringController::class, 'endSession'])->name('end');
        Route::get('/{session}/suspicious-images', [LiveMonitoringController::class, 'getSuspiciousImages'])->name('suspicious');
        Route::post('/images/{image}/review', [LiveMonitoringController::class, 'reviewImage'])->name('review-image');
        Route::get('/{session}/export', [LiveMonitoringController::class, 'exportReport'])->name('export');
    });

    // Monitoring Routes
    Route::prefix('monitoring')->group(function () {
        Route::get('/', [MonitoringController::class, 'index'])->name('monitoring.index');
        Route::post('/start', [MonitoringController::class, 'startSession'])->name('monitoring.start');
        Route::get('/{session}', [MonitoringController::class, 'show'])->name('monitoring.show');
        Route::post('/{session}/end', [MonitoringController::class, 'endSession'])->name('monitoring.end');
        Route::post('/{session}/pause', [MonitoringController::class, 'pauseSession'])->name('monitoring.pause');
        Route::post('/{session}/resume', [MonitoringController::class, 'resumeSession'])->name('monitoring.resume');

        // Student proctoring details
        Route::get('/student/{studentExamId}', [MonitoringController::class, 'studentProctoringDetails'])
            ->name('monitoring.student-details');

        // Proctoring actions
        Route::post('/review-images', [MonitoringController::class, 'reviewImages'])
            ->name('monitoring.review-images');
        Route::post('/{session}/capture-manual', [MonitoringController::class, 'captureManualScreenshot'])
            ->name('monitoring.capture-manual');
        Route::post('/{session}/log-activity', [MonitoringController::class, 'logActivity'])
            ->name('monitoring.log-activity');
        Route::get('/{session}/export', [MonitoringController::class, 'exportReport'])
            ->name('monitoring.export');
    });

    // ==================== Feedback ====================
    Route::prefix('feedback')->name('feedback.')->group(function () {
        Route::get('/', [FeedbackController::class, 'index'])->name('index');
        Route::get('/create', [FeedbackController::class, 'create'])->name('create');
        Route::post('/launch', [FeedbackController::class, 'launch'])->name('launch');
        Route::get('/{feedback}', [FeedbackController::class, 'show'])->name('show');
        Route::get('/{feedback}/responses', [FeedbackController::class, 'responses'])->name('responses');
        Route::post('/{feedback}/close', [FeedbackController::class, 'close'])->name('close');
        Route::get('/{feedback}/export', [FeedbackController::class, 'exportResponses'])->name('export');
        Route::delete('/{feedback}', [FeedbackController::class, 'destroy'])->name('destroy');
    });

    // ==================== Analytics ====================
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [App\Http\Controllers\Instructor\AnalyticsController::class, 'index'])->name('index');
        Route::get('/exams', [App\Http\Controllers\Instructor\AnalyticsController::class, 'examAnalytics'])->name('exams');
        Route::get('/students', [App\Http\Controllers\Instructor\AnalyticsController::class, 'studentAnalytics'])->name('students');
        Route::get('/batches', [App\Http\Controllers\Instructor\AnalyticsController::class, 'batchAnalytics'])->name('batches');
    });

    // ==================== Reports ====================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\Instructor\ReportController::class, 'index'])->name('index');
        Route::get('/exams', [App\Http\Controllers\Instructor\ReportController::class, 'examReports'])->name('exams');
        Route::get('/students', [App\Http\Controllers\Instructor\ReportController::class, 'studentReports'])->name('students');
        Route::get('/batches', [App\Http\Controllers\Instructor\ReportController::class, 'batchReports'])->name('batches');
        Route::post('/export', [App\Http\Controllers\Instructor\ReportController::class, 'export'])->name('export');
    });

    // ==================== Study Materials ====================
    Route::prefix('study-materials')->name('study-materials.')->group(function () {
        Route::get('/', [App\Http\Controllers\Instructor\StudyMaterialController::class, 'index'])->name('index');
        Route::get('/{studyMaterial}', [App\Http\Controllers\Instructor\StudyMaterialController::class, 'show'])->name('show');
        Route::get('/{studyMaterial}/download', [App\Http\Controllers\Instructor\StudyMaterialController::class, 'download'])->name('download');
        Route::get('/{studyMaterial}/view', [App\Http\Controllers\Instructor\StudyMaterialController::class, 'view'])->name('view');

    });
});

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/course/{course}', [App\Http\Controllers\Student\DashboardController::class, 'courseDetail'])->name('course.detail');
    Route::get('/batch/{batch}', [App\Http\Controllers\Student\DashboardController::class, 'batchDetail'])->name('batch.detail');
    Route::get('/batch/{batch}/exams', [App\Http\Controllers\Student\DashboardController::class, 'batchExams'])->name('batch.exams');

    // Student Notification
    Route::get('/notifications', [App\Http\Controllers\Student\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\Student\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [App\Http\Controllers\Student\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    // ==================== Exams ====================
    Route::prefix('exams')->name('exams.')->group(function () {
        Route::get('/', [App\Http\Controllers\Student\ExamController::class, 'index'])->name('index');
        Route::get('/{exam}/instructions', [App\Http\Controllers\Student\ExamController::class, 'instructions'])->name('instructions');
        Route::post('/{exam}/start', [App\Http\Controllers\Student\ExamController::class, 'start'])->name('start');
    });

    // ==================== Taking Exam ====================
    Route::prefix('exam')->name('exam.')->group(function () {
        Route::get('/{studentExam}/take', [App\Http\Controllers\Student\ExamController::class, 'take'])->name('take');
        Route::get('/{studentExam}/resume', [App\Http\Controllers\Student\ExamController::class, 'resume'])->name('resume');
        Route::get('/{studentExam}/question/{question}', [App\Http\Controllers\Student\ExamController::class, 'getQuestion'])->name('question');
        Route::post('/{studentExam}/save-answer', [App\Http\Controllers\Student\ExamController::class, 'saveAnswer'])->name('save-answer');
        Route::post('/{studentExam}/mark-review', [App\Http\Controllers\Student\ExamController::class, 'markForReview'])->name('mark-review');
        Route::post('/{studentExam}/submit', [App\Http\Controllers\Student\ExamController::class, 'submit'])->name('submit');
        Route::post('/{studentExam}/tab-switch', [App\Http\Controllers\Student\ExamController::class, 'recordTabSwitch'])->name('tab-switch');
        Route::get('/{studentExam}/time-remaining', [App\Http\Controllers\Student\ExamController::class, 'getRemainingTime'])->name('time-remaining');
        Route::post('/{studentExam}/activity', [App\Http\Controllers\Student\ExamController::class, 'logActivity'])->name('activity');
        Route::get('/{studentExam}/result', [App\Http\Controllers\Student\ExamController::class, 'result'])->name('result');

        Route::get('/{studentExam}/submitted', [App\Http\Controllers\Student\ExamController::class, 'submitted'])->name('submitted');
        Route::get('/result/{studentExam}', [App\Http\Controllers\Student\ExamController::class, 'result'])->name('exam.result'); // need to recheck this route, it might be redundant with the previous one

        Route::post('/{studentExam}/save-current-question', [App\Http\Controllers\Student\ExamController::class, 'saveCurrentQuestion'])->name('save-current-question');
        Route::post('/{studentExam}/save-exit', [App\Http\Controllers\Student\ExamController::class, 'saveExit'])->name('save-exit');
        Route::post('/{studentExam}/sync-timer', [App\Http\Controllers\Student\ExamController::class, 'syncTimer'])->name('sync-timer');
    });

    // ==================== Results ====================
    Route::prefix('results')->name('results.')->group(function () {
        Route::get('/', [ResultController::class, 'index'])->name('index');
        Route::get('/{result}', [ResultController::class, 'show'])->name('show');
        Route::get('/{result}/analytics', [ResultController::class, 'analytics'])->name('analytics');
        Route::post('/{result}/share', [ResultController::class, 'share'])->name('share');
        Route::get('/{result}/download', [ResultController::class, 'download'])->name('download');
        Route::get('/{result}/compare', [ResultController::class, 'compare'])->name('compare');
        Route::get('/public/{id}', [ResultController::class, 'publicView'])->name('public');
    });

    // ==================== Feedback ====================
    Route::prefix('feedback')->name('feedback.')->group(function () {
        // Static routes FIRST (these must come before the {code} route)
        Route::get('/history', [App\Http\Controllers\Student\FeedbackController::class, 'history'])->name('history');
        Route::get('/pending', [App\Http\Controllers\Student\FeedbackController::class, 'pending'])->name('pending');

        // Dynamic routes LAST (with parameters)
        Route::get('/{code}', [App\Http\Controllers\Student\FeedbackController::class, 'show'])->name('show');
        Route::post('/{code}/respond', [App\Http\Controllers\Student\FeedbackController::class, 'respond'])->name('respond');
        Route::get('/{feedback}/details', [App\Http\Controllers\Student\FeedbackController::class, 'details'])->name('details');
    });

    // ==================== Analytics ====================
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [App\Http\Controllers\Student\AnalyticsController::class, 'index'])->name('index');
        Route::get('/performance', [App\Http\Controllers\Student\AnalyticsController::class, 'performance'])->name('performance');
        Route::get('/topics', [App\Http\Controllers\Student\AnalyticsController::class, 'topics'])->name('topics');
    });

    // ==================== Quizzes ====================
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/', [App\Http\Controllers\Student\QuizController::class, 'index'])->name('index');
        Route::get('/{exam}/instructions', [App\Http\Controllers\Student\QuizController::class, 'instructions'])->name('instructions');
        Route::post('/{exam}/start', [App\Http\Controllers\Student\QuizController::class, 'start'])->name('start');
    });

    // ==================== Taking Quiz ====================
    Route::prefix('quiz')->name('quiz.')->group(function () {
        Route::get('/{studentExam}/take', [App\Http\Controllers\Student\QuizController::class, 'take'])->name('take');
        Route::get('/{studentExam}/question/{question}', [App\Http\Controllers\Student\QuizController::class, 'getQuestion'])->name('question');
        Route::post('/{studentExam}/save-answer', [App\Http\Controllers\Student\QuizController::class, 'saveAnswer'])->name('save-answer');
        Route::post('/{studentExam}/mark-review', [App\Http\Controllers\Student\QuizController::class, 'markForReview'])->name('mark-review');
        Route::post('/{studentExam}/submit', [App\Http\Controllers\Student\QuizController::class, 'submit'])->name('submit');
        Route::get('/{studentExam}/result', [App\Http\Controllers\Student\QuizController::class, 'result'])->name('result');
        Route::post('/{studentExam}/save-current-question', [App\Http\Controllers\Student\QuizController::class, 'saveCurrentQuestion'])->name('save-current-question');
        Route::post('/{studentExam}/sync-timer', [App\Http\Controllers\Student\QuizController::class, 'syncTimer'])->name('sync-timer');
    });

    // ==================== Study Materials ====================
    Route::get('study-materials', [App\Http\Controllers\Student\StudyMaterialController::class, 'index'])->name('study-materials');
    Route::get('study-materials/{studyMaterial}', [App\Http\Controllers\Student\StudyMaterialController::class, 'show'])->name('study-materials.show');
    Route::get('study-materials/{studyMaterial}/download', [App\Http\Controllers\Student\StudyMaterialController::class, 'download'])->name('study-materials.download');
    Route::get('study-materials/category/{categoryId}', [App\Http\Controllers\Student\StudyMaterialController::class, 'getByCategory'])->name('study-materials.category');
    Route::get('study-materials/course/{courseId}', [App\Http\Controllers\Student\StudyMaterialController::class, 'getByCourse'])->name('study-materials.course');
    Route::get('study-materials/{studyMaterial}/view', [App\Http\Controllers\Student\StudyMaterialController::class, 'view'])->name('study-materials.view');

});
