<?php

namespace App\Console\Commands;

use App\Jobs\TranscribeAudio;
use App\Models\AudioSlice;
use Illuminate\Console\Command;

class TranscribeSlices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccai:slices';

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
        $slices = AudioSlice::whereNull('transcription')->get();

        $slices->each(function ($slice) {
            dispatch(new TranscribeAudio($slice->id, 'slice'));
            $this->info("OK => {$slice->file_name} job has been queued");
        });
    }
}
