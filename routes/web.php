<?php

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

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
/*
|--------------------------------------------------------------------------
| Public Static Pages
|--------------------------------------------------------------------------
*/
Route::prefix('pages')->name('')->group(function () {
    Route::get('/privacy-policy', [App\Http\Controllers\HomeController::class, 'privacyPolicy'])->name('privacy-policy');
    Route::get('/terms-of-service', [App\Http\Controllers\HomeController::class, 'termsOfService'])->name('terms-of-service');
    Route::get('/about-us', [App\Http\Controllers\HomeController::class, 'aboutUs'])->name('about');
    Route::get('/contact-us', [App\Http\Controllers\HomeController::class, 'contactUs'])->name('contact');
    Route::get('/faq', [App\Http\Controllers\HomeController::class, 'faq'])->name('faq');
    Route::get('/cookie-policy', [App\Http\Controllers\HomeController::class, 'cookiePolicy'])->name('cookie-policy');
});

/*
|--------------------------------------------------------------------------
| Profile Routes (Authenticated Users)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [App\Http\Controllers\ProfileController::class, 'show'])->name('show');
    Route::get('/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('edit');
    Route::put('/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('update');
    Route::get('/settings', [App\Http\Controllers\ProfileController::class, 'settings'])->name('settings');
    Route::put('/settings/update', [App\Http\Controllers\ProfileController::class, 'updateSettings'])->name('update-settings');
    Route::post('/avatar', [App\Http\Controllers\ProfileController::class, 'uploadAvatar'])->name('upload-avatar');
    Route::get('/activity', [App\Http\Controllers\ProfileController::class, 'activity'])->name('activity');
    Route::put('/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('password');
    Route::post('/revoke-session', [App\Http\Controllers\ProfileController::class, 'revokeSession'])->name('revoke-session');
    Route::post('/enable-2fa', [App\Http\Controllers\ProfileController::class, 'enable2FA'])->name('enable-2fa');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // ==================== Course Management ====================
    Route::resource('courses', App\Http\Controllers\Admin\CourseController::class);
    Route::post('courses/{course}/modules', [App\Http\Controllers\Admin\CourseController::class, 'storeModule'])->name('courses.modules.store');
    Route::put('courses/{course}/modules/{module}', [App\Http\Controllers\Admin\CourseController::class, 'updateModule'])->name('courses.modules.update');
    Route::delete('courses/{course}/modules/{module}', [App\Http\Controllers\Admin\CourseController::class, 'deleteModule'])->name('courses.modules.delete');
    Route::post('courses/{course}/modules/order', [App\Http\Controllers\Admin\CourseController::class, 'updateModuleOrder'])->name('courses.modules.order');

    // ==================== Batch Management ====================
    Route::get('students/import-template', [App\Http\Controllers\Admin\BatchController::class, 'downloadImportTemplate'])
        ->name('students.import-template');

    Route::get('batches/{batch}/add-students', [App\Http\Controllers\Admin\BatchController::class, 'addStudents'])->name('batches.add-students');
    Route::post('batches/{batch}/students', [App\Http\Controllers\Admin\BatchController::class, 'storeStudents'])->name('batches.store-students');
    Route::delete('batches/{batch}/students/{student}', [App\Http\Controllers\Admin\BatchController::class, 'removeStudent'])->name('batches.remove-student');
    Route::post('batches/{batch}/students/{student}/status', [App\Http\Controllers\Admin\BatchController::class, 'updateStudentStatus'])->name('batches.student-status');
    Route::post('batches/{batch}/import-students', [App\Http\Controllers\Admin\BatchController::class, 'importStudents'])->name('batches.import-students');
    Route::get('batches/{batch}/export-students', [App\Http\Controllers\Admin\BatchController::class, 'exportStudents'])->name('batches.export-students');
    Route::get('batches/{batch}/statistics', [App\Http\Controllers\Admin\BatchController::class, 'statistics'])->name('batches.statistics');

    Route::resource('batches', App\Http\Controllers\Admin\BatchController::class);

    // ==================== Student Management ====================
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\StudentController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\StudentController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\StudentController::class, 'store'])->name('store');
        Route::get('/import', [App\Http\Controllers\Admin\StudentController::class, 'import'])->name('import');
        Route::post('/import', [App\Http\Controllers\Admin\StudentController::class, 'importStudents'])->name('import.post');
        Route::get('/export', [App\Http\Controllers\Admin\StudentController::class, 'export'])->name('export');
        Route::get('/download-template', [App\Http\Controllers\Admin\StudentController::class, 'downloadTemplate'])->name('download-template');
        Route::get('/{student}', [App\Http\Controllers\Admin\StudentController::class, 'show'])->name('show');
        Route::get('/{student}/edit', [App\Http\Controllers\Admin\StudentController::class, 'edit'])->name('edit');
        Route::put('/{student}', [App\Http\Controllers\Admin\StudentController::class, 'update'])->name('update');
        Route::delete('/{student}', [App\Http\Controllers\Admin\StudentController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-delete', [App\Http\Controllers\Admin\StudentController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-status', [App\Http\Controllers\Admin\StudentController::class, 'bulkStatus'])->name('bulk-status');
        Route::post('/{student}/toggle-status', [App\Http\Controllers\Admin\StudentController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{student}/reset-password', [App\Http\Controllers\Admin\StudentController::class, 'resetPassword'])->name('reset-password');
    });

    // ==================== Question Bank ====================
    Route::get('questions/import', [App\Http\Controllers\Admin\QuestionController::class, 'showImportForm'])->name('questions.import');
    Route::post('questions/import', [App\Http\Controllers\Admin\QuestionController::class, 'import'])->name('questions.import.post');
    Route::get('questions/export/template', [App\Http\Controllers\Admin\QuestionController::class, 'downloadTemplate'])->name('questions.template');
    Route::get('questions/export', [App\Http\Controllers\Admin\QuestionController::class, 'export'])->name('questions.export');
    Route::post('questions/bulk-delete', [App\Http\Controllers\Admin\QuestionController::class, 'bulkDelete'])->name('questions.bulk-delete');
    Route::post('questions/bulk-status', [App\Http\Controllers\Admin\QuestionController::class, 'bulkStatus'])->name('questions.bulk-status');
    Route::post('questions/{question}/duplicate', [App\Http\Controllers\Admin\QuestionController::class, 'duplicate'])->name('questions.duplicate');
    Route::resource('questions', App\Http\Controllers\Admin\QuestionController::class);

    // ==================== Question Categories ====================
    Route::get('question-categories/tree', [App\Http\Controllers\Admin\QuestionCategoryController::class, 'getTree'])->name('question-categories.tree');
    Route::post('question-categories/order', [App\Http\Controllers\Admin\QuestionCategoryController::class, 'updateOrder'])->name('question-categories.order');
    Route::resource('question-categories', App\Http\Controllers\Admin\QuestionCategoryController::class)->except(['create', 'edit']);

    // ==================== Exam Management ====================
    Route::resource('exams', App\Http\Controllers\Admin\ExamController::class);
    Route::get('exams/{exam}/add-questions', [App\Http\Controllers\Admin\ExamController::class, 'addQuestions'])->name('exams.add-questions');
    Route::post('exams/{exam}/questions', [App\Http\Controllers\Admin\ExamController::class, 'storeQuestions'])->name('exams.store-questions');
    Route::delete('exams/{exam}/questions/{question}', [App\Http\Controllers\Admin\ExamController::class, 'removeQuestion'])->name('exams.remove-question');
    Route::post('exams/{exam}/questions/order', [App\Http\Controllers\Admin\ExamController::class, 'updateQuestionsOrder'])->name('exams.questions-order');
    Route::post('exams/{exam}/publish', [App\Http\Controllers\Admin\ExamController::class, 'publish'])->name('exams.publish');
    Route::post('exams/{exam}/duplicate', [App\Http\Controllers\Admin\ExamController::class, 'duplicate'])->name('exams.duplicate');
    Route::get('exams/{exam}/statistics', [App\Http\Controllers\Admin\ExamController::class, 'statistics'])->name('exams.statistics');
    Route::get('exams/{exam}/results', [App\Http\Controllers\Admin\ExamController::class, 'results'])->name('exams.results');
    Route::get('exams/{exam}/results/export', [App\Http\Controllers\Admin\ExamController::class, 'exportResults'])->name('exams.export-results');

    // ==================== Exam Sections ====================
    Route::post('exams/{exam}/sections', [App\Http\Controllers\Admin\ExamSectionController::class, 'store'])->name('exams.sections.store');
    Route::put('exams/{exam}/sections/{section}', [App\Http\Controllers\Admin\ExamSectionController::class, 'update'])->name('exams.sections.update');
    Route::delete('exams/{exam}/sections/{section}', [App\Http\Controllers\Admin\ExamSectionController::class, 'destroy'])->name('exams.sections.destroy');
    Route::post('exams/{exam}/sections/order', [App\Http\Controllers\Admin\ExamSectionController::class, 'updateOrder'])->name('exams.sections.order');
    Route::post('exams/{exam}/sections/move-questions', [App\Http\Controllers\Admin\ExamSectionController::class, 'moveQuestions'])->name('exams.sections.move-questions');

    // ==================== Reports ====================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
        Route::get('/exams', [App\Http\Controllers\Admin\ReportController::class, 'examReports'])->name('exams');
        Route::get('/exams/{exam}', [App\Http\Controllers\Admin\ReportController::class, 'examDetail'])->name('exam-detail');
        Route::get('/students', [App\Http\Controllers\Admin\ReportController::class, 'studentReports'])->name('students');
        Route::get('/students/{student}', [App\Http\Controllers\Admin\ReportController::class, 'studentDetail'])->name('student-detail');
        Route::get('/batches', [App\Http\Controllers\Admin\ReportController::class, 'batchReports'])->name('batches');
        Route::get('/batches/{batch}', [App\Http\Controllers\Admin\ReportController::class, 'batchDetail'])->name('batch-detail');
        Route::post('/export', [App\Http\Controllers\Admin\ReportController::class, 'export'])->name('export');
        Route::post('/exams/{exam}/generate-ranklist', [App\Http\Controllers\Admin\ReportController::class, 'generateRankList'])->name('generate-ranklist');
        Route::get('/ranklist/{rankList}', [App\Http\Controllers\Admin\ReportController::class, 'viewRankList'])->name('rank-list');
        Route::post('/ranklist/{rankList}/publish', [App\Http\Controllers\Admin\ReportController::class, 'publishRankList'])->name('publish-ranklist');
    });

    // ==================== Analytics ====================
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\AnalyticsController::class, 'dashboard'])->name('dashboard');
        Route::get('/realtime', [App\Http\Controllers\Admin\AnalyticsController::class, 'getRealTimeData'])->name('realtime');

        // Exam Analytics
        Route::get('/exams', [App\Http\Controllers\Admin\AnalyticsController::class, 'examAnalytics'])->name('exams');
        Route::get('/exams/{exam}', [App\Http\Controllers\Admin\AnalyticsController::class, 'showExamAnalytics'])->name('exam-detail');

        // Student Analytics
        Route::get('/students', [App\Http\Controllers\Admin\AnalyticsController::class, 'studentAnalytics'])->name('students');
        Route::get('/students/{student}', [App\Http\Controllers\Admin\AnalyticsController::class, 'showStudentAnalytics'])->name('student-detail');

        // Batch Analytics
        Route::get('/batches', [App\Http\Controllers\Admin\AnalyticsController::class, 'batchAnalytics'])->name('batches');
        Route::get('/batches/{batch}', [App\Http\Controllers\Admin\AnalyticsController::class, 'showBatchAnalytics'])->name('batch-detail');

        // Question Analytics
        Route::get('/questions', [App\Http\Controllers\Admin\AnalyticsController::class, 'questionAnalytics'])->name('questions');
        Route::get('/questions/{question}', [App\Http\Controllers\Admin\AnalyticsController::class, 'showQuestionAnalytics'])->name('question-detail');

        // Reports
        Route::get('/reports', [App\Http\Controllers\Admin\AnalyticsController::class, 'listReports'])->name('reports');
        Route::post('/reports/generate', [App\Http\Controllers\Admin\AnalyticsController::class, 'generateReport'])->name('generate');
        Route::get('/reports/{report}', [App\Http\Controllers\Admin\AnalyticsController::class, 'showReport'])->name('show-report');
        Route::delete('/reports/{report}', [App\Http\Controllers\Admin\AnalyticsController::class, 'deleteReport'])->name('delete-report');
        Route::get('/reports/{report}/export', [App\Http\Controllers\Admin\AnalyticsController::class, 'exportReport'])->name('export-report');

        // Comparison
        Route::post('/compare', [App\Http\Controllers\Admin\AnalyticsController::class, 'getComparisonData'])->name('compare');

    });

    // ==================== Settings ====================
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('index');
        Route::post('/update', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('update');
        Route::post('/upload-logo', [App\Http\Controllers\Admin\SettingsController::class, 'uploadLogo'])->name('upload-logo');
        Route::post('/upload-favicon', [App\Http\Controllers\Admin\SettingsController::class, 'uploadFavicon'])->name('upload-favicon');
        Route::post('/reset', [App\Http\Controllers\Admin\SettingsController::class, 'reset'])->name('reset');
        Route::get('/get/{key}', [App\Http\Controllers\Admin\SettingsController::class, 'get'])->name('get');
    });

    // ==================== User Management ====================
    Route::resource('users', App\Http\Controllers\Admin\UserController::class)
        ->middleware('permission:view users|create users|edit users|delete users');
    Route::post('users/{user}/roles', [App\Http\Controllers\Admin\UserController::class, 'assignRole'])
        ->middleware('permission:assign roles')
        ->name('users.assign-role');
    Route::post('users/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');
    Route::post('users/bulk-delete', [App\Http\Controllers\Admin\UserController::class, 'bulkDelete'])
        ->name('users.bulk-delete');
    Route::get('users/export', [App\Http\Controllers\Admin\UserController::class, 'export'])
        ->name('users.export');
    Route::get('users/{user}/activity', [App\Http\Controllers\Admin\UserController::class, 'activity'])
        ->name('users.activity');
    Route::post('users/{user}/impersonate', [App\Http\Controllers\Admin\UserController::class, 'impersonate'])
        ->name('users.impersonate');
    Route::get('users/leave-impersonate', [App\Http\Controllers\Admin\UserController::class, 'leaveImpersonate'])
        ->name('users.leave-impersonate');
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
        Route::get('/', [App\Http\Controllers\Instructor\QuizController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Instructor\QuizController::class, 'create'])->name('create');
        Route::post('/launch', [App\Http\Controllers\Instructor\QuizController::class, 'launch'])->name('launch');
        Route::post('/launch-instant', [App\Http\Controllers\Instructor\QuizController::class, 'launchInstant'])->name('launch-instant');
        Route::get('/{quiz}', [App\Http\Controllers\Instructor\QuizController::class, 'show'])->name('show');
        Route::get('/{quiz}/edit', [App\Http\Controllers\Instructor\QuizController::class, 'edit'])->name('edit');
        Route::put('/{quiz}', [App\Http\Controllers\Instructor\QuizController::class, 'update'])->name('update');
        Route::delete('/{quiz}', [App\Http\Controllers\Instructor\QuizController::class, 'destroy'])->name('destroy');
        Route::get('/{quiz}/results', [App\Http\Controllers\Instructor\QuizController::class, 'results'])->name('results');
        Route::post('/{quiz}/publish', [App\Http\Controllers\Instructor\QuizController::class, 'publish'])->name('publish');
        Route::get('/{quiz}/available-questions', [App\Http\Controllers\Instructor\QuizController::class, 'getAvailableQuestions'])->name('available-questions');
        Route::post('/{quiz}/add-questions', [App\Http\Controllers\Instructor\QuizController::class, 'addQuestions'])->name('add-questions');
        Route::get('/{quiz}/export-results', [App\Http\Controllers\Instructor\QuizController::class, 'exportResults'])->name('export-results');
        Route::get('/template', [App\Http\Controllers\Instructor\QuizController::class, 'getTemplate'])->name('template');
    });

    // ==================== Live Monitoring ====================
    Route::prefix('live-monitoring')->name('live-monitoring.')->group(function () {
        Route::get('/', [App\Http\Controllers\Instructor\LiveMonitoringController::class, 'index'])->name('index');
        Route::post('/start/{exam}', [App\Http\Controllers\Instructor\LiveMonitoringController::class, 'startSession'])->name('start');
        Route::get('/{session}', [App\Http\Controllers\Instructor\LiveMonitoringController::class, 'show'])->name('show');
        Route::get('/{session}/updates', [App\Http\Controllers\Instructor\LiveMonitoringController::class, 'getUpdates'])->name('updates');
        Route::get('/{session}/student/{student}', [App\Http\Controllers\Instructor\LiveMonitoringController::class, 'getStudentDetails'])->name('student');
        Route::post('/{session}/student/{student}/warn', [App\Http\Controllers\Instructor\LiveMonitoringController::class, 'sendWarning'])->name('warn');
        Route::post('/{session}/student/{student}/terminate', [App\Http\Controllers\Instructor\LiveMonitoringController::class, 'terminateStudent'])->name('terminate');
        Route::post('/{session}/end', [App\Http\Controllers\Instructor\LiveMonitoringController::class, 'endSession'])->name('end');
        Route::get('/{session}/suspicious-images', [App\Http\Controllers\Instructor\LiveMonitoringController::class, 'getSuspiciousImages'])->name('suspicious');
        Route::post('/images/{image}/review', [App\Http\Controllers\Instructor\LiveMonitoringController::class, 'reviewImage'])->name('review-image');
        Route::get('/{session}/export', [App\Http\Controllers\Instructor\LiveMonitoringController::class, 'exportReport'])->name('export');
    });

    // ==================== Feedback ====================
    Route::prefix('feedback')->name('feedback.')->group(function () {
        Route::get('/', [App\Http\Controllers\Instructor\FeedbackController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Instructor\FeedbackController::class, 'create'])->name('create');
        Route::post('/launch', [App\Http\Controllers\Instructor\FeedbackController::class, 'launch'])->name('launch');
        Route::get('/{feedback}', [App\Http\Controllers\Instructor\FeedbackController::class, 'show'])->name('show');
        Route::get('/{feedback}/responses', [App\Http\Controllers\Instructor\FeedbackController::class, 'responses'])->name('responses');
        Route::post('/{feedback}/close', [App\Http\Controllers\Instructor\FeedbackController::class, 'close'])->name('close');
        Route::get('/{feedback}/export', [App\Http\Controllers\Instructor\FeedbackController::class, 'exportResponses'])->name('export');
        Route::delete('/{feedback}', [App\Http\Controllers\Instructor\FeedbackController::class, 'destroy'])->name('destroy');
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
});

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');

    // ==================== Exams ====================
    Route::prefix('exams')->name('exams.')->group(function () {
        Route::get('/', [App\Http\Controllers\Student\ExamController::class, 'index'])->name('index');
        Route::get('/{exam}/instructions', [App\Http\Controllers\Student\ExamController::class, 'instructions'])->name('instructions');
        Route::post('/{exam}/start', [App\Http\Controllers\Student\ExamController::class, 'start'])->name('start');
    });

    // ==================== Taking Exam ====================
    Route::prefix('exam')->name('exam.')->group(function () {
        Route::get('/{studentExam}/take', [App\Http\Controllers\Student\ExamController::class, 'take'])->name('take');
        Route::get('/{studentExam}/question/{question}', [App\Http\Controllers\Student\ExamController::class, 'getQuestion'])->name('question');
        Route::post('/{studentExam}/save-answer', [App\Http\Controllers\Student\ExamController::class, 'saveAnswer'])->name('save-answer');
        Route::post('/{studentExam}/mark-review', [App\Http\Controllers\Student\ExamController::class, 'markForReview'])->name('mark-review');
        Route::post('/{studentExam}/submit', [App\Http\Controllers\Student\ExamController::class, 'submit'])->name('submit');
        Route::post('/{studentExam}/tab-switch', [App\Http\Controllers\Student\ExamController::class, 'recordTabSwitch'])->name('tab-switch');
        Route::get('/{studentExam}/time-remaining', [App\Http\Controllers\Student\ExamController::class, 'getRemainingTime'])->name('time-remaining');
        Route::post('/{studentExam}/activity', [App\Http\Controllers\Student\ExamController::class, 'logActivity'])->name('activity');
        Route::get('/{studentExam}/result', [App\Http\Controllers\Student\ExamController::class, 'result'])->name('result');
    });

    // ==================== Results ====================
    Route::prefix('results')->name('results.')->group(function () {
        Route::get('/', [App\Http\Controllers\Student\ResultController::class, 'index'])->name('index');
        Route::get('/{result}', [App\Http\Controllers\Student\ResultController::class, 'show'])->name('show');
        Route::get('/{result}/analytics', [App\Http\Controllers\Student\ResultController::class, 'analytics'])->name('analytics');
        Route::post('/{result}/share', [App\Http\Controllers\Student\ResultController::class, 'share'])->name('share');
        Route::get('/{result}/download', [App\Http\Controllers\Student\ResultController::class, 'download'])->name('download');
        Route::get('/{result}/compare', [App\Http\Controllers\Student\ResultController::class, 'compare'])->name('compare');
        Route::get('/public/{id}', [App\Http\Controllers\Student\ResultController::class, 'publicView'])->name('public');
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

    // ==================== Study Materials ====================
    Route::get('/study-materials', [App\Http\Controllers\Student\StudyMaterialController::class, 'index'])->name('study-materials');
});
