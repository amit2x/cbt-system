<?php

namespace App\Console\Commands;

use App\Services\Exam\ExamEngineService;
use Illuminate\Console\Command;

class AutoSubmitExams extends Command
{
    protected $signature = 'exams:auto-submit';
    protected $description = 'Auto-submit expired exams';

    protected $examEngine;

    public function __construct(ExamEngineService $examEngine)
    {
        parent::__construct();
        $this->examEngine = $examEngine;
    }

    public function handle()
    {
        $this->info('Starting auto-submit process...');

        $count = $this->examEngine->autoSubmitExpiredExams();

        $this->info("Auto-submitted {$count} expired exams.");

        return Command::SUCCESS;
    }
}
