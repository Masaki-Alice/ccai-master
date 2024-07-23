<?php

namespace App\Jobs\ccai;

use App\Jobs\GeminiTranscribeAudio;
use App\Models\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ExportConversationToGCSBucket implements ShouldQueue
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
        $pythonEnv = config('settings.gcs_upload.python_env');
        $script = config('settings.gcs_upload.script');

        # Check the field actually exists
        if (!$this->audio->transcription) {
            dispatch(new GeminiTranscribeAudio($this->audio->id));
            Log::warning("Transcription does not exist for {$this->audio->disk_path}. Retrying...", [
                'audio' => $this->audio,
            ]);
            die;
        }

        $payload = base64_encode(json_encode($this->audio->transcription['transcript']));
        $process = new Process([$pythonEnv, $script, $payload, $this->audio->id]);
        $process->setTimeout(60 * 30);

        Log::info("Conversation export started for {$this->audio->disk_path}", [
            'payload' => $payload,
            'audio' => $this->audio,
        ]);

        try {
            $process->mustRun();
            $response = json_decode($process->getOutput(), true);

            Log::info("OK => Conversation export finished for {$this->audio->disk_path}", [
                'response' => $response,
            ]);

            # Save conversation in database
            $this->audio->update([
                'conversation' => $response['conversation'],
            ]);
        } catch (ProcessFailedException $exception) {
            Log::error("Exporting conversation failed for {$this->audio->disk_path}", [
                'error' => $exception->getMessage()
            ]);
        }
    }
}
