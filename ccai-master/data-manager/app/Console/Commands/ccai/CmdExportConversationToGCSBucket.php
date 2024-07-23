<?php

namespace App\Console\Commands\ccai;

use App\Jobs\ccai\ExportConversationToGCSBucket;
use App\Models\Inventory;
use Illuminate\Console\Command;

class CmdExportConversationToGCSBucket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccai:export-conversation {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export a transcription to GCS bucket in CCAI conversation format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $audio = Inventory::findOrFail($this->argument('id'));
        // dispatch_sync(new ExportConversationToGCSBucket($audio->id));

        Inventory::all()->random(5)->each(function ($audio) {
            dispatch(new ExportConversationToGCSBucket($audio->id));
        });
    }
}
