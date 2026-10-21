<?php

namespace App\Providers;

use App\Models\Exam;
use App\Policies\ExamPolicy;
use App\Repositories\Contracts\QuestionRepositoryInterface;
use App\Repositories\Eloquent\QuestionRepository;
use App\Services\Analytics\ResultAnalysisService;
use App\Services\Exam\ExamEngineService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        // Bind repository interfaces to their implementations
        $this->app->bind(
            QuestionRepositoryInterface::class,
            QuestionRepository::class,
        );
        $this->app->singleton(ResultAnalysisService::class);
        $this->app->singleton(ExamEngineService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Exam::class, ExamPolicy::class);
    }
}
