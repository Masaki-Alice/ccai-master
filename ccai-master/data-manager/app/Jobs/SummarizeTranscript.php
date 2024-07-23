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

class SummarizeTranscript implements ShouldQueue
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
        $pythonEnv = config('settings.summarization.python_env');
        $script = config('settings.summarization.script');
        $payload = base64_encode(json_encode($this->audio->transcription['transcript']));

        $process = new Process([$pythonEnv, $script, $payload]);
        $process->setTimeout(60 * 30);

        Log::info("Summarization started for {$this->audio->disk_path}", [
            'encoded_payload' => $payload
        ]);

        try {
            $process->mustRun();

            # Returns a summary paragraph
            $response = $process->getOutput();

            Log::info("OK => Summarization finished for {$this->audio->disk_path}", [
                'response' => $response,
            ]);

            # Save summary in database
            $this->audio->update([
                'summary' => $response,
            ]);

            # Push to BugQuery
            dispatch(new PushToBigQuery($this->audio->id));
        } catch (ProcessFailedException $exception) {
            Log::error("Summarization failed for {$this->audio->disk_path}", [
                'error' => $exception->getMessage()
            ]);
        }
    }
}
