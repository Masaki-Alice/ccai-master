<?php

namespace App\Console\Commands;

use App\Jobs\SliceAudio;
use App\Models\Inventory;
use Illuminate\Console\Command;

class SliceVoiceSamples extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccai:slice-voice-samples';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Slice all voice samples in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $audios = Inventory::all();

        # Slice eact full length audio into pieces
        $audios->each(function ($audio) {
            dispatch(new SliceAudio($audio->id));
            $this->info('OK => Slice job has been queued');
        });
    }
}
