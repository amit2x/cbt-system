<?php

// app/Console/Commands/PublishScheduledResults.php

namespace App\Console\Commands;

use App\Models\Result;
use Illuminate\Console\Command;

class PublishScheduledResults extends Command
{
    protected $signature = 'results:publish-scheduled';

    protected $description = 'Auto-publish results whose scheduled publish date has arrived';

    public function handle()
    {
        $results = Result::readyToPublish()->get();

        foreach ($results as $result) {
            $result->update([
                'is_published' => true,
                'is_draft' => false,
                'published_at' => now(),
                'published_by' => null,
            ]);
        }

        $this->info("Published {$results->count()} result(s).");

        return Command::SUCCESS;
    }
}
