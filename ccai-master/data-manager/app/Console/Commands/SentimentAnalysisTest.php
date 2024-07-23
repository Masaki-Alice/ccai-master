<?php

namespace App\Console\Commands;

use App\Jobs\AnalyzeSentiment;
use App\Models\Inventory;
use Illuminate\Console\Command;

class SentimentAnalysisTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccai:sentiment {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $audio = Inventory::find($this->argument('id'));

        dispatch_sync(new AnalyzeSentiment($audio->id));
    }
}
