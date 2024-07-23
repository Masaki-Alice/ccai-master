<?php

namespace App\Jobs;

use App\Models\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class TranslateTranscriptionWithGemini implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

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
        $pythonEnv = config('settings.translation_gemini.python_env');
        $script = config('settings.translation_gemini.script');

        # Check the field actually exists
        if (!$this->audio->transcription) {
            dispatch(new GeminiTranscribeAudio($this->audio->id));
            Log::error("Transcription does not exist for {$this->audio->disk_path}. Retrying...", [
                'audio' => $this->audio,
            ]);
            die;
        }

        # Handover base64 encoded payload to Python script
        $payload = base64_encode(json_encode($this->audio->transcription));
        $process = new Process([$pythonEnv, $script, $payload]);
        $process->setTimeout(60 * 30);

        Log::info("Translation started for {$this->audio->disk_path}");

        try {
            $process->mustRun();
            $response = json_decode($process->getOutput(), true);

            Log::info("OK => Translation response for {$this->audio->disk_path}", [
                'response' => $response,
            ]);

            # Save transcription in database
            $this->audio->update([
                'translation' => $response,
            ]);
        } catch (ProcessFailedException $exception) {
            Log::error("Translation failed for {$this->audio->disk_path}", [
                'error' => $exception->getMessage()
            ]);
        }
    }
}
