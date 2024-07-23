<?php

namespace App\Jobs;

use App\Models\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class DiarizeAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 0;

    public $audio;

    /**
     * Create a new job instance.
     */
    public function __construct($id)
    {
        $this->audio = Inventory::findOrFail($id);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pythonEnv = config('settings.diarization_gemini.python_env');
        $script = config('settings.diarization_gemini.script');

        # Handover base64 encoded payload to Python script
        $payload = $this->audio->disk_path;
        $process = new Process([$pythonEnv, $script, $payload]);
        $process->setTimeout(60 * 30);

        Log::info("Diarization started for {$this->audio->disk_path}");

        try {
            $process->mustRun();
            $response = json_decode($process->getOutput(), true);
            Log::info("OK => Diarization response for {$this->audio->disk_path}", [
                'response' => $response,
                'payload' => $payload
            ]);

            # Save transcription in database
            $this->audio->update([
                'diarization' => $response,
            ]);
        } catch (ProcessFailedException $exception) {
            Log::info("Diarization failed for {$this->audio->disk_path}", [
                'error' => $exception->getMessage()
            ]);
        }
    }
}
