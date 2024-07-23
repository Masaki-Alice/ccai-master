<?php

namespace App\Jobs;

use App\Models\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use App\Library\Transcription;

class TranscribeAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $audio;
    public $timeout = 0;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct($audioID, $audioType)
    {
        $this->audio = Transcription::findAudio($audioID, $audioType);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pythonEnv = config('settings.transcription.python_env');
        $script = config('settings.transcription.script');

        $process = new Process([$pythonEnv, $script, $this->audio->disk_path]);
        $process->setTimeout(60 * 30);

        try {
            $process->mustRun();

            $this->audio->update([
                'transcription' => json_decode($process->getOutput(), true),
                'transcription_queued_at' => null
            ]);
        } catch (ProcessFailedException $exception) {
            print($exception->getMessage());
        }
    }

    public function failed(): void
    {
        $this->audio->update([
            'transcription' => null,
            'transcription_queued_at' => null
        ]);
    }
}
